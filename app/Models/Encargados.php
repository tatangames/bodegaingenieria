<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encargados extends Model
{
    use HasFactory;
    protected $table = 'encargados';
    public $timestamps = false;
}
