<?php

namespace App\Http\Controllers\Backend\Historial;

use App\Http\Controllers\Controller;
use App\Models\HistoHerramientaSalida;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HistorialController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function indexHistorialHerramientaSalida(){

        return view('backend.admin.historial.salidaherramientas.vistasalidaherramientas');
    }


    public function tablaHistorialHerramientaSalida(){

        $lista = HistoHerramientaSalida::orderBy('fecha', 'DESC')->get();

        foreach ($lista as $dato){

            $dato->fecha = date("Y-m-d", strtotime($dato->fecha));


            $infoRecibe = QuienRecibe::where('id', $dato->quien_recibe)->first();
            $infoEntrega = QuienEntrega::where('id', $dato->quien_entrega)->first();

            $dato->nomrecibe = $infoRecibe->nombre;
            $dato->nomentrega = $infoEntrega->nombre;
        }

        return view('backend.admin.historial.salidaherramientas.tablasalidaherramientas', compact('lista'));
    }


    public function informacionHistorialSalidaHerramienta(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = HistoHerramientaSalida::where('id', $request->id)->first()){

            $recibe = QuienRecibe::orderBy('nombre')->get();
            $entrega = QuienEntrega::orderBy('nombre')->get();

            return ['success' => 1, 'info' => $lista, 'arrayrecibe' => $recibe, 'arrayentrega' => $entrega];
        }else{
            return ['success' => 2];
        }
    }



    public function actualizarHistorialSalidaHerramienta(Request $request){


        $regla = array(
            'id' => 'required',
            'fecha' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(HistoHerramientaSalida::where('id', $request->id)->first()){

            HistoHerramientaSalida::where('id', $request->id)->update([
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'num_salida' => $request->recibo,
                'quien_recibe' => $request->idrecibe,
                'quien_entrega' => $request->identrega
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }


    }








}
