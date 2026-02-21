<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LicenseController;

// Recursos Maestros
Route::prefix('v1')->group(function () {
    // 👇 RUTAS PÚBLICAS (sin autenticación)
    //Route::prefix('auth')->group(function () {
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('/register', [API\AuthController::class, 'register']);
        Route::post('/login', [API\AuthController::class, 'login']);
        
    });
    Route::post('/auth/forgot-password', [API\AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [API\AuthController::class, 'resetPassword']);
    Route::post('/verification-notification', [API\AuthController::class, 'sendVerificationEmail']);
    Route::get('/auth/verify-email', [API\AuthController::class, 'verifyEmail']);
    // Ruta pública para descargar el archivo (requiere autenticación previa)
    //Route::middleware('auth:sanctum')->get('/download/file', [DownloadController::class, 'getDownloadFile'])
    Route::middleware(['auth:sanctum', 'throttle:10,1'])->get('/download/file', [DownloadController::class, 'getDownloadFile'])
        ->name('download.desktop.file');
    
    // 👇 RUTAS PROTEGIDAS (con autenticación)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [API\AuthController::class, 'logout']);
        Route::get('/auth/me', [API\AuthController::class, 'me']);
        
        // Recursos Maestros
        Route::post('/recursos/bulk-update-prices', [API\ResourceController::class, 'updatePrices']);
        Route::get('/recursos/tipo/{tipo}', [API\ResourceController::class, 'index']);
        Route::apiResource('/recursos', API\ResourceController::class);
        
        // Items
        Route::apiResource('/items', API\ItemController::class);
        Route::get('/items/search', [API\ItemController::class, 'index']);
        
        // Módulos
        Route::apiResource('/modules', API\ModuleController::class);
        Route::post('/modules/{module}/reorder-items', [API\ModuleController::class, 'reorderItems']);
        
        // Categorías
        Route::apiResource('/categories', API\CategoryController::class);

        Route::get(
            '/categories/{category}/presupuesto-estructura',
            [API\CategoryController::class, 'presupuestoEstructura']
        );
        
        // Versiones
        Route::get('/versions/active', [API\VersionController::class, 'getActiveVersion']);
        Route::get('/versions/latest', [API\VersionController::class, 'getLatestVersion']);
        Route::post('/versions/publish-active', [API\VersionController::class, 'publishActiveVersion']);
        Route::get('/versions/published', [API\VersionController::class, 'getPublishedVersion']);
        Route::post('/versions/publish-existing', [API\VersionController::class, 'publishExistingVersion']);
        Route::post('/versions/publish', [API\VersionController::class, 'publish']);
        Route::post('/versions/{version}/activate', [API\VersionController::class, 'activate']);
        Route::apiResource('/versions', API\VersionController::class);
        
        // Regiones
        Route::apiResource('regions', API\RegionController::class);
        
        // Precios regionales
        Route::get('/recursos/{resource}/precios-regionales', [API\ResourceController::class, 'getRegionalPrices']);
        Route::put('/recursos/{resource}/precios-regionales/{region}', [API\ResourceController::class, 'updateRegionalPrice']);
        Route::delete('/recursos/{resource}/precios-regionales/{region}', [API\ResourceController::class, 'deleteRegionalPrice']);
        
        // Actualización masiva
        Route::get('/resources/bulk-prices', [API\ResourceController::class, 'getBulkPrices']);
        Route::post('/resources/bulk-update', [API\ResourceController::class, 'bulkUpdate']);
        
        // Importación
        Route::post('/import/ddp', [API\ImportController::class, 'importDdp']);
        Route::post('/import/complete-project', [API\ImportController::class, 'importCompleteProject']);
        Route::post('/import/modules-items', [API\ImportController::class, 'importModulesAndItems']);
        
        // Rutas de administración
        Route::get('/test', function () {
            return response()->json(['message' => 'Acceso básico']);
        });
        
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin-only', function () {
                return response()->json(['message' => 'Solo para administradores']);
            });
        });

        // Rutas de licencias
        Route::get('/licenses/my', [LicenseController::class, 'getMyLicenses']);
        Route::get('/licenses/active', [LicenseController::class, 'getActiveLicense']);
        Route::post('/licenses', [LicenseController::class, 'createLicenseApi']); // 👈 NUEVA
        Route::post('/licenses/{license}/process-payment', [LicenseController::class, 'processPaymentApi']); // 👈 NUEVA

        // Ruta para verificar y obtener URL de descarga
        Route::get('/download/desktop', [DownloadController::class, 'downloadDesktopAppApi']);


        // Verificación de email
        
        Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);

    });
});