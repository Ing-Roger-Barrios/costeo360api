<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ObraCategoria;
use App\Models\ObraItem;
use App\Models\ObraRecursoMaestro;
use App\Models\ObraModulo;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class ImportController extends Controller
{
    // Constantes de tamaño
    const TAMANO_REGISTRO_DAT = 8;
    const TAMANO_REGISTRO_IND = 92;
    const TAMANO_REGISTRO_PRE = 460;

    // Configuración de bloques DAT
    const CONFIG_BLOQUES = [
        'Material' => ['offset' => 0, 'tamano' => 30, 'fijo' => true],
        'ManoObra' => ['offset' => 30, 'tamano' => 10, 'fijo' => false],
        'Equipo' => ['offset' => 40, 'tamano' => 20, 'fijo' => false]
    ];

    // ========================================================================
    // MÉTODOS DE LIMPIEZA Y CONVERSIÓN (CORRIGE EL ERROR DE UTF-8)
    // ========================================================================

    /**
     * Limpiar y convertir string de Latin-1 a UTF-8 con manejo de caracteres especiales
     */
    private function cleanLatin1String($string)
    {
        if (!$string || !is_string($string)) {
            return '';
        }
        
        // Reemplazos críticos para Prescom (caracteres que causan error UTF-8)
        $criticalReplacements = [
            "\xB2" => '²',  // m²
            "\xB3" => '³',  // m³
            "\xB0" => '°',  // °C
            "\xF1" => 'ñ',
            "\xD1" => 'Ñ',
            "\xA0" => ' ',  // Espacio no separable
        ];
        
        $clean = strtr($string, $criticalReplacements);
        
        // Eliminar caracteres de control no imprimibles
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        
        // Convertir Latin-1 → UTF-8
        if (!mb_check_encoding($clean, 'UTF-8')) {
            $clean = mb_convert_encoding($clean, 'UTF-8', 'ISO-8859-1');
        }
        
        // Limpiar UTF-8 inválido restante
        $clean = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
        
        // Normalizar espacios
        $clean = preg_replace('/\s+/', ' ', $clean);
        
        return trim($clean);
    }

    // ========================================================================
    // PARSEO ROBUSTO DE ARCHIVOS .DDP (ADAPTADO DEL SCRIPT PYTHON)
    // ========================================================================

    /**
     * Parsear IND - Catálogo de insumos con tipos e índices de precios
     */
    private function parseIndFileRobust($filePath)
{
    $insumos = [];
    $tipoMap = [
        0x4D => 'Material',
        0x4F => 'ManoObra', 
        0x45 => 'Equipo'
    ];

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new \Exception("No se pudo abrir archivo IND: {$filePath}");
    }

    $indice = 1; // ID REAL del recurso (empieza en 1, coincide con posición en DAT)

    while (!feof($handle)) {
        $registro = fread($handle, self::TAMANO_REGISTRO_IND);
        if (strlen($registro) < self::TAMANO_REGISTRO_IND) {
            break;
        }

        // Byte 2 = tipo de insumo
        $tipoByte = ord($registro[2]);
        $tipo = $tipoMap[$tipoByte] ?? 'Desconocido';

        // Bytes 88-92 = ID relacionado para buscar precio en DAT
        $idRelacionadoPrecio = unpack('V', substr($registro, 88, 4))[1];

        // Bytes 4-68: descripción (Latin-1)
        $descripcionRaw = substr($registro, 3, 63);
        $descripcion = $this->cleanLatin1String($descripcionRaw);
        
        // Bytes 76-80: unidad (Latin-1)
        /*$unidadRaw = substr($registro, 75, 4);
        $unidad = $this->cleanLatin1String($unidadRaw);*/
        $unidad = mb_convert_encoding(substr($registro, 75, 5), 'UTF-8', 'ISO-8859-1');
        $unidad = trim($unidad);
        $unidad = rtrim($unidad, "\x00"); // Eliminar null terminators

        // Solo agregar si tiene descripción válida
        //if (!empty($descripcion)) {
            $insumos[$indice] = [  // 👈 $indice ES el ID REAL que aparece en el DAT
                'id' => $indice,     // 👈 GUARDAR EL ID REAL
                'tipo' => $tipo,
                'id_relacionado_precio' => $idRelacionadoPrecio,
                'descripcion' => $descripcion,
                'unidad' => $unidad
            ];
            $indice++;
        //}
    }

    fclose($handle);
    Log::info("✓ IND cargado: " . ($indice - 1) . " insumos catalogados (ID real: 1-" . ($indice - 1) . ")");
    return $insumos;
}

    /**
     * Parsear DAT completo como array indexado
     */
    private function parseDatFileRobust($filePath)
    {
        $registros = [];

        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            throw new \Exception("No se pudo abrir archivo DAT: {$filePath}");
        }

        $indice = 0;

        while (!feof($handle)) {
            $registro = fread($handle, self::TAMANO_REGISTRO_DAT);
            if (strlen($registro) < self::TAMANO_REGISTRO_DAT) {
                break;
            }

            $idVal = unpack('V', substr($registro, 0, 4))[1]; // uint32 LE
            $valor = unpack('f', substr($registro, 4, 4))[1] ?? 0.0; // float32 LE

            if (!is_finite($valor) || abs($valor) > 1e6) {
                $valor = 0.0;
            }

            $registros[] = [
                'indice' => $indice,
                'id' => $idVal,
                'valor' => $valor
            ];

            $indice++;
        }

        fclose($handle);
        Log::info("✓ DAT cargado: " . count($registros) . " registros");
        return $registros;
    }

    /**
     * Parsear PRE - Partidas/Items (CORREGIDO UTF-8)
     */
    private function parsePreFileRobust($filePath)
{
    $partidas = [];

    $handle = fopen($filePath, 'rb');
    if (!$handle) {
        throw new \Exception("No se pudo abrir archivo PRE: {$filePath}");
    }

    while (!feof($handle)) {
        $registro = fread($handle, self::TAMANO_REGISTRO_PRE);
        if (strlen($registro) < self::TAMANO_REGISTRO_PRE) {
            break;
        }

        // Bytes 4-68: descripción (Latin-1)
        /*$descripcionRaw = substr($registro, 3, 63);
        $descripcion = $this->cleanLatin1String($descripcionRaw);*/
        // 👇 CORRECCIÓN CRÍTICA: Extraer bytes crudos y convertir MANUALMENTE de Latin-1 a UTF-8
        $descripcionRaw = substr($registro, 3, 63);
        $descripcion = $this->latin1ToUtf8($descripcionRaw);
        
        if (empty($descripcion)) {
            continue;
        }

        // Bytes 76-80: unidad (Latin-1)
        /*$unidadRaw = substr($registro, 75, 4);
        $unidad = $this->cleanLatin1String($unidadRaw);*/
        /*$unidad = mb_convert_encoding(substr($registro, 75, 5), 'UTF-8', 'ISO-8859-1');
            $unidad = trim($unidad);*/

        // 👇 CORRECCIÓN CRÍTICA: Unidad con conversión correcta
        $unidadRaw = substr($registro, 76, 5);

        $unidad = iconv('Windows-1252', 'UTF-8//IGNORE', $unidadRaw);

        $unidad = rtrim($unidad, "\x00");


        // Bytes 0-4: ID del módulo
        $idModulo = unpack('h', substr($registro, 2, 2))[1] ?? 1;

        // 👇 CRÍTICO: Bytes 81-85 = ID relacionado (OFFSET BASE en DAT)
        $idRelacionado = unpack('V', substr($registro, 81, 4))[1];

        // Bytes 85-89: Rendimiento del item (float32)
        /*$rendimiento = unpack('f', substr($registro, 85, 8))[1] ?? 1.0;
        if (!is_finite($rendimiento) || abs($rendimiento) > 1e6) {
            $rendimiento = 1.0;
        }*/
        // Rendimiento (bytes 85-92, double little-endian)
        $rendimientoBytes = substr($registro, 85, 8);
        $rendimiento = unpack('e', $rendimientoBytes)[1] ?? 0.0; // 'e' = double (little-endian)

        // Validar rendimiento
        if (!is_finite($rendimiento) || abs($rendimiento) > 1e6) {
            $rendimiento = 0.0;
        }

        $partidas[] = [
            'id' => count($partidas) + 1,
            'descripcion' => $descripcion,
            'unidad' => $unidad,
            'id_relacionado' => $idRelacionado,  // 👈 GUARDAR EL OFFSET BASE
            'id_modulo' => $idModulo,
            'rendimiento' => $rendimiento
        ];
    }

    fclose($handle);
    Log::info("✓ PRE cargado: " . count($partidas) . " partidas (offsets: " . 
        (count($partidas) > 0 ? $partidas[0]['id_relacionado'] . "..." . end($partidas)['id_relacionado'] : 'N/A') . ")");
    return $partidas;
}



/**
 * Convertir string de Latin-1 a UTF-8 con manejo de caracteres especiales
 * Soluciona el problema de "mÂÂ²" → "m²"
 */
private function latin1ToUtf8($string)
{
    if (!$string || !is_string($string)) {
        return '';
    }
    
    // Reemplazos manuales para caracteres problemáticos de Prescom
    $replacements = [
        "\xB2" => '²',  // Superíndice 2 (m²)
        "\xB3" => '³',  // Superíndice 3 (m³)
        "\xB0" => '°',  // Grados (°C)
        "\xF1" => 'ñ',
        "\xD1" => 'Ñ',
        "\xA0" => ' ',  // Espacio no separable
        "\xAA" => 'ª',
        "\xBA" => 'º',
    ];
    
    // Aplicar reemplazos
    $clean = strtr($string, $replacements);
    
    // Eliminar caracteres de control no imprimibles (excepto espacios)
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
    
    // Intentar conversión Latin-1 → UTF-8
    if (!mb_check_encoding($clean, 'UTF-8')) {
        $utf8 = mb_convert_encoding($clean, 'UTF-8', 'ISO-8859-1');
    } else {
        $utf8 = $clean;
    }
    
    // Limpiar UTF-8 inválido restante
    $utf8 = mb_convert_encoding($utf8, 'UTF-8', 'UTF-8');
    
    // Normalizar espacios
    $utf8 = preg_replace('/\s+/', ' ', $utf8);
    
    return trim($utf8);
}

    /**
     * Analizar bloque de insumos (versión robusta)
     */
    private function analizarBloqueEstricto($registrosDat, $inicio, $tiposInsumos, $tipoEsperado, $permitirVacio = false, $tamanoBloque = 30)
    {
        $insumos = [];
        $tieneDatos = false;
        $fin = $inicio + $tamanoBloque;

        for ($i = $inicio; $i < $fin; $i++) {
            if ($i >= count($registrosDat)) {
                break;
            }

            $reg = $registrosDat[$i];
            if ($reg['id'] == 0) {
                continue;
            }

            $tieneDatos = true;
            $tipoReal = $tiposInsumos[$reg['id']]['tipo'] ?? 'Desconocido';

            // Si el tipo no coincide → bloque inválido
            if ($tipoReal !== $tipoEsperado) {
                return [false, []];
            }

            $insumos[] = [
                'tipo' => $tipoReal,
                'id_insumo' => $reg['id'],
                'coeficiente' => $reg['valor']
            ];
        }

        // Bloque vacío pero permitido → válido
        if (!$tieneDatos && $permitirVacio) {
            return [true, []];
        }

        // Bloque vacío no permitido → inválido
        if (!$tieneDatos && !$permitirVacio) {
            return [false, []];
        }

        return [true, $insumos];
    }

    /**
     * Obtener precio de insumo desde DAT indexado
     */
    private function obtenerPrecioInsumo($registrosDat, $idRelacionadoPrecio)
    {
        if ($idRelacionadoPrecio == 0 || $idRelacionadoPrecio > count($registrosDat)) {
            return 0.0;
        }

        // El ID relacionado es el ÍNDICE en el array DAT (1-based)
        //$indice = $idRelacionadoPrecio - 1;
        $indice = $idRelacionadoPrecio  - 1;
        return $registrosDat[$indice]['valor'] ?? 0.0;
    }

    /**
     * Obtener todos los insumos de un item
     */
    private function obtenerInsumosItem($registrosDat, $idRelacionado, $tiposInsumos)
    {
        $insumosTotales = [];
        //$base = $idRelacionado - 1; // Convertir a índice base 0
        $base = $idRelacionado - 1;

        // 1️⃣ MATERIALES (30 registros fijos, puede estar vacío)
        [$validoMat, $bloqueMat] = $this->analizarBloqueEstricto(
            $registrosDat,
            $base,
            $tiposInsumos,
            'Material',
            true,
            self::CONFIG_BLOQUES['Material']['tamano']
        );

        if (!$validoMat) {
            return []; // Item inválido
        }
        $insumosTotales = array_merge($insumosTotales, $bloqueMat);

        // 2️⃣ MANO DE OBRA (10 registros opcionales)
        $bloqueMoInicio = $base + self::CONFIG_BLOQUES['Material']['tamano'];
        [$validoMo, $bloqueMo] = $this->analizarBloqueEstricto(
            $registrosDat,
            $bloqueMoInicio,
            $tiposInsumos,
            'ManoObra',
            true,
            self::CONFIG_BLOQUES['ManoObra']['tamano']
        );

        if ($validoMo && !empty($bloqueMo)) {
            $insumosTotales = array_merge($insumosTotales, $bloqueMo);
        }

        // 3️⃣ EQUIPOS (20 registros opcionales)
        $bloqueEqInicio = $base + self::CONFIG_BLOQUES['Material']['tamano'] + self::CONFIG_BLOQUES['ManoObra']['tamano'];
        [$validoEq, $bloqueEq] = $this->analizarBloqueEstricto(
            $registrosDat,
            $bloqueEqInicio,
            $tiposInsumos,
            'Equipo',
            true,
            self::CONFIG_BLOQUES['Equipo']['tamano']
        );

        if ($validoEq && !empty($bloqueEq)) {
            $insumosTotales = array_merge($insumosTotales, $bloqueEq);
        }

        return $insumosTotales;
    }

    // ========================================================================
    // MÉTODOS EXISTENTES (MANTENIDOS SIN CAMBIOS)
    // ========================================================================

    private function findProjectFiles($extractedPath)
    {
        $files = [
            'stt' => null,
            'mod' => null,
            'ind' => null,
            'pre' => null,
            'dat' => null
        ];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractedPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                if (preg_match('/\.STT$/i', $filename)) {
                    $files['stt'] = $file->getPathname();
                } elseif (preg_match('/\.MOD$/i', $filename)) {
                    $files['mod'] = $file->getPathname();
                } elseif (preg_match('/\.IND$/i', $filename)) {
                    $files['ind'] = $file->getPathname();
                } elseif (preg_match('/\.PRE$/i', $filename)) {
                    $files['pre'] = $file->getPathname();
                } elseif (preg_match('/\.DAT$/i', $filename)) {
                    $files['dat'] = $file->getPathname();
                }
            }
        }
        
        $missing = [];
        foreach (['stt', 'mod', 'ind', 'pre', 'dat'] as $required) {
            if (!$files[$required]) {
                $missing[] = strtoupper($required);
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('Archivos esenciales no encontrados en el .DDP: ' . implode(', ', $missing));
        }
        
        return $files;
    }

    private function parseModFile($modFilePath)
    {
        if (!file_exists($modFilePath)) {
            throw new \Exception('Archivo .MOD no encontrado');
        }
        
        $handle = fopen($modFilePath, 'rb');
        if (!$handle) {
            throw new \Exception('No se pudo abrir archivo .MOD');
        }
        
        $modulos = [];
        $moduleId = 1;
        $recordSize = 50;
        
        while (!feof($handle)) {
            $data = fread($handle, $recordSize);
            if (strlen($data) < $recordSize) {
                break;
            }
            
            // Decodificar nombre
            $nombre = $this->cleanLatin1String($data);
            
            // Saltar registros vacíos o muy cortos
            if ($nombre && strlen($nombre) >= 3) {
                // Detectar comentarios
                $esComentario = strpos($nombre, '«') === 0 ||
                               strpos($nombre, '»') === 0 ||
                               strpos($nombre, '"') === 0;
                
                $modulos[] = [
                    'id' => $moduleId,
                    'nombre' => $nombre,
                    'es_comentario' => $esComentario
                ];
                $moduleId++;
            }
        }
        
        fclose($handle);
        return $modulos;
    }

    // ========================================================================
    // MÉTODO PRINCIPAL DE IMPORTACIÓN (ADAPTADO CON LÓGICA ROBUSTA)
    // ========================================================================

    public function importCompleteProject(Request $request)
{
    try {
        $request->validate([
            'extracted_path' => 'required|string',
            'category_name' => 'required|string'
        ]);

        // Generar código único para evitar duplicados
        $codigoUnico = substr(strtoupper($request->category_name), 0, 40) . '_' . time();

        $categoria = ObraCategoria::create([
            'codigo' => $codigoUnico,
            'nombre' => $request->category_name,
            'descripcion' => 'Importado desde archivo .DDP',
            'activo' => true
        ]);

        Log::info("✓ Categoría creada: {$categoria->id} - {$categoria->nombre}");

        // 2. Encontrar y parsear archivos con métodos ROBUSTOS
        $files = $this->findProjectFiles($request->extracted_path);
        
        $insumos = $this->parseIndFileRobust($files['ind']);      // Catálogo con ID real
        $registrosDat = $this->parseDatFileRobust($files['dat']);  // Array indexado completo de DAT
        $modulos = $this->parseModFile($files['mod']);             // Módulos (sin cambios)
        $partidas = $this->parsePreFileRobust($files['pre']);      // Items con offset base

        Log::info("✓ Archivos parseados:");
        Log::info("  - Insumos: " . count($insumos) . " (ID real: 1-" . count($insumos) . ")");
        Log::info("  - Registros DAT: " . count($registrosDat));
        Log::info("  - Partidas: " . count($partidas));

        // 3. Crear/actualizar recursos maestros (indexados por ID REAL)
        $recursosMap = []; // 👈 MAPA indexado por ID REAL del recurso (1, 2, 3... 7815)
        foreach ($insumos as $idReal => $insumo) {  // 👈 $idReal es el ID que aparece en el DAT
            // Obtener precio usando el índice de precio del IND
            $precioDesdeDat = $this->obtenerPrecioInsumo($registrosDat, $insumo['id_relacionado_precio']);
            
            // Limpiar los campos antes de usarlos
            $nombreLimpio = $this->cleanLatin1String($insumo['descripcion']);
            $unidadLimpia = $this->cleanLatin1String($insumo['unidad']);
            
            // Buscar recurso existente por nombre + unidad LIMPIOS
            $recurso = ObraRecursoMaestro::whereRaw('TRIM(nombre) = ?', [$nombreLimpio])
                                        ->whereRaw('TRIM(unidad) = ?', [$unidadLimpia])
                                        ->first();
            
            if ($recurso) {
                $recurso->update([
                    'tipo' => $insumo['tipo'],
                    'precio_referencia' => $precioDesdeDat,
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
                Log::info("  ↳ Recurso existente actualizado: {$recurso->id} - {$nombreLimpio}");
            } else {
                $recurso = ObraRecursoMaestro::create([
                    'codigo' => 'REC_' . str_pad($idReal, 4, '0', STR_PAD_LEFT) . '_' . time(),
                    'nombre' => $insumo['descripcion'],
                    'tipo' => $insumo['tipo'],
                    'unidad' => $insumo['unidad'],
                    'precio_referencia' => $precioDesdeDat,
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
                Log::info("  ↳ Recurso nuevo creado: {$recurso->id} - {$nombreLimpio}");
            }
            
            // 👇 GUARDAR EN MAPA POR ID REAL (no por posición)
            $recursosMap[$idReal] = $recurso->id;
        }

        Log::info("✓ Recursos procesados: " . count($recursosMap));

        // 4. Crear módulos y establecer relación con categoría
        $createdModules = [];
        $orden = 1;
        
        foreach ($modulos as $modulo) {
            if (!$modulo['es_comentario']) {
                $dbModule = ObraModulo::create([
                    'codigo' => 'MOD_' . str_pad($modulo['id'], 3, '0', STR_PAD_LEFT) . '_' . time() . '_' . rand(100, 999),
                    'nombre' => $modulo['nombre'],
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
                
                $categoria->modulos()->attach($dbModule->id, ['orden' => $orden]);
                $createdModules[$modulo['id']] = $dbModule->id;
                Log::info("✓ Módulo creado: {$dbModule->id} - {$dbModule->nombre} (orden: {$orden})");
                $orden++;
            }
        }

        Log::info("✓ Módulos creados: " . count($createdModules));

        // 5. Crear/actualizar items y establecer relaciones con recursos
        $itemsCreados = 0;
        $itemsActualizados = 0;
        $itemsSinModulo = 0;
        $itemsSinRecursos = 0;
        
        // 👇 CORRECCIÓN CRÍTICA: USAR id_relacionado COMO OFFSET, NO $index
        foreach ($partidas as $index => $partida) {
            Log::info("Procesando partida #{$index}: '{$partida['descripcion']}' (offset DAT: {$partida['id_relacionado']}, módulo: {$partida['id_modulo']})");
            
            if (!isset($createdModules[$partida['id_modulo']])) {
                Log::warning("  ⚠️ Módulo {$partida['id_modulo']} no existe");
                $itemsSinModulo++;
                continue;
            }
            
            $moduloId = $createdModules[$partida['id_modulo']];
            $descripcionLimpia = $this->cleanLatin1String($partida['descripcion']);
            $unidadLimpia = $partida['unidad'];
            
            $item = ObraItem::whereRaw('TRIM(descripcion) = ?', [$descripcionLimpia])
                            ->whereRaw('TRIM(unidad) = ?', [$unidadLimpia])
                            ->first();

            if ($item) {
                /*$item->update(['descripcion' => $descripcionLimpia, 'unidad' => $unidadLimpia, 'activo' => true]);
                Log::info("  ↳ Item existente actualizado: {$item->id} - {$descripcionLimpia}");
                $itemsActualizados++;*/
                $codigoBase = 'ITEM_' . str_pad($partida['id'], 4, '0', STR_PAD_LEFT);
                $codigoUnicoItem = $codigoBase;
                $contador = 1;
                
                while (ObraItem::where('codigo', $codigoUnicoItem)->exists()) {
                    $sufijo = '_' . $contador;
                    $codigoUnicoItem = substr($codigoBase, 0, 50 - strlen($sufijo)) . $sufijo;
                    $contador++;
                }
                
                $item = ObraItem::create([
                    'codigo' => $codigoUnicoItem,
                    'descripcion' => $descripcionLimpia,
                    'unidad' => $unidadLimpia,
                    'activo' => true
                ]);
                Log::info("  ↳ Item nuevo creado: {$item->id} - {$descripcionLimpia} (código: {$codigoUnicoItem})");
                $itemsCreados++;
            } else {
                $codigoBase = 'ITEM_' . str_pad($partida['id'], 4, '0', STR_PAD_LEFT);
                $codigoUnicoItem = $codigoBase;
                $contador = 1;
                
                while (ObraItem::where('codigo', $codigoUnicoItem)->exists()) {
                    $sufijo = '_' . $contador;
                    $codigoUnicoItem = substr($codigoBase, 0, 50 - strlen($sufijo)) . $sufijo;
                    $contador++;
                }
                
                $item = ObraItem::create([
                    'codigo' => $codigoUnicoItem,
                    'descripcion' => $descripcionLimpia,
                    'unidad' => $unidadLimpia,
                    'activo' => true
                ]);
                Log::info("  ↳ Item nuevo creado: {$item->id} - {$descripcionLimpia} (código: {$codigoUnicoItem})");
                $itemsCreados++;
            }
            
            // Establecer relación con el módulo
            $modulo = ObraModulo::find($moduloId);
            if ($modulo) {
                $modulo->items()->syncWithoutDetaching([
                    $item->id => ['orden' => $partida['id'], 'rendimiento' => $partida['rendimiento'] ?? 1.0]
                ]);
                Log::info("  → Relación item-módulo establecida (rendimiento: {$partida['rendimiento']})");
            }

            // 👇 CORRECCIÓN DEFINITIVA: USAR id_relacionado COMO OFFSET BASE
            $insumosItem = $this->obtenerInsumosItem(
                $registrosDat,
                $partida['id_relacionado'],  // 👈 OFFSET BASE DEL PRE, NO $index
                $insumos
            );

            if (empty($insumosItem)) {
                Log::warning("  ⚠️ Item {$index} sin recursos (offset: {$partida['id_relacionado']})");
                $itemsSinRecursos++;
                continue;
            }
            
            Log::info("  → Recursos encontrados para item {$index} (offset {$partida['id_relacionado']}): " . count($insumosItem));
            
            $recursosParaRelacionar = [];
            foreach ($insumosItem as $insumo) {
                // 👇 BUSCAR POR ID REAL DEL DAT (no por posición)
                $recursoId = $recursosMap[$insumo['id_insumo']] ?? null;  // $insumo['id_insumo'] = ID real del DAT
                
                if ($recursoId) {
                    $recursosParaRelacionar[$recursoId] = ['rendimiento' => $insumo['coeficiente']];
                    Log::info("    ↳ Recurso relacionado: ID DAT {$insumo['id_insumo']} → BD {$recursoId} (rendimiento: {$insumo['coeficiente']})");
                } else {
                    Log::warning("    ⚠️ Recurso ID {$insumo['id_insumo']} no encontrado en mapa (¿fuera de rango?)");
                }
            }
            
            if (!empty($recursosParaRelacionar)) {
                $item->recursos()->sync($recursosParaRelacionar);
                Log::info("  ✓ Relaciones item-recursos establecidas: " . count($recursosParaRelacionar));
            } else {
                Log::warning("  ⚠️ No se encontraron recursos válidos para relacionar");
            }
        }

        Log::info("✓ Resumen final:");
        Log::info("  - Items creados: {$itemsCreados}");
        Log::info("  - Items actualizados: {$itemsActualizados}");
        Log::info("  - Items sin módulo: {$itemsSinModulo}");
        Log::info("  - Items sin recursos: {$itemsSinRecursos} de " . count($partidas));

        return response()->json([
            'success' => true,
            'message' => 'Proyecto importado exitosamente',
            'categoria_id' => $categoria->id,
            'stats' => [
                'recursos' => count($insumos),
                'modulos' => count($createdModules),
                'items_creados' => $itemsCreados,
                'items_actualizados' => $itemsActualizados,
                'items_sin_recursos' => $itemsSinRecursos
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('❌ Error FATAL: ' . $e->getMessage());
        Log::error('Stack: ' . $e->getTraceAsString());
        
        return response()->json([
            'error' => 'Error al importar el proyecto',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    // ========================================================================
    // OTROS MÉTODOS EXISTENTES (MANTENIDOS)
    // ========================================================================

    public function importDdp(Request $request)
    {
        Log::info('Iniciando importación DDP');
        try {
            // Validar archivo
            $request->validate([
                'file' => 'required|file'
            ]);
            Log::info('Archivo recibido, validación pasada');
            
            // Obtener el archivo directamente sin store()
            $uploadedFile = $request->file('file');
            
            // Verificar que el archivo se haya subido correctamente
            if (!$uploadedFile->isValid()) {
                Log::error('Archivo no válido: ' . $uploadedFile->getErrorMessage());
                return response()->json(['error' => 'Archivo .DDP inválido'], 400);
            }
            
            // Crear directorio temporal
            $tempDir = storage_path('app/temp/' . uniqid());
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Guardar archivo temporalmente
            $tempFilePath = $tempDir . '/archivo.ddp';
            $uploadedFile->move($tempDir, 'archivo.ddp');
            Log::info('Archivo movido a: ' . $tempFilePath);
            
            // Verificar que el archivo exista
            if (!file_exists($tempFilePath)) {
                Log::error('Archivo no existe después de mover: ' . $tempFilePath);
                return response()->json(['error' => 'Error al guardar el archivo temporal'], 500);
            }
            
            $fileSize = filesize($tempFilePath);
            Log::info('Tamaño del archivo: ' . $fileSize . ' bytes');
            if ($fileSize === 0) {
                Log::error('Archivo vacío');
                return response()->json(['error' => 'Archivo .DDP vacío'], 400);
            }
            
            // Verificar firma ZIP
            $fileHeader = file_get_contents($tempFilePath, false, null, 0, 4);
            $zipSignature = "\x50\x4B\x03\x04";
            if ($fileHeader !== $zipSignature) {
                Log::error('Archivo no es ZIP válido. Header: ' . bin2hex($fileHeader));
                return response()->json(['error' => 'Archivo .DDP inválido. No es un archivo ZIP válido.'], 400);
            }
            
            Log::info('Archivo ZIP válido detectado');
            
            // Descomprimir ZIP
            $zip = new ZipArchive();
            $openResult = $zip->open($tempFilePath);
            if ($openResult !== true) {
                $errorMessages = [
                    ZipArchive::ER_EXISTS => 'El archivo ya existe',
                    ZipArchive::ER_INCONS => 'Archivo ZIP inconsistente',
                    ZipArchive::ER_INVAL => 'Argumento inválido',
                    ZipArchive::ER_MEMORY => 'Fallo de memoria',
                    ZipArchive::ER_NOENT => 'No existe',
                    ZipArchive::ER_NOZIP => 'No es un archivo ZIP',
                    ZipArchive::ER_OPEN => 'No se puede abrir el archivo',
                    ZipArchive::ER_READ => 'Error al leer el archivo',
                    ZipArchive::ER_SEEK => 'Error al buscar en el archivo'
                ];
                $errorMessage = $errorMessages[$openResult] ?? 'Error desconocido';
                Log::error('Error al abrir ZIP (' . $openResult . '): ' . $errorMessage);
                return response()->json(['error' => 'Error al abrir el archivo: ' . $errorMessage], 400);
            }
            
            Log::info('ZIP abierto correctamente');
            
            // Extraer contenido
            $extractPath = storage_path('app/temp/extracted/' . uniqid());
            if (!is_dir(dirname($extractPath))) {
                mkdir(dirname($extractPath), 0755, true);
            }
            $zip->extractTo($extractPath);
            $zip->close();
            Log::info('Contenido extraído a: ' . $extractPath);
            
            // Buscar archivo .STT
            $sttFile = $this->findSttFile($extractPath);
            if (!$sttFile) {
                Log::warning('No se encontró archivo .STT en: ' . $extractPath);
                $this->listDirectories($extractPath);
                return response()->json(['error' => 'No se encontró archivo .STT'], 400);
            }
            
            Log::info('Archivo .STT encontrado: ' . $sttFile);
            
            // Extraer nombre de categoría
            $nombreCategoria = $this->extractCategoryName($sttFile);
            if (!$nombreCategoria) {
                Log::error('No se pudo extraer nombre de categoría');
                return response()->json(['error' => 'No se pudo extraer el nombre de la categoría'], 400);
            }
            
            Log::info('Nombre de categoría extraído: ' . $nombreCategoria);
            
            // Limpiar archivos temporales
            // $this->cleanupTempFiles($tempFilePath, $extractPath);
            
            return response()->json([
                'success' => true,
                'nombre_categoria' => $nombreCategoria,
                'extracted_path' => $extractPath,
                'preview' => [
                    'categoria' => $nombreCategoria,
                    'mensaje' => 'Listo para crear categoría'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en importDdp: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error al procesar el archivo'], 500);
        }
    }

    private function findSttFile($extractPath)
    {
        Log::info('Buscando .STT en: ' . $extractPath);
        $prescomPath = $extractPath . '/PRESCOM_2013/temporal';
        Log::info('Ruta PRESCOM_2013: ' . $prescomPath);
        
        if (!is_dir($prescomPath)) {
            Log::warning('Directorio PRESCOM_2013/temporal no existe');
            return null;
        }
        
        // Buscar .STT en mayúsculas
        $files = glob($prescomPath . '/*.STT');
        Log::info('Archivos .STT encontrados: ' . count($files));
        if (!empty($files)) {
            return $files[0];
        }
        
        // Buscar en minúsculas
        $files = glob($prescomPath . '/*.stt');
        if (!empty($files)) {
            return $files[0];
        }
        
        return null;
    }

    private function listDirectories($path)
    {
        if (!is_dir($path)) return;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            Log::info('Archivo encontrado: ' . $file->getPathname());
        }
    }

    private function extractCategoryName($sttFilePath)
    {
        try {
            Log::info('Extrayendo nombre de: ' . $sttFilePath);
            if (!file_exists($sttFilePath)) {
                Log::error('Archivo .STT no existe: ' . $sttFilePath);
                return null;
            }
            
            $handle = fopen($sttFilePath, 'rb');
            if (!$handle) {
                Log::error('No se pudo abrir archivo .STT: ' . $sttFilePath);
                return null;
            }
            
            $data = fread($handle, 70);
            fclose($handle);
            
            if (strlen($data) < 70) {
                Log::warning('Archivo .STT muy corto: ' . strlen($data) . ' bytes');
                return null;
            }
            
            // Decodificar con latin-1 (ISO-8859-1)
            $nombre = mb_convert_encoding(substr($data, 0, 70), 'UTF-8', 'ISO-8859-1');
            $nombre = trim($nombre);
            Log::info('Nombre extraído: "' . $nombre . '"');
            return $nombre ?: null;
        } catch (Exception $e) {
            Log::error('Error extrayendo nombre: ' . $e->getMessage());
            return null;
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}