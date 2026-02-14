<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models;
use App\Models\ObraCategoria;
use App\Models\ObraItem;
use App\Models\ObraRecursoMaestro; // 👈 FALTABA ESTE IMPORT
use App\Models\ObraModulo;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
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
            'extracted_path' => $extractPath, // 👈 AÑADIR ESTA LÍNEA
            'preview' => [
                'categoria' => $nombreCategoria,
                'mensaje' => 'Listo para crear categoría'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error en importDdp: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
        
        // Intentar limpiar cualquier archivo temporal
        /*if (isset($tempFilePath)) {
            $this->cleanupTempFiles($tempFilePath, $extractPath ?? '');
        }*/
        
        return response()->json(['error' => 'Error al procesar el archivo'], 500);
    }
}

private function cleanupTempFiles($zipPath, $extractPath)
{
    // Eliminar archivo ZIP temporal
    if (file_exists($zipPath)) {
        unlink($zipPath);
        rmdir(dirname($zipPath));
    }
    
    // Eliminar directorio extraído
    if (file_exists($extractPath)) {
        $this->deleteDirectory($extractPath);
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
    // En ImportController.php

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
            $nombre = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            $nombre = trim($nombre);

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
    // En ImportController.php

    private function parsePreFile($preFilePath)
    {
        if (!file_exists($preFilePath)) {
            throw new \Exception('Archivo .PRE no encontrado');
        }

        $handle = fopen($preFilePath, 'rb');
        if (!$handle) {
            throw new \Exception('No se pudo abrir archivo .PRE');
        }

        $partidas = [];
        $itemId = 1;
        $recordSize = 460;

        while (!feof($handle)) {
            $data = fread($handle, $recordSize);
            if (strlen($data) < $recordSize) {
                break;
            }

            try {
                // ID módulo (bytes 2-3, uint16 little-endian)
                $moduleIdBytes = substr($data, 2, 2);
                $moduleId = unpack('v', $moduleIdBytes)[1]; // 'v' = unsigned short (little-endian)

                // Descripción (bytes 4-67, 64 bytes)
                $descripcion = mb_convert_encoding(substr($data, 4, 64), 'UTF-8', 'ISO-8859-1');
                $descripcion = trim($descripcion);

                // Saltar descripciones vacías
                if (!$descripcion || strlen($descripcion) < 3) {
                    continue;
                }

                // Unidad (bytes 76-79, 4 bytes)
                $unidad = mb_convert_encoding(substr($data, 76, 4), 'UTF-8', 'ISO-8859-1');
                $unidad = trim($unidad);

                // Rendimiento (bytes 85-92, double little-endian)
                $rendimientoBytes = substr($data, 85, 8);
                $rendimiento = unpack('e', $rendimientoBytes)[1] ?? 0.0; // 'e' = double (little-endian)

                // Validar rendimiento
                if (!is_finite($rendimiento) || abs($rendimiento) > 1e6) {
                    $rendimiento = 0.0;
                }

                $partidas[] = [
                    'id' => $itemId,
                    'id_modulo' => $moduleId,
                    'descripcion' => $descripcion,
                    'unidad' => $unidad,
                    'rendimiento' => $rendimiento
                ];

                $itemId++;

            } catch (\Exception $e) {
                Log::warning('Error parsing partida: ' . $e->getMessage());
                continue;
            }
        }

        fclose($handle);
        return $partidas;
    }
    public function importModulesAndItems(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:obra_categorias,id',
                'extracted_path' => 'required|string'
            ]);

            $categoryId = $request->category_id;
            $extractedPath = $request->extracted_path;

            // Buscar archivos .MOD y .PRE
            $modFile = $this->findFileInPath($extractedPath, ['*.MOD', '*.mod']);
            $preFile = $this->findFileInPath($extractedPath, ['*.PRE', '*.pre']);

            if (!$modFile || !$preFile) {
                return response()->json([
                    'error' => 'Archivos .MOD o .PRE no encontrados'
                ], 400);
            }

            // Parsear archivos
            $modulos = $this->parseModFile($modFile);
            $partidas = $this->parsePreFile($preFile);

            // Crear módulos en la base de datos
            $createdModules = [];
            foreach ($modulos as $modulo) {
                if (!$modulo['es_comentario']) {
                    $dbModule = ObraModulo::create([
                        'categoria_id' => $categoryId,
                        'codigo' => 'MOD_' . str_pad($modulo['id'], 3, '0', STR_PAD_LEFT),
                        'nombre' => $modulo['nombre'],
                        'descripcion' => 'Importado desde .DDP',
                        'activo' => true
                    ]);
                    
                    $createdModules[$modulo['id']] = $dbModule->id;
                }
            }

            // Crear items/partidas
            $createdItems = [];
            foreach ($partidas as $partida) {
                // Verificar que el módulo exista
                if (!isset($createdModules[$partida['id_modulo']])) {
                    continue;
                }

                $dbItem = ObraItem::create([
                    'modulo_id' => $createdModules[$partida['id_modulo']],
                    'codigo' => 'ITEM_' . str_pad($partida['id'], 4, '0', STR_PAD_LEFT),
                    'descripcion' => $partida['descripcion'],
                    'unidad' => $partida['unidad'],
                    'rendimiento' => $partida['rendimiento'],
                    'activo' => true
                ]);

                $createdItems[] = $dbItem;
            }

            return response()->json([
                'success' => true,
                'message' => 'Módulos e items importados exitosamente',
                'stats' => [
                    'modulos' => count($createdModules),
                    'items' => count($createdItems)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing modules and items: ' . $e->getMessage());
            return response()->json(['error' => 'Error al importar módulos e items'], 500);
        }
    }

    private function findFileInPath($path, $patterns)
    {
        foreach ($patterns as $pattern) {
            $files = glob($path . '/' . $pattern);
            if (!empty($files)) {
                return $files[0];
            }
        }
        return null;
    }
    private function parseIndFile($indFilePath)
{
    if (!file_exists($indFilePath)) {
        throw new \Exception('Archivo .IND no encontrado');
    }

    $handle = fopen($indFilePath, 'rb');
    if (!$handle) {
        throw new \Exception('No se pudo abrir archivo .IND');
    }

    $insumos = [];
    $contador = 1;
    $recordSize = 92;

    // Mapa de tipos
    $tipoMap = [
        0x4D => 'Material',    // 'M'
        0x4F => 'ManoObra',   // 'O' 
        0x45 => 'Equipo'      // 'E'
    ];

    while (!feof($handle)) {
        $data = fread($handle, $recordSize);
        if (strlen($data) < $recordSize) {
            break;
        }

        try {
            // ID relacionado (bytes 88-91, uint32 little-endian)
            $idRelacionadoBytes = substr($data, 88, 4);
            $idRelacionado = unpack('V', $idRelacionadoBytes)[1]; // 'V' = unsigned long (little-endian)

            // Tipo (byte 2)
            $tipoByte = ord($data[2]);
            $tipo = $tipoMap[$tipoByte] ?? 'Desconocido';

            // Descripción (bytes 3-74, 72 bytes)
            $descripcion = mb_convert_encoding(substr($data, 3, 70), 'UTF-8', 'ISO-8859-1');
            $descripcion = trim($descripcion);

            // Saltar descripciones vacías
            if (!$descripcion || strlen($descripcion) < 3) {
                continue;
            }

            // Unidad (bytes 75-78, 4 bytes)
            $unidad = mb_convert_encoding(substr($data, 75, 4), 'UTF-8', 'ISO-8859-1');
            $unidad = trim($unidad);
            $unidad = rtrim($unidad, "\x00"); // Eliminar null terminators

            // Precio (bytes 80-87, double little-endian)
            $precioBytes = substr($data, 80, 8);
            $precio = unpack('e', $precioBytes)[1] ?? 0.0;

            if (!is_finite($precio) || abs($precio) > 1e6) {
                $precio = 0.0;
            }

            $insumos[] = [
                'id' => $contador,
                'id_relacionado' => $idRelacionado,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'unidad' => $unidad,
                'precio' => $precio
            ];

            $contador++;

        } catch (\Exception $e) {
            Log::warning('Error parsing insumo: ' . $e->getMessage());
            continue;
        }
    }

    fclose($handle);
    return $insumos;
}
// En ImportController.php

// En ImportController.php

/*private function parseDatFile($datFilePath)
{
    if (!file_exists($datFilePath)) {
        throw new \Exception('Archivo .DAT no encontrado');
    }

    $handle = fopen($datFilePath, 'rb');
    if (!$handle) {
        throw new \Exception('No se pudo abrir archivo .DAT');
    }

    try {
        // Leer precios de insumos (8 bytes por registro: 4 bytes ID + 4 bytes precio)
        $preciosInsumos = [];
        
        while (!feof($handle)) {
            $data = fread($handle, 8);
            if (strlen($data) < 8) {
                break;
            }
            
            // ID relacionado (bytes 0-3, uint32 little-endian)
            $idRelacionado = unpack('V', substr($data, 0, 4))[1];
            
            // Precio (bytes 4-7, float32 little-endian)
            $precio = unpack('f', substr($data, 4, 4))[1] ?? 0.0;
            
            if (!is_finite($precio) || abs($precio) > 1e6) {
                $precio = 0.0;
            }
            
            $preciosInsumos[] = $precio;
        }
        
        return [
            'precios_insumos' => $preciosInsumos,
            'recursos_por_item' => [] // Implementaremos esto después
        ];
        
    } finally {
        fclose($handle);
    }
}*/

// En ImportController.php

private function parsePreciosInsumos($handle, $insumos)
{
    $precios = [];
    
    foreach ($insumos as $insumo) {
        $precioBytes = fread($handle, 8); // 8 bytes totales por registro
        if (strlen($precioBytes) < 8) {
            break;
        }
        
        // ID relacionado (bytes 0-3, uint32 little-endian)
        $idRelacionado = unpack('V', substr($precioBytes, 0, 4))[1];
        
        // Precio (bytes 4-7, float32 little-endian)
        $precio = unpack('f', substr($precioBytes, 4, 4))[1] ?? 0.0;
        
        if (!is_finite($precio) || abs($precio) > 1e6) {
            $precio = 0.0;
        }
        
        $precios[$idRelacionado] = $precio;
    }
    
    return $precios;
}
private function findProjectFiles($extractedPath)
{
    Log::info('Contenido de la ruta extraída:');
    
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($extractedPath, \RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            Log::info('Archivo encontrado: ' . $file->getPathname());
        }
    }

    // Buscar recursivamente todos los archivos necesarios
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

    // Verificación detallada
    $missing = [];
    foreach (['stt', 'mod', 'ind', 'pre', 'dat'] as $required) {
        if (!$files[$required]) {
            $missing[] = strtoupper($required);
        }
    }
    
    if (!empty($missing)) {
        Log::error('Archivos faltantes: ' . implode(', ', $missing));
        throw new \Exception('Archivos esenciales no encontrados en el .DDP');
    }

    return $files;
}
private function crearRecursosMaestros($insumos, $preciosInsumos)
{
    $recursosCreados = [];
    
    foreach ($insumos as $insumo) {
        $recurso = ObraRecursoMaestro::create([
            'codigo' => 'REC_' . str_pad($insumo['id'], 4, '0', STR_PAD_LEFT),
            'nombre' => $insumo['descripcion'],
            'tipo' => $insumo['tipo'],
            'unidad' => $insumo['unidad'],
            'precio_referencia' => $preciosInsumos[$insumo['id_relacionado']] ?? $insumo['precio'],
            'descripcion' => 'Importado desde .DDP',
            'activo' => true
        ]);
        
        $recursosCreados[] = $recurso;
    }
    
    return $recursosCreados;
}

private function crearModulos($modulos, $categoriaId)
{
    $modulosCreados = [];
    
    foreach ($modulos as $modulo) {
        if (!$modulo['es_comentario']) {
            $dbModule = ObraModulo::create([
                'categoria_id' => $categoriaId,
                'codigo' => 'MOD_' . str_pad($modulo['id'], 3, '0', STR_PAD_LEFT),
                'nombre' => $modulo['nombre'],
                'descripcion' => 'Importado desde .DDP',
                'activo' => true
            ]);
            
            $modulosCreados[$modulo['id']] = $dbModule->id;
        }
    }
    
    return $modulosCreados;
}

private function crearItems($partidas, $modulosCreados, $recursosCreados, $recursosPorItem)
{
    $itemsCreados = [];
    
    foreach ($partidas as $partida) {
        if (!isset($modulosCreados[$partida['id_modulo']])) {
            continue;
        }

        $dbItem = ObraItem::create([
            'modulo_id' => $modulosCreados[$partida['id_modulo']],
            'codigo' => 'ITEM_' . str_pad($partida['id'], 4, '0', STR_PAD_LEFT),
            'descripcion' => $partida['descripcion'],
            'unidad' => $partida['unidad'],
            'rendimiento' => $partida['rendimiento'],
            'activo' => true
        ]);

        $itemsCreados[] = $dbItem;
    }
    
    return $itemsCreados;
}

// En ImportController.php
private function parseRecursosPorItem($handle, $ultimoIdRecurso)
{
    $recursosPorItem = [];
    $itemId = 1;
    
    while (!feof($handle)) {
        $itemRecursos = [
            'materiales' => [],
            'mano_obra' => [],
            'equipos' => []
        ];
        
        $hasData = false;
        $tipos = ['materiales', 'mano_obra', 'equipos'];
        
        foreach ($tipos as $tipo) {
            $blockData = fread($handle, 240);
            if (strlen($blockData) < 240) {
                // Fin del archivo
                if (!$hasData) {
                    return $recursosPorItem;
                }
                break;
            }
            
            // Parsear el bloque de 240 bytes
            $recursos = $this->parseBloqueRecursos240($blockData, $ultimoIdRecurso);
            if (!empty($recursos)) {
                $itemRecursos[$tipo] = $recursos;
                $hasData = true;
            }
        }
        
        if ($hasData) {
            $recursosPorItem[$itemId] = $itemRecursos;
            $itemId++;
        } else {
            // Si no hay datos en ningún bloque, asumimos fin del archivo
            break;
        }
    }
    
    return $recursosPorItem;
}
private function parseBloqueRecursos240($blockData, $ultimoIdRecurso)
{
    $recursos = [];
    $offset = 0;
    $recordSize = 8; // 4 bytes ID + 4 bytes rendimiento
    
    while ($offset + $recordSize <= 240) {
        $registro = substr($blockData, $offset, $recordSize);
        
        // Verificar si el registro está vacío
        if (unpack('L', substr($registro, 0, 4))[1] === 0) {
            $offset += $recordSize;
            continue;
        }
        
        // Extraer ID del recurso
        $idRecurso = unpack('V', substr($registro, 0, 4))[1]; // uint32 LE
        
        // Verificar si el ID es válido (menor o igual al último ID de recursos)
        if ($idRecurso > $ultimoIdRecurso || $idRecurso <= 0) {
            // Este registro marca el inicio de un nuevo item
            break;
        }
        
        // Extraer rendimiento
        $rendimiento = unpack('f', substr($registro, 4, 4))[1] ?? 0.0;
        if (!is_finite($rendimiento) || abs($rendimiento) > 1e6) {
            $rendimiento = 0.0;
        }
        
        $recursos[] = [
            'recurso_id' => $idRecurso,
            'rendimiento' => $rendimiento
        ];
        
        $offset += $recordSize;
    }
    
    return $recursos;
}
private function parseDatFile($datFilePath, $insumos)
{
    if (!file_exists($datFilePath)) {
        throw new \Exception('Archivo .DAT no encontrado');
    }

    // Crear mapa de tipos de insumos (ID => Tipo)
    $tiposInsumos = [];
    foreach ($insumos as $insumo) {
        $tiposInsumos[$insumo['id_relacionado']] = $insumo['tipo'];
    }

    $handle = fopen($datFilePath, 'rb');
    if (!$handle) {
        throw new \Exception('No se pudo abrir archivo .DAT');
    }

    try {
        // Parte A: Precios correlativos
        $precios = [];
        $lastId = 0;
        
        while (!feof($handle)) {
            $data = fread($handle, 8);
            if (strlen($data) < 8) {
                break;
            }
            
            $idVal = unpack('V', substr($data, 0, 4))[1]; // uint32 LE
            $precio = unpack('f', substr($data, 4, 4))[1] ?? 0.0;
            
            if (!is_finite($precio) || abs($precio) > 1e6) {
                $precio = 0.0;
            }
            
            // Detectar ruptura correlativa
            if ($lastId > 0 && $idVal != $lastId + 1) {
                // Retroceder 8 bytes para que este registro sea parte de las relaciones
                fseek($handle, -8, SEEK_CUR);
                break;
            }
            
            $precios[] = $precio;
            $lastId = $idVal;
        }

        // Parte B: Relaciones por item
        $relaciones = [];
        $itemActual = 1;
        $estado = 'MATERIAL'; // Siempre empieza con Material

        while (!feof($handle)) {
            $bloque = fread($handle, 240);
            if (strlen($bloque) < 240) {
                break;
            }
            
            // Bloque vacío
            if ($this->esBloqueVacio($bloque)) {
                if ($estado === 'MATERIAL') {
                    // Item sin materiales
                    $estado = 'POST_MATERIAL';
                    continue;
                } else {
                    // Fin del item actual
                    $itemActual++;
                    $estado = 'MATERIAL';
                    continue;
                }
            }
            
            // Detectar tipo del bloque
            $idInicio = $this->primerIdValido($bloque);
            if ($idInicio === null) {
                continue;
            }
            
            $tipoInicio = $tiposInsumos[$idInicio] ?? 'Desconocido';
            
            // Nuevo item detectado
            if ($tipoInicio === 'Material' && $estado !== 'MATERIAL') {
                $itemActual++;
                $estado = 'MATERIAL';
            }
            
            // Procesar registros del bloque
            for ($i = 0; $i < 240; $i += 8) {
                $chunk = substr($bloque, $i, 8);
                
                if ($this->esRegistroVacio($chunk)) {
                    continue;
                }
                
                $insId = unpack('V', substr($chunk, 0, 4))[1];
                $rendimiento = unpack('f', substr($chunk, 4, 4))[1] ?? 0.0;
                
                if (!is_finite($rendimiento) || abs($rendimiento) > 1e6) {
                    $rendimiento = 0.0;
                }
                
                if ($insId == 0) {
                    continue;
                }
                
                $tipo = $tiposInsumos[$insId] ?? 'Desconocido';
                
                $relaciones[] = [
                    'item' => $itemActual,
                    'tipo' => $tipo,
                    'id_insumo' => $insId,
                    'rendimiento' => $rendimiento
                ];
            }
            
            // Actualizar estado
            if ($tipoInicio === 'Material') {
                $estado = 'POST_MATERIAL';
            } else {
                $estado = 'POST_OTROS';
            }
        }
        
        return [
            'precios_insumos' => $precios,
            'relaciones' => $relaciones
        ];
        
    } finally {
        fclose($handle);
    }
}
private function esBloqueVacio($bloque)
{
    return $bloque === str_repeat("\x00", 240);
}

private function esRegistroVacio($registro)
{
    return $registro === str_repeat("\x00", 8);
}

private function primerIdValido($bloque)
{
    for ($i = 0; $i < 240; $i += 8) {
        $chunk = substr($bloque, $i, 8);
        if (!$this->esRegistroVacio($chunk)) {
            $insId = unpack('V', substr($chunk, 0, 4))[1];
            if ($insId > 0) {
                return $insId;
            }
        }
    }
    return null;
}
/*private function parseRecursosPorItem($handle)
{
    $recursosPorItem = [];
    $itemId = 1;
    
    while (!feof($handle)) {
        $itemRecursos = [
            'materiales' => [],
            'mano_obra' => [],
            'equipos' => []
        ];
        
        $hasData = false;
        $tipos = ['materiales', 'mano_obra', 'equipos'];
        
        foreach ($tipos as $tipo) {
            $blockData = fread($handle, 240);
            if (strlen($blockData) < 240) {
                // Fin del archivo
                if (!$hasData) {
                    return $recursosPorItem;
                }
                break;
            }
            
            // Verificar si el bloque contiene datos reales
            $recursos = $this->parseBloqueRecursos240($blockData, $tipo);
            if (!empty($recursos)) {
                $itemRecursos[$tipo] = $recursos;
                $hasData = true;
            }
        }
        
        if ($hasData) {
            $recursosPorItem[$itemId] = $itemRecursos;
            $itemId++;
        } else {
            // Si no hay datos en ningún bloque, asumimos fin del archivo
            break;
        }
    }
    
    return $recursosPorItem;
}*/

/*private function parseBloqueRecursos240($blockData, $tipoRecurso)
{
    $recursos = [];
    
    // Analizar los 240 bytes según la estructura de Prescom
    // Esta es una implementación básica - necesitarás ajustar según la estructura exacta
    
    // Verificar si el bloque está vacío (todos ceros o espacios)
    if (trim($blockData) === '' || unpack('L', substr($blockData, 0, 4))[1] === 0) {
        return $recursos;
    }
    
    // Aquí iría la lógica específica para parsear los 240 bytes
    // Por ahora, devolvemos información básica para debugging
    $recursos[] = [
        'tipo' => $tipoRecurso,
        'datos_hex' => bin2hex(substr($blockData, 0, 32)), // Primeros 32 bytes para debugging
        'tamaño_bloque' => strlen($blockData)
    ];
    
    return $recursos;
}*/


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

        // 2. Encontrar y parsear archivos
        $files = $this->findProjectFiles($request->extracted_path);
        $insumos = $this->parseIndFile($files['ind']);
        $modulos = $this->parseModFile($files['mod']);
        $partidas = $this->parsePreFile($files['pre']);
        $datosDat = $this->parseDatFile($files['dat'], $insumos);
        $preciosDat = $datosDat['precios_insumos'];
        $relaciones = $datosDat['relaciones'];

        // 3. Crear recursos maestros
        // Reemplazar la sección de creación de recursos
        // 3. Crear/actualizar recursos maestros (únicos por descripcion + unidad)
        // 3. Crear/actualizar recursos maestros (únicos por nombre + unidad)
        foreach ($insumos as $index => $insumo) {
            // Obtener precio del .DAT (mismo orden que .IND)
            $precioDesdeDat = isset($preciosDat[$index]) ? $preciosDat[$index] : $insumo['precio'];
            // Limpiar los campos antes de usarlos
            $nombreLimpio = $this->cleanString($insumo['descripcion']);
            $unidadLimpia = $this->cleanString($insumo['unidad']);
            // Buscar recurso existente por nombre + unidad LIMPIOS
            $recurso = ObraRecursoMaestro::whereRaw('TRIM(nombre) = ?', [$nombreLimpio])
                                        ->whereRaw('TRIM(unidad) = ?', [$unidadLimpia])
                                        ->first();
            
            if ($recurso) {
                // Si existe, solo actualizar campos que pueden cambiar
                $recurso->update([
                    'tipo' => $insumo['tipo'],
                    'precio_referencia' => $precioDesdeDat,
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
            } else {
                // Si no existe, crear nuevo con código único
                $recurso = ObraRecursoMaestro::create([
                    'codigo' => 'REC_' . str_pad($insumo['id'], 4, '0', STR_PAD_LEFT) . '_' . time(),
                    'nombre' => $insumo['descripcion'],
                    'tipo' => $insumo['tipo'],
                    'unidad' => $insumo['unidad'],
                    'precio_referencia' => $precioDesdeDat,
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
            }
        }

        // 4. Crear módulos y establecer relación con categoría
        $createdModules = [];
        $orden = 1;
        
        foreach ($modulos as $modulo) {
            if (!$modulo['es_comentario']) {
                // SIEMPRE crear un nuevo módulo
                $dbModule = ObraModulo::create([
                    'codigo' => 'MOD_' . str_pad($modulo['id'], 3, '0', STR_PAD_LEFT) . '_' . time() . '_' . rand(100, 999),
                    'nombre' => $modulo['nombre'],
                    'descripcion' => 'Importado desde .DDP',
                    'activo' => true
                ]);
                
                // Establecer relación con la categoría
                $categoria->modulos()->attach($dbModule->id, ['orden' => $orden]);
                
                $createdModules[$modulo['id']] = $dbModule->id;
                $orden++;
            }
        }

        // 5. Crear items
        // 5. Crear/actualizar items (únicos por descripcion + unidad)
        // 5. Crear/actualizar items (únicos por descripcion + unidad)
        // 5. Crear/actualizar items y establecer relaciones con rendimiento
        foreach ($partidas as $partida) {
            if (isset($createdModules[$partida['id_modulo']])) {
                $moduloId = $createdModules[$partida['id_modulo']];
                
                // Limpiar campos
                $descripcionLimpia = $this->cleanString($partida['descripcion']);
                $unidadLimpia = $this->cleanString($partida['unidad']);
                
                // Buscar/crear item por descripcion + unidad
                $item = ObraItem::whereRaw('TRIM(descripcion) = ?', [$descripcionLimpia])
                                ->whereRaw('TRIM(unidad) = ?', [$unidadLimpia])
                                ->first();

                if ($item) {
                    // Si existe, actualizar sus datos
                    $item->update([
                        'descripcion' => $descripcionLimpia,
                        'unidad' => $unidadLimpia,
                        'rendimiento' => $partida['rendimiento'],
                        'activo' => true
                    ]);
                } else {
                    // Si no existe, crear nuevo con código único
                    $codigoBase = 'ITEM_' . str_pad($partida['id'], 4, '0', STR_PAD_LEFT);
                    $codigoUnico = $codigoBase;
                    $contador = 1;
                    
                    // Verificar si el código ya existe y ajustar
                    while (ObraItem::where('codigo', $codigoUnico)->exists()) {
                        $sufijo = '_' . $contador;
                        $codigoUnico = substr($codigoBase, 0, 50 - strlen($sufijo)) . $sufijo;
                        $contador++;
                    }
                    
                    $item = ObraItem::create([
                        'codigo' => $codigoUnico,
                        'descripcion' => $descripcionLimpia,
                        'unidad' => $unidadLimpia,
                        'rendimiento' => $partida['rendimiento'],
                        'activo' => true
                    ]);
                }
                
                // Establecer relación con el módulo incluyendo rendimiento
                $modulo = ObraModulo::find($moduloId);
                $modulo->items()->syncWithoutDetaching([
                    $item->id => [
                        'orden' => $partida['id'],
                        'rendimiento' => $partida['rendimiento'] // 👈 RENDIMIENTO DE LA RELACIÓN
                    ]
                ]);

                // 👇 ESTABLECER RELACIONES CON RECURSOS
                $itemIdIndex = $index + 1; // Los items en .DAT empiezan desde 1
                if (isset($datosDat['recursos_por_item'][$itemIdIndex])) {
                    $relaciones = $datosDat['recursos_por_item'][$itemIdIndex];
                    
                    // Combinar todos los recursos del item
                    $todosRecursos = array_merge(
                        $relaciones['materiales'],
                        $relaciones['mano_obra'], 
                        $relaciones['equipos']
                    );
                    
                    if (!empty($todosRecursos)) {
                        $recursosParaRelacionar = [];
                        foreach ($todosRecursos as $recursoRel) {
                            // Encontrar el recurso real por su posición en el .IND
                            if (isset($insumos[$recursoRel['recurso_id'] - 1])) {
                                $recursoNombre = $insumos[$recursoRel['recurso_id'] - 1]['descripcion'];
                                $recursoUnidad = $insumos[$recursoRel['recurso_id'] - 1]['unidad'];
                                
                                $recursoReal = ObraRecursoMaestro::whereRaw('TRIM(nombre) = ?', [$recursoNombre])
                                                                ->whereRaw('TRIM(unidad) = ?', [$recursoUnidad])
                                                                ->first();
                                
                                if ($recursoReal) {
                                    $recursosParaRelacionar[$recursoReal->id] = [
                                        'rendimiento' => $recursoRel['rendimiento']
                                    ];
                                }
                            }
                        }
                        
                        if (!empty($recursosParaRelacionar)) {
                            $item->recursos()->sync($recursosParaRelacionar);
                        }
                    }
                }


            }
        }
        // Establecer relaciones item → recursos
        $this->crearRelacionesItemRecursos($relaciones, $partidas, $insumos);
        $extractedPath = $request->extracted_path;

         // ✅ LIMPIAR SOLO DESPUÉS DE IMPORTAR TODO
         //$this->cleanupTempFiles(storage_path('app/temp/' . basename(dirname($extractedPath))), $extractedPath);


        return response()->json([
            'success' => true,
            'message' => 'Proyecto importado exitosamente',
            'categoria_id' => $categoria->id,
            'stats' => [
                'recursos' => count($insumos),
                'modulos' => count($createdModules),
                'items' => count($partidas)
            ]
        ]);

    } catch (\Exception $e) {
        // ✅ TAMBIÉN LIMPIAR EN CASO DE ERROR
        if (isset($extractedPath)) {
            $this->cleanupTempFiles(storage_path('app/temp/' . basename(dirname($extractedPath))), $extractedPath);
        }
        return response()->json(['error' => 'Error al importar el proyecto: ' . $e->getMessage()], 500);
    }
}
private function crearRelacionesItemRecursos($relaciones, $partidas, $insumos)
{
    // Agrupar relaciones por item
    $relacionesPorItem = [];
    foreach ($relaciones as $rel) {
        $itemId = $rel['item'];
        if (!isset($relacionesPorItem[$itemId])) {
            $relacionesPorItem[$itemId] = [];
        }
        $relacionesPorItem[$itemId][] = $rel;
    }
    
    // Procesar cada item
    foreach ($partidas as $index => $partida) {
        $itemId = $index + 1; // Los items en .DAT empiezan desde 1
        
        if (isset($relacionesPorItem[$itemId])) {
            // Buscar el item real en la base de datos
            $descripcionLimpia = $this->cleanString($partida['descripcion']);
            $unidadLimpia = $this->cleanString($partida['unidad']);
            
            $item = ObraItem::whereRaw('TRIM(descripcion) = ?', [$descripcionLimpia])
                            ->whereRaw('TRIM(unidad) = ?', [$unidadLimpia])
                            ->first();
            
            if ($item) {
                $recursosParaRelacionar = [];
                
                foreach ($relacionesPorItem[$itemId] as $rel) {
                    // Encontrar el recurso por ID relacionado
                    foreach ($insumos as $insumo) {
                        if ($insumo['id_relacionado'] == $rel['id_insumo']) {
                            $recursoNombre = $this->cleanString($insumo['descripcion']);
                            $recursoUnidad = $this->cleanString($insumo['unidad']);
                            
                            $recurso = ObraRecursoMaestro::whereRaw('TRIM(nombre) = ?', [$recursoNombre])
                                                        ->whereRaw('TRIM(unidad) = ?', [$recursoUnidad])
                                                        ->first();
                            
                            if ($recurso) {
                                $recursosParaRelacionar[$recurso->id] = [
                                    'rendimiento' => $rel['rendimiento']
                                ];
                            }
                            break;
                        }
                    }
                }
                
                if (!empty($recursosParaRelacionar)) {
                    $item->recursos()->sync($recursosParaRelacionar);
                }
            }
        }
    }
}
private function cleanString($string)
{
    if (!$string) {
        return '';
    }
    
    // Eliminar caracteres de control (ASCII 0-31) excepto espacios, tabs y newlines
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    
    // Eliminar espacios múltiples y trim
    $clean = preg_replace('/\s+/', ' ', $clean);
    
    return trim($clean);
}

}