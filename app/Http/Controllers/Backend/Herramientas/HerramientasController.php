<?php

namespace App\Http\Controllers\Backend\Herramientas;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\HerramientaPendiente;
use App\Models\Herramientas;
use App\Models\HistoHerramientaDescartada;
use App\Models\HistoHerramientaRegistro;
use App\Models\HistoHerramientaRegistroDeta;
use App\Models\HistoHerramientaReingreso;
use App\Models\HistoHerramientaSalida;
use App\Models\HistoHerramientaSalidaDetalle;
use App\Models\HistorialEntradas;
use App\Models\HistorialSalidas;
use App\Models\HistorialSalidasDeta;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HerramientasController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }


    public function indexInventarioHerramientas(){

        $lUnidad = UnidadMedida::orderBy('nombre')->get();

        return view('backend.admin.herramientas.inventario.vistainventario', compact('lUnidad'));
    }

    public function tablaInventarioHerramientas(){

        $lista = Herramientas::orderBy('nombre', 'ASC')->get();

        foreach ($lista as $item) {
            $medida = '';
            if($dataUnidad = UnidadMedida::where('id', $item->id_medida)->first()){
                $medida = $dataUnidad->nombre;
            }

            $item->medida = $medida;
        }

        return view('backend.admin.herramientas.inventario.tablainventario', compact('lista'));
    }

    public function nuevaHerramienta(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new Herramientas();
        $dato->id_medida = $request->unidad;
        $dato->nombre = $request->nombre;
        $dato->codigo = $request->codigo;
        $dato->cantidad = 0;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionHerramienta(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Herramientas::where('id', $request->id)->first()){

            $arrayUnidad = UnidadMedida::orderBy('nombre', 'ASC')->get();

            return ['success' => 1, 'herramienta' => $lista, 'unidad' => $arrayUnidad];
        }else{
            return ['success' => 2];
        }
    }


    public function editarMaterial(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        Herramientas::where('id', $request->id)->update([
            'id_medida' => $request->unidad,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo
        ]);

        return ['success' => 1];
    }





    //****************************************************************************************



    public function indexRegistroHerramientas(){

        return view('backend.admin.herramientas.registro.vistaregistroherramientas');
    }


    public function buscadorHerramienta(Request $request){

        if($request->get('query')){
            $query = $request->get('query');
            $data = Herramientas::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo', 'LIKE', "%{$query}%")
                ->get();

            foreach ($data as $dd){
                if($info = UnidadMedida::where('id', $dd->id_medida)->first()){
                    $dd->medida = "- " . $info->nombre;
                }else{
                    $dd->medida = "";
                }

                if($dd->codigo != null){
                    $dd->code = "- " . $dd->codigo;
                }else{
                    $dd->code = "";
                }
            }

            $output = '<ul class="dropdown-menu" style="display:block; position:relative;">';
            $tiene = true;
            foreach($data as $row){

                // si solo hay 1 fila, No mostrara el hr, salto de linea
                if(count($data) == 1){
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px">'.$row->nombre . '  ' .$row->medida . ' ' .$row->code .'</a></li>
                ';
                    }
                }

                else{
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px">'.$row->nombre . ' ' .$row->medida . ' ' .$row->code .'</a></li>
                   <hr>
                ';
                    }
                }
            }
            $output .= '</ul>';
            if($tiene){
                $output = '';
            }
            echo $output;
        }
    }



    public function guardarEntradaHerramienta(Request $request){

        $rules = array(
            'fecha' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();


        try {

            // PRIMERO GUARDAR UN HISTORIAL
            $histoEntrada = new HistoHerramientaRegistro();
            $histoEntrada->fecha = $request->fecha;
            $histoEntrada->descripcion = $request->descripcion;
            $histoEntrada->save();


            for ($i = 0; $i < count($request->cantidad); $i++) {

                $nuevoIngreso = new HistoHerramientaRegistroDeta();
                $nuevoIngreso->id_herra_registro = $histoEntrada->id;
                $nuevoIngreso->id_herramienta = $request->datainfo[$i];
                $nuevoIngreso->cantidad = $request->cantidad[$i];
                $nuevoIngreso->save();

                // SUMAR CANTIDAD

                $detalle = Herramientas::where('id', $request->datainfo[$i])->first();
                $suma = $detalle->cantidad + $request->cantidad[$i];

                Herramientas::where('id', $detalle->id)->update([
                    'cantidad' => $suma,
                ]);
            }


            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('err ' . $e);
            DB::rollback();
            return ['success' => 2];
        }
    }





    //********************************************************************************



    // SALIDA DE HERRAMIENTAS PARA USUARIO

    public function indexSalidaHerramientas(){

        $arrayRecibe = QuienRecibe::orderby('nombre')->get();
        $arrayEntrega = QuienEntrega::orderby('nombre')->get();

        return view('backend.admin.herramientas.salidausuario.vistasalidausuario', compact('arrayRecibe', 'arrayEntrega'));
    }


    public function bloqueCantidadHerramienta($id){

        // obtener todas las entradas y obtener cada fila de cantidad

        $lista = Herramientas::where('id', $id)
            ->where('cantidad', '>', 0)
            ->get();

        $dataArray = array();

        $hayCantidad = false;

        foreach ($lista as $dd){

            if($dd->cantidad > 0){
                $dataArray[] = [
                    'id' => $dd->id,
                    'cantidadtotal' => $dd->cantidad,
                    'nombre' => $dd->nombre
                ];
            }
        }

        if(sizeof($dataArray) > 0){
            $hayCantidad = true;
        }

        return view('backend.admin.herramientas.salidausuario.tablasalidaherramientausuario', compact('dataArray', 'hayCantidad'));
    }



    public function salidaHerramientaUsuario(Request $request){


        $rules = array(
            'fecha' => 'required',
        );

        // salida array
        // identrada array
        // descripcion
        // quienrecibe
        // quienentrega
        // numerosalida

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            // SE GUARDA UN HISTORIAL DE SALIDA

            $r = new HistoHerramientaSalida();
            $r->fecha = $request->fecha;
            $r->descripcion = $request->descripcion;
            $r->quien_recibe = $request->quienrecibe;
            $r->quien_entrega = $request->quienentrega;
            $r->num_salida = $request->numerosalida;
            $r->save();

            for ($i = 0; $i < count($request->salida); $i++) {

                $infoHerra = Herramientas::where('id', $request->identrada[$i])->first();

                if($infoHerra->cantidad < $request->salida[$i]){
                    return ['success' => 1, 'fila' => ($i), 'cantidad' => $infoHerra->cantidad];
                }

                $rDetalle = new HistoHerramientaSalidaDetalle();
                $rDetalle->id_herra_salida = $r->id;
                $rDetalle->id_herramienta = $request->identrada[$i];
                $rDetalle->cantidad = $request->salida[$i];
                $rDetalle->save();

                $resta = $infoHerra->cantidad - $request->salida[$i];


                // REGISTRAR SALIDA PARA VOLVER A RETORNAR DESPUES


                $pendiente = new HerramientaPendiente();

                $pendiente->id_histo_herra_salida = $r->id;
                $pendiente->id_herramienta = $request->identrada[$i];
                $pendiente->fecha = $request->fecha;
                $pendiente->cantidad = $request->salida[$i];
                $pendiente->save();

                // ACTUALIZAR CANTIDAD RETIRADA
                Herramientas::where('id', $request->identrada[$i])->update([
                    'cantidad' => $resta,
                ]);



            }

            DB::commit();
            return ['success' => 2];


        }catch(\Throwable $e){
            Log::info('ee' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }




    //*******************************************************************************


    public function indexReingresoHerramientas(){
        return view('backend.admin.herramientas.reingreso.vistareingresoherramienta');
    }


    public function tablaReingresoHerramientas(){

        $listado = HerramientaPendiente::orderBy('fecha', 'DESC')->get();

        foreach ($listado as $dato){

            //$dato->fecha = date("d-m-Y", strtotime($dato->fecha));

            $infoHerra = Herramientas::where('id', $dato->id_herramienta)->first();
            $dato->nomherra = $infoHerra->nombre;
            $dato->codigo = $infoHerra->codigo;

            $infoHistorial = HistoHerramientaSalida::where('id', $dato->id_histo_herra_salida)->first();

            $dato->descripcion = $infoHistorial->descripcion;
            $dato->quienrecibe = $infoHistorial->quien_recibe;
            $dato->quienentrega = $infoHistorial->quien_entrega;
        }

        return view('backend.admin.herramientas.reingreso.tablareingresoherramienta', compact('listado'));
    }


    public function reingresoInformacion(Request $request){

        $regla = array(
            'id' => 'required', // id de herramienta pendiente
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        $info = HerramientaPendiente::where('id', $request->id)->first();
        $datoHerra = Herramientas::where('id', $info->id_herramienta)->first();

        $histosal = HistoHerramientaSalida::where('id', $info->id_histo_herra_salida)->first();

        $fechasalida = date("d-m-Y", strtotime($histosal->fecha));


        return ['success' => 1, 'lista' => $info, 'lista2' => $datoHerra, 'fechasalio' => $fechasalida];
    }


    public function reingresoCantidadHerramienta(Request $request){

        $rules = array(
            'id' => 'required',
            'cantidad' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();


        try {

            $infoPendiente = HerramientaPendiente::where('id', $request->id)->first();

            if($request->cantidad > $infoPendiente->cantidad){

                // cantidad a reingresar es mayor a la que está afuera de bodega
                return ['success' => 1];
            }


            //*************************
            // SUMAR CANTIDAD DE INVENTARIO

            $infoInventario = Herramientas::where('id', $infoPendiente->id_herramienta)->first();

            $sumatoria = $infoInventario->cantidad + $request->cantidad;


            Herramientas::where('id', $infoInventario->id)->update([
                'cantidad' => $sumatoria
            ]);



            //**************************
            // RESTAR CANTIDAD A HERRAMIENTAS PENDIENTES, BORRAR FILA SI LLEGA A CERO

            $restado = $infoPendiente->cantidad - $request->cantidad;

            $fechaCarbon = Carbon::parse(Carbon::now());

            // guardar historial de reingreso
            $datoReingre = new HistoHerramientaReingreso();
            $datoReingre->id_histo_herra_salida = $infoPendiente->id_histo_herra_salida;
            $datoReingre->id_herramienta = $infoInventario->id;
            $datoReingre->fecha = $fechaCarbon;
            $datoReingre->cantidad = $request->cantidad;
            $datoReingre->descripcion = $request->descripcion;
            $datoReingre->save();


            if($restado <= 0){
                // eliminar registro

                HerramientaPendiente::where('id', $request->id)->delete();

            }else{
                HerramientaPendiente::where('id', $request->id)->update([
                    'cantidad' => $restado
                ]);
            }


            DB::commit();
            return ['success' => 2];

        }catch(\Throwable $e){
            Log::info('err ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



   public function descartarCantidadHerramienta(Request $request){


       $rules = array(
           'id' => 'required',
           'cantidad' => 'required',
           'descripcion' => 'required'
       );

       $validator = Validator::make($request->all(), $rules);
       if ( $validator->fails()){
           return ['success' => 0];
       }

       DB::beginTransaction();


       try {

           $infoPendiente = HerramientaPendiente::where('id', $request->id)->first();

           if($request->cantidad > $infoPendiente->cantidad){

               // cantidad a descartar es mayor a la que está afuera de bodega
               return ['success' => 1];
           }




           //**************************
           // RESTAR CANTIDAD A HERRAMIENTAS PENDIENTES, BORRAR FILA SI LLEGA A CERO

           $restado = $infoPendiente->cantidad - $request->cantidad;

           $fechaCarbon = Carbon::parse(Carbon::now());

           $infoInventario = Herramientas::where('id', $infoPendiente->id_herramienta)->first();


           // guardar historial del descartado
           $datoDescarto = new HistoHerramientaDescartada();
           $datoDescarto->id_histo_herra_salida = $infoPendiente->id_histo_herra_salida;
           $datoDescarto->id_herramienta = $infoInventario->id;
           $datoDescarto->fecha = $fechaCarbon;
           $datoDescarto->cantidad = $request->cantidad;
           $datoDescarto->descripcion = $request->descripcion;
           $datoDescarto->save();


           if($restado <= 0){
               // eliminar registro

               HerramientaPendiente::where('id', $request->id)->delete();

           }else{
               HerramientaPendiente::where('id', $request->id)->update([
                   'cantidad' => $restado
               ]);
           }


           DB::commit();
           return ['success' => 2];

       }catch(\Throwable $e){
           Log::info('err ' . $e);
           DB::rollback();
           return ['success' => 99];
       }

   }




}
