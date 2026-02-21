<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ObraRecursoMaestro extends Model
{
    use HasFactory;

    protected $table = 'obra_recursos_maestros';
      // 👇 ASEGURAR QUE ESTÉ DEFINIDA LA CLAVE PRIMARIA
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'codigo',
        'nombre', 
        'tipo',
        'unidad',
        'precio_referencia',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'precio_referencia' => 'decimal:2',
        'activo' => 'boolean'
    ];

    // Relación: Un recurso maestro puede estar en muchos items
    public function items()
    {
        return $this->belongsToMany(ObraItem::class, 'obra_item_recursos')
                    ->withPivot('rendimiento')
                    ->withTimestamps();
    }

    // Relación: Un recurso maestro puede estar en muchas versiones
    public function versiones()
    {
        return $this->belongsToMany(Version::class, 'version_recurso_maestro')
                    ->withPivot('precio_version')
                    ->withTimestamps();
    }

    
    // NUEVA RELACIÓN: Precios por región
    public function preciosRegionales()
    {
        return $this->belongsToMany(Region::class, 'recurso_maestro_region')
                    ->withPivot('precio_regional')
                    ->withTimestamps();
    }
    public function versionesRegiones()
    {
        return $this->hasMany(VersionRecursoRegion::class, 'obra_recurso_maestro_id');
    }


    // Obtener precio para una región específica
    public function getPrecioPorRegion($regionId)
    {
        return $this->preciosRegionales()
                   ->where('region_id', $regionId)
                   ->first()?->pivot->precio_regional ?? $this->precio_referencia;
    }

    // Obtener precio para una versión y región específicas
    public function getPrecioByVersionYRegion($versionId, $regionId)
    {
        // Primero buscar en version_recurso_region
        $precioVersionRegional = DB::table('version_recurso_region')
            ->where('version_id', $versionId)
            ->where('obra_recurso_maestro_id', $this->id)
            ->where('region_id', $regionId)
            ->value('precio_version_regional');

        if ($precioVersionRegional) {
            return $precioVersionRegional;
        }

        // Si no existe, buscar en recurso_maestro_region
        $precioRegional = $this->getPrecioPorRegion($regionId);
        return $precioRegional;
    }

    // Scope para recursos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}