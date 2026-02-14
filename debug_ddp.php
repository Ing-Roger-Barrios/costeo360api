<?php
// debug_ddp.php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Storage;

// Ruta al archivo .DDP (ajusta según tu caso)
$ddpFile = 'ruta/a/tu/archivo.DDP';

// Crear directorio temporal
$tempDir = storage_path('app/debug_ddp_' . uniqid());
mkdir($tempDir, 0755, true);

// Descomprimir
$zip = new ZipArchive();
if ($zip->open($ddpFile) === true) {
    $zip->extractTo($tempDir);
    $zip->close();
    
    echo "Contenido del archivo .DDP:\n";
    echo "============================\n";
    
    // Listar todos los archivos y directorios
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        echo str_repeat('  ', $iterator->getDepth()) . 
             basename($file->getPathname()) . "\n";
    }
    
    // Limpiar
    system('rm -rf ' . escapeshellarg($tempDir));
} else {
    echo "Error al abrir el archivo .DDP\n";
}