<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'nombre',
        'descripcion',
        'fecha_publicacion',
        'activo',
        'publicada' // 👈 Nuevo campo
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'activo' => 'boolean',
        'publicada' => 'boolean'
    ];

    // Relación: Una versión tiene muchos recursos maestros
    public function recursos()
    {
        return $this->belongsToMany(ObraRecursoMaestro::class, 'version_recurso_maestro')
                    ->withPivot('precio_version')
                    ->withTimestamps();
    }

    // 👇 NUEVOS SCOPES
    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    public function scopePublicada($query)
    {
        return $query->where('publicada', true);
    }


    // Activar esta versión y desactivar otras
    // 👇 NUEVOS MÉTODOS
    public function activar()
    {
        // Desactivar otras versiones manteniendo sus timestamps originales
        DB::statement("
            UPDATE versions 
            SET activo = false, updated_at = updated_at 
            WHERE id != ? AND activo = true
        ", [$this->id]);
        
        $this->activo = true;
        $this->save();
        
        return $this;
    }

     public function publicar()
    {
        // 👇 Solo despublicar versiones que realmente están publicadas
        // y solo actualizar el campo publicada
        DB::statement("
            UPDATE versions 
            SET publicada = false 
            WHERE id != ? AND publicada = true
        ", [$this->id]);
        
        // Publicar solo esta versión
        $this->publicada = true;
        $this->fecha_publicacion = now();
        $this->save();
        
        return $this;
    }

   
     // Método para publicar con precios
    public static function crearYPublicarVersionConPrecios($codigo, $nombre = null, $descripcion = null)
    {
        // Crear la versión
        $version = self::create([
            'version' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'fecha_publicacion' => now(),
            'activo' => false,
            'publicada' => false
        ]);

        // Copiar precios actuales
        $recursos = ObraRecursoMaestro::activos()->get();
        foreach ($recursos as $recurso) {
            $version->recursos()->attach($recurso->id, [
                'precio_version' => $recurso->precio_referencia
            ]);
        }

        $regiones = Region::activas()->get();
        foreach ($regiones as $region) {
            foreach ($recursos as $recurso) {
                $precioRegional = $recurso->getPrecioPorRegion($region->id);
                DB::table('version_recurso_region')->insert([
                    'version_id' => $version->id,
                    'obra_recurso_maestro_id' => $recurso->id,
                    'region_id' => $region->id,
                    'precio_version_regional' => $precioRegional,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Publicar la versión
        $version->publicar();
        
        return $version;
    }

    public static function crearVersionBasadaEnUltimaPublicada($codigo, $nombre = null, $descripcion = null)
    {
        // Obtener la última versión publicada
        $ultimaVersion = self::where('publicada', true)
                            ->orderBy('fecha_publicacion', 'desc')
                            ->first();
        
        // Crear la nueva versión
        $nuevaVersion = self::create([
            'version' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'fecha_publicacion' => null, // Aún no publicada
            'activo' => false,
            'publicada' => false
        ]);
        
        if ($ultimaVersion) {
            // Copiar precios de la última versión publicada
            $preciosGlobales = DB::table('version_recurso_maestro')
                ->where('version_id', $ultimaVersion->id)
                ->get();
                
            foreach ($preciosGlobales as $precio) {
                DB::table('version_recurso_maestro')->insert([
                    'version_id' => $nuevaVersion->id,
                    'obra_recurso_maestro_id' => $precio->obra_recurso_maestro_id,
                    'precio_version' => $precio->precio_version,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Copiar precios regionales
            $preciosRegionales = DB::table('version_recurso_region')
                ->where('version_id', $ultimaVersion->id)
                ->get();
                
            foreach ($preciosRegionales as $precio) {
                DB::table('version_recurso_region')->insert([
                    'version_id' => $nuevaVersion->id,
                    'obra_recurso_maestro_id' => $precio->obra_recurso_maestro_id,
                    'region_id' => $precio->region_id,
                    'precio_version_regional' => $precio->precio_version_regional,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } else {
            // Si no hay versión publicada, usar precios de la base de datos
            $recursos = ObraRecursoMaestro::activos()->get();
            foreach ($recursos as $recurso) {
                DB::table('version_recurso_maestro')->insert([
                    'version_id' => $nuevaVersion->id,
                    'obra_recurso_maestro_id' => $recurso->id,
                    'precio_version' => $recurso->precio_referencia,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            $regiones = Region::activas()->get();
            foreach ($regiones as $region) {
                foreach ($recursos as $recurso) {
                    $precioRegional = $recurso->getPrecioPorRegion($region->id);
                    DB::table('version_recurso_region')->insert([
                        'version_id' => $nuevaVersion->id,
                        'obra_recurso_maestro_id' => $recurso->id,
                        'region_id' => $region->id,
                        'precio_version_regional' => $precioRegional,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
        
        return $nuevaVersion;
    }
}