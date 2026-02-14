<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function downloadDesktopAppApi(Request $request)
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Requiere autenticación',
                'message' => 'Debes iniciar sesión para descargar el software'
            ], 401);
        }

        $user = Auth::user();

        // Verificar estado
        if (!$user->active) {
            return response()->json([
                'error' => 'Cuenta inactiva',
                'message' => 'Tu cuenta no está activa. Contacta al administrador.'
            ], 403);
        }

        // Verificar rol
        if (!$user->hasRole('user')) {
            return response()->json([
                'error' => 'Permiso denegado',
                'message' => 'Solo los usuarios con rol "user" pueden descargar el software'
            ], 403);
        }

        // Verificar licencia
        if (!$user->hasValidLicense()) {
            return response()->json([
                'error' => 'Licencia requerida',
                'message' => 'Necesitas una licencia activa para descargar el software',
                'redirect_url' => '/my-licenses'
            ], 403);
        }

        // Verificar archivo
        $filePath = public_path('downloads/costeo360.exe');
        
        if (!file_exists($filePath)) {
            return response()->json([
                'error' => 'Archivo no encontrado',
                'message' => 'El archivo de descarga no está disponible temporalmente'
            ], 404);
        }

        // Registrar descarga
        Log::info('Descarga iniciada', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'license_id' => $user->activeLicense()->id ?? null,
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);

        // Devolver información para la descarga
        return response()->json([
            'download_url' => route('download.desktop.file'),
            'filename' => 'costeo360.exe',
            'filesize' => filesize($filePath),
            'message' => 'Licencia válida. Iniciando descarga...'
        ]);
    }

    public function getDownloadFile(Request $request)
    {
        // Esta ruta solo verifica que el usuario esté autenticado
        if (!Auth::check()) {
            abort(401);
        }

        $filePath = public_path('downloads/costeo360.exe');
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, 'costeo360.exe', [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="costeo360.exe"'
        ]);
    }
}
