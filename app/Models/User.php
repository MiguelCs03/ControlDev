<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'numero',
        'password',
        'fecha_nacimiento',
        'cargo',
        'avatar',
        'creado_en', 
        'creado_por', 
        'modificado_en', 
        'modificado_por'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relación: Un usuario puede tener muchos roles (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Asignar un rol al usuario
    public function assignRole($role)
    {
        return $this->roles()->attach($role);
    }

    // Quitar un rol al usuario
    public function removeRole($role)
    {
        return $this->roles()->detach($role);
    }

    // Verificar si el usuario tiene un rol por nombre
    public function hasRole($roleName)
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    // Verificar si el usuario tiene un permiso por nombre
    public function hasPermission($permissionName)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }

    /**
     * Crear un nuevo usuario con roles
     */
    public static function createUser(array $data)
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        // Hash de contraseña
        if (isset($data['password'])) {
            $data['password'] = \Hash::make($data['password']);
        }

        // Manejar avatar
        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            $data['avatar'] = $data['avatar']->store('avatars', 'public');
        }

        // Asegurar que el usuario quede activo por defecto
        if (!isset($data['activo'])) {
            $data['activo'] = true;
        }

        // Campos de auditoría
        $data['creado_por'] = \Auth::id();
        $data['creado_en'] = now();

        $user = self::create($data);

        // Asignar roles
        if (!empty($roles)) {
            $user->roles()->attach($roles);
        }

        return $user->fresh(['roles']);
    }

    /**
     * Actualizar usuario con roles
     */
    public function updateUser(array $data)
    {
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        // Hash de contraseña si se proporciona
        if (isset($data['password'])) {
            $data['password'] = \Hash::make($data['password']);
        }

        // Campos de auditoría
        $data['modificado_por'] = \Auth::id();
        $data['modificado_en'] = now();

        $this->update($data);

        // Actualizar roles si se proporcionaron
        if ($roles !== null) {
            $this->roles()->sync($roles);
        }

        return $this->load('roles');
    }

    /**
     * Desactivar usuario
     */
    public function deactivateUser()
    {
        $this->activo = false;
        $this->modificado_por = \Auth::id();
        $this->modificado_en = now();
        $this->save();
        return $this;
    }

    /**
     * Activar usuario
     */
    public function activateUser()
    {
        $this->activo = true;
        $this->modificado_por = \Auth::id();
        $this->modificado_en = now();
        $this->save();
        return $this;
    }

    /**
     * Actualizar perfil del usuario
     */
    public function updateUserProfile(array $data)
    {
        $data['modificado_por'] = \Auth::id();
        $data['modificado_en'] = now();
        $this->update($data);
        return $this->fresh();
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function changeUserPassword(string $currentPassword, string $newPassword)
    {
        // Verificar contraseña actual
        if (!\Hash::check($currentPassword, $this->password)) {
            throw new \Exception('La contraseña actual es incorrecta');
        }

        // Actualizar contraseña
        $this->update([
            'password' => \Hash::make($newPassword),
            'modificado_por' => \Auth::id(),
            'modificado_en' => now()
        ]);

        return $this;
    }

    /**
     * Subir avatar del usuario
     */
    public function uploadUserAvatar(\Illuminate\Http\UploadedFile $file)
    {
        $path = $file->store('avatars', 'public');

        // Eliminar imagen anterior si existe
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            \Storage::disk('public')->delete($this->avatar);
        }

        $this->avatar = $path;
        $this->modificado_por = \Auth::id();
        $this->modificado_en = now();
        $this->save();

        return [
            'avatar' => $path,
            'avatar_url' => asset('storage/' . $path)
        ];
    }

    /**
     * Obtener datos del usuario con avatar_url
     */
    public function toArrayWithAvatar()
    {
        $data = $this->toArray();
        if (!empty($this->avatar)) {
            $data['avatar_url'] = asset('storage/' . $this->avatar);
        }
        // Asegurar que activo esté presente
        $data['activo'] = $this->activo ?? true;
        return $data;
    }
}
