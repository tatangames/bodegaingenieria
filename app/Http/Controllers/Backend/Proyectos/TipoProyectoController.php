<?php

namespace App\Http\Controllers\Backend\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\TipoProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoProyectoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


}
