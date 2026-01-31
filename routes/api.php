

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Parametros\CargoController;
use App\Http\Controllers\Parametros\CentroCostoController;
use App\Http\Controllers\Parametros\ConceptoRemuneracionController;
use App\Http\Controllers\Parametros\NivelController;
use App\Http\Controllers\Parametros\RelojController;
use App\Http\Controllers\Parametros\SubcentroController;
use App\Http\Controllers\Parametros\TipoJustificacionController;
use App\Http\Controllers\Parametros\UnidadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

// Rutas públicas (sin autenticación)
Route::post('/login', [AuthController::class, 'login']);

// ruta para reestablecimiento/cambio de contraseña 
// Rutas para recuperación de contraseña (Password Reset)
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
     // Ejecuta el envío, pero siempre responde igual para evitar enumeración de usuarios
    Password::sendResetLink($request->only('email'));
    return response()->json([
        'message' => 'se ha enviado un enlace para restablecer la contraseña.'
    ]);
});

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password)
            ])->save();
        }
    );
    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => __($status)])
        : response()->json(['message' => __($status)], 422);
});

// Ruta de prueba para logs
Route::get('/test-log', function () {
    Log::info('Test log funcionando correctamente', ['timestamp' => now()]);

    return response()->json(['message' => 'Log test ejecutado']);
});

// Ruta de depuración para verificar roles/permisos y secciones permitidas del usuario autenticado
Route::get('/debug/auth', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'authenticated' => false,
            'message' => 'No hay usuario autenticado en la sesión actual',
            'db_connection' => config('database.default'),
        ]);
    }

    $user->loadMissing('roles.permissions');

    $adminRoles = ['admin', 'administrador', 'administrator'];
    $isAdmin = false;
    $allowedSections = [];
    $permissionsList = [];
    $roles = [];

    foreach ($user->roles as $role) {
        $roles[] = $role->nombre;
        if (in_array(strtolower($role->nombre), $adminRoles)) {
            $isAdmin = true;
        }

        foreach ($role->permissions as $permission) {
            $permissionsList[] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $permission->action,
                'subject' => $permission->subject,
            ];

            $section = $permission->subject ?: $permission->name;
            if ($section) {
                $allowedSections[] = strtolower(trim($section));
            }
        }
    }

    // dashboard-inicio siempre accesible para autenticados
    $allowedSections[] = 'dashboard-inicio';
    $allowedSections = array_values(array_unique($allowedSections));

    return response()->json([
        'authenticated' => true,
        'db_connection' => config('database.default'),
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'roles' => $roles,
            'isAdmin' => $isAdmin,
        ],
        'permissions' => $permissionsList,
        'allowedSections' => $allowedSections,
    ]);
});

// Rutas protegidas con autenticación Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // Menús dinámicos (requiere autenticación para filtrar según permisos)
    Route::get('/menus', [MenuController::class, 'index']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Test de autenticación (para debugging)
    Route::get('/test-auth', function (Request $request) {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user(),
            'token_name' => $request->user()->currentAccessToken()?->name,
        ]);
    });
    
    // Rutas de usuarios
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::put('/users/{id}/deactivate', [UserController::class, 'deactivate']);
    Route::put('/users/{id}/activate', [UserController::class, 'activate']);

    // Rutas para perfil de usuario
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Cambio de contraseña
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('api.profile.password');

    // Rutas para roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::put('/roles/{id}/deactivate', [RoleController::class, 'deactivate']);
    Route::put('/roles/{id}/activate', [RoleController::class, 'activate']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // Rutas para permisos
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/from-menus', [PermissionController::class, 'indexFromMenus']);
    Route::get('/roles/{roleId}/permissions', [PermissionController::class, 'byRole']);
    Route::put('/roles/{roleId}/permissions', [PermissionController::class, 'syncForRole']);

    // -------------------------RUTAS PARA PARAMETROS RELOJ-------------------------------------
    Route::get('parametros/relojes/activos', [RelojController::class, 'listarActivos']);
    Route::post('parametros/relojes', [RelojController::class, 'crearReloj']);
    Route::put('parametros/relojes/{id}', [RelojController::class, 'editarReloj']);
    Route::delete('parametros/relojes/{id}', [RelojController::class, 'eliminarReloj']);

    //--------------------------RUTAS PARA PARAMETROS CARGO--------------------------------------
    Route::get('parametros/cargos', [CargoController::class, 'listarCargos']);
    Route::post('parametros/cargos', [CargoController::class, 'crearCargo']);
    Route::put('parametros/cargos/{id}', [CargoController::class, 'editarCargo']);
    Route::delete('parametros/cargos/{id}', [CargoController::class, 'eliminarCargo']);

    //---------------------------RUTAS PARA PARAMETROS UNIDAD--------------------------------------
    Route::get('parametros/unidades', [UnidadController::class, 'listarUnidades']);
    Route::post('parametros/unidades', [UnidadController::class, 'crearUnidad']);
    Route::put('parametros/unidades/{id}', [UnidadController::class, 'editarUnidad']);
    Route::delete('parametros/unidades/{id}', [UnidadController::class, 'eliminarUnidad']);

    //---------------------------RUTAS PARA PARAMETROS SUB CENTROS-----------------------------------
    Route::get('parametros/subcentros', [SubcentroController::class, 'listarSubcentros']);
    Route::post('parametros/subcentros', [SubcentroController::class, 'crearSubcentro']);
    Route::put('parametros/subcentros/{id}', [SubcentroController::class, 'editarSubcentro']);
    Route::delete('parametros/subcentros/{id}', [SubcentroController::class, 'eliminarSubcentro']);

    //-----------------------------RUTAS PARA PARAMETROS CENTRO DE COSTO ----------------------------
    Route::get('parametros/centros-costo', [CentroCostoController::class, 'listarCentrosCosto']);
    Route::get('parametros/centros-costo/dependencias', [CentroCostoController::class, 'obtenerDependencias']);
    Route::post('parametros/centros-costo', [CentroCostoController::class, 'crearCentroCosto']);
    Route::put('parametros/centros-costo/{id}', [CentroCostoController::class, 'editarCentroCosto']);
    Route::delete('parametros/centros-costo/{id}', [CentroCostoController::class, 'eliminarCentroCosto']);

    //------------------------------RUTAS PARA PARAMTROS NIVEL----------------------------------------------
    Route::get('parametros/niveles', [NivelController::class, 'listarNiveles']);
    Route::post('parametros/niveles', [NivelController::class, 'crearNivel']);
    Route::put('parametros/niveles/{id}', [NivelController::class, 'editarNivel']);
    Route::delete('parametros/niveles/{id}', [NivelController::class, 'eliminarNivel']);
    
    //------------------------------RUTAS PARA PARAMETROS TIPO DE JUSTIFICACION-----------------------------
    Route::get('parametros/tipos-justificacion', [TipoJustificacionController::class, 'listarTiposJustificacion']);
    Route::post('parametros/tipos-justificacion', [TipoJustificacionController::class, 'crearTipoJustificacion']);
    Route::put('parametros/tipos-justificacion/{id}', [TipoJustificacionController::class, 'editarTipoJustificacion']);
    Route::delete('parametros/tipos-justificacion/{id}', [TipoJustificacionController::class, 'eliminarTipoJustificacion']);

    //-----------------------------RUTAS PARA PARAMETROS CONCEPTO DE RENUMERACION -------------------------------------
    Route::get('parametros/conceptos', [ConceptoRemuneracionController::class, 'listarConceptos']);
    Route::get('parametros/conceptos/opciones', [ConceptoRemuneracionController::class, 'obtenerOpciones']);
    Route::post('parametros/conceptos', [ConceptoRemuneracionController::class, 'crearConcepto']);
    Route::put('parametros/conceptos/{id}', [ConceptoRemuneracionController::class, 'editarConcepto']);
    Route::delete('parametros/conceptos/{id}', [ConceptoRemuneracionController::class, 'eliminarConcepto']);
        
});
