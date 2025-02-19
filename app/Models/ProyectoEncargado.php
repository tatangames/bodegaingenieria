<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyectoEncargado extends Model
{
    use HasFactory;
    protected $table = 'proyecto_encargado';
    public $timestamps = false;
}
