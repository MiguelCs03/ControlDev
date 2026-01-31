<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name', 'icon', 'route', 'section', 'is_active', 'order',
        'creado_en', 'creado_por', 'modificado_en', 'modificado_por'
    ];

    public function submenus()
    {
        return $this->hasMany(Submenu::class);
    }
}
