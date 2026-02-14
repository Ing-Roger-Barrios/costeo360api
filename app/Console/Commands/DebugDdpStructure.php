<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class DebugDdpStructure extends Command
{
    protected $signature = 'debug:ddp {file}';
    protected $description = 'Debug DDP file structure';

    public function handle()
    {
        $ddpFile = $this->argument('file');
        
        if (!file_exists($ddpFile)) {
            $this->error("Archivo no encontrado: {$ddpFile}");
            return 1;
        }

        $tempDir = storage_path('app/debug_ddp_' . uniqid());
        mkdir($tempDir, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($ddpFile) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            $this->info("Contenido del archivo .DDP:");
            $this->info("============================");
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                $relativePath = str_replace($tempDir, '', $file->getPathname());
                $this->line(str_repeat('  ', $iterator->getDepth()) . $relativePath);
            }
            
            // Limpiar
            $this->deleteDirectory($tempDir);
            $this->info("\n✓ Análisis completado");
            
        } else {
            $this->error("Error al abrir el archivo .DDP");
            return 1;
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