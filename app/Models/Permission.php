<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'action',
        'subject',
        'description',
        'creado_en', 'creado_por', 'modificado_en', 'modificado_por'
    ];

    // Relación: Un permiso puede pertenecer a muchos roles (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
