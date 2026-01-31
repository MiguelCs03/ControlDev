<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tu_inicio',
        'activo',
        'creado_en', 'creado_por', 'modificado_en', 'modificado_por'
    ];

    // Relación: Un rol puede tener muchos usuarios (many-to-many con tabla role_user)
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    // Relación: Un rol puede tener muchos permisos (many-to-many)
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
}
