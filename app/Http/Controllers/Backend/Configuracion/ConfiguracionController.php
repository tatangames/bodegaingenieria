<?php

namespace App\Http\Controllers\Backend\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }



    public function indexVistaRegistroQuienRecibe(){

        return view('backend.admin.configuracion.quienrecibe.vistaquienrecibe');
    }


    public function tablaRegistroQuienRecibe(){

        $lista = QuienRecibe::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.quienrecibe.tablaquienrecibe', compact('lista'));
    }


    public function registrarNombreQuienRecibe(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new QuienRecibe();
        $dato->nombre = $request->nombre;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionQuienRecibe(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = QuienRecibe::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarNombreQuienRecibe(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(QuienRecibe::where('id', $request->id)->first()){

            QuienRecibe::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }

    }





    //*************************************************************************



    public function indexVistaRegistroQuienEntrega(){

        return view('backend.admin.configuracion.quienentrega.vistaquienentrega');
    }


    public function tablaRegistroQuienEntrega(){

        $lista = QuienEntrega::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.quienentrega.tablaquienentrega', compact('lista'));
    }


    public function registrarNombreQuienEntrega(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new QuienEntrega();
        $dato->nombre = $request->nombre;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionQuienEntrega(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = QuienEntrega::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarNombreQuienEntrega(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(QuienEntrega::where('id', $request->id)->first()){

            QuienEntrega::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }

    }





















}
