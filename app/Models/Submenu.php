<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submenu extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'menu_id', 'name', 'icon', 'route', 'section', 'is_active', 'order',
        'creado_en', 'creado_por', 'modificado_en', 'modificado_por'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function subsubmenus()
    {
        return $this->hasMany(Subsubmenu::class);
    }
}
