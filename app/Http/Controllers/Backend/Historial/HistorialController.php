<?php

namespace App\Http\Controllers\Backend\Historial;

use App\Http\Controllers\Controller;
use App\Models\Herramientas;
use App\Models\HistoHerramientaSalida;
use App\Models\HistoHerramientaSalidaDetalle;
use App\Models\HistorialSalidas;
use App\Models\HistorialSalidasDeta;
use App\Models\Materiales;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use App\Models\TipoProyecto;
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


    public function detalleIndexHistorialSalidasHerramientas($id){

        return view('backend.admin.historial.salidaherramientas.detalle.vistadetalle', compact('id'));
    }



    public function detalleTablaHistorialSalidasHerramientas($id){

        $lista = HistoHerramientaSalidaDetalle::where('id_herra_salida', $id)->get();

        foreach ($lista as $dato){

            $infoMate = Herramientas::where('id', $dato->id_herramienta)->first();

            $dato->nommaterial = $infoMate->nombre;
            $dato->codmaterial = $infoMate->codigo;
        }

        return view('backend.admin.historial.salidaherramientas.detalle.tabladetalle', compact('lista'));
    }




    //***********************************************************************************

    public function indexHistorialRepuestosSalida(){

        return view('backend.admin.historial.salidarepuesto.vistasalidarepuesto');
    }


    public function tablaHistorialRepuestosSalida(){

        $lista = HistorialSalidas::orderBy('fecha', 'DESC')->get();

        foreach ($lista as $dato){

            $infoProy = TipoProyecto::where('id', $dato->id_tipoproyecto)->first();

            $dato->nomproy = $infoProy->nombre;
        }

        return view('backend.admin.historial.salidarepuesto.tablasalidarepuesto', compact('lista'));
    }


    public function informacionHistorialSalidaRepuesto(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = HistorialSalidas::where('id', $request->id)->first()){


            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }

    }



    public function actualizarHistorialSalidaRepuesto(Request $request){


        $regla = array(
            'id' => 'required',
            'fecha' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(HistorialSalidas::where('id', $request->id)->first()){

            HistorialSalidas::where('id', $request->id)->update([
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function detalleIndexHistorialSalidas($id){

        return view('backend.admin.historial.salidarepuesto.detalle.vistadetalle', compact('id'));
    }



    public function detalleTablaHistorialSalidas($id){

        $lista = HistorialSalidasDeta::where('id_historial_salidas', $id)->get();

        foreach ($lista as $dato){

            $infoMate = Materiales::where('id', $dato->id_material)->first();

            $dato->nommaterial = $infoMate->nombre;
            $dato->codmaterial = $infoMate->codigo;
        }

        return view('backend.admin.historial.salidarepuesto.detalle.tabladetalle', compact('lista'));
    }


}
