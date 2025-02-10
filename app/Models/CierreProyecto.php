<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreProyecto extends Model
{
    use HasFactory;
    protected $table = 'cierre_proyecto';
    public $timestamps = false;
}
