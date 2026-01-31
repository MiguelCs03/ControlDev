<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsubmenu extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'submenu_id', 'name', 'icon', 'route', 'section', 'is_active', 'order',
        'creado_en', 'creado_por', 'modificado_en', 'modificado_por'
    ];

    public function submenu()
    {
        return $this->belongsTo(Submenu::class);
    }
}
