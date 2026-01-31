<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $users = User::with('roles')->get();
        
        // Transformar para incluir avatar_url
        $users = $users->map(function ($user) {
            return $user->toArrayWithAvatar();
        });
        
        return response()->json($users);
    }

    // Crear usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'numero' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = User::createUser($validated);

        return response()->json($user->toArrayWithAvatar(), 201);
    }

    // Editar usuario
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,'.$id,
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'numero' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'activo' => 'nullable|boolean',
        ]);

        $user->updateUser($validated);

        return response()->json($user);
    }

    // Desactivar usuario
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->deactivateUser();

        return response()->json(['message' => 'Usuario desactivado']);
    }

    // Activar usuario
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->activateUser();

        return response()->json(['message' => 'Usuario activado']);
    }

    // Obtener perfil del usuario autenticado
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('roles');
        
        $userData = $user->toArray();
        
        // Agregar avatar_url si existe el avatar
        if (!empty($user->avatar)) {
            $userData['avatar_url'] = asset('storage/' . $user->avatar);
        }
        
        return response()->json($userData);
    }

    // Actualizar perfil del usuario autenticado
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'numero' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
        ]);

        // Actualizar campos
        $user->fill($validated);
        $user->modificado_por = \Auth::id();
        $user->modificado_en = now();
        $user->save();

        // Preparar respuesta con avatar_url
        $userData = $user->toArray();
        if (!empty($user->avatar)) {
            $userData['avatar_url'] = asset('storage/' . $user->avatar);
        }

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $userData
        ]);
    }

    // Cambiar contraseña del usuario autenticado
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user->changeUserPassword($validated['current_password'], $validated['new_password']);
            return response()->json(['message' => 'Contraseña actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['current_password' => [$e->getMessage()]]
            ], 422);
        }
    }

    // Subir imagen al perfil 
    public function uploadAvatar(Request $request)
    {
        $user = Auth::user(); 
        
        if (!$user) {
            \Log::warning('Avatar upload failed: user not authenticated');
            return response()->json(['message' => 'No autorizado. Por favor, inicie sesión.'], 401);
        }

        $validated = $request->validate([
            'avatar' => 'required|image|max:2048', 
        ]);

        $result = $user->uploadUserAvatar($validated['avatar']);

        return response()->json([
            'message' => 'Avatar subido exitosamente',
            'avatar_url' => $result['avatar_url'],
        ]);
    }
}
