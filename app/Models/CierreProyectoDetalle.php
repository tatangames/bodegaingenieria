<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreProyectoDetalle extends Model
{
    use HasFactory;
    protected $table = 'cierre_proyecto_detalle';
    public $timestamps = false;
}
