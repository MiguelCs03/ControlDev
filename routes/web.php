
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// Rutas de autenticación (necesitan sesión)
Route::post('/api/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/api/logout', [AuthController::class, 'logout'])->name('api.logout');

// Ruta para subir avatar (usa Sanctum para autenticación)
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar'])
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
});

// Catch-all SOLO para GET y al final
Route::get('{any}', function() {
    return view('application');
})->where('any', '.*');
