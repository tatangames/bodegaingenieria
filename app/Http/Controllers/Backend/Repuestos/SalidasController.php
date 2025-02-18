<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
use App\Models\Anios;
use App\Models\CierreProyecto;
use App\Models\CierreProyectoDetalle;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\HistorialSalidas;
use App\Models\HistorialSalidasDeta;
use App\Models\HistorialTransferido;
use App\Models\HistorialTransferidoDetalle;
use App\Models\Materiales;
use App\Models\QuienRecibe;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalidasController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexRegistroSalida(){

        $arrayProyectos = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre')->get();

        $arrayRecibe = QuienRecibe::where('id', '!=', 1)
            ->orderBy('nombre')->get();

        return view('backend.admin.registros.salidas.vistasalidaregistro', compact('arrayProyectos', 'arrayRecibe'));
    }

    public function buscadorMaterialPorProyecto(Request $request){

        if($request->get('query')){

            $query = $request->get('query');
            $idproyecto = $request->tipoproyecto;
            $pilaArrayIdEntrada = array();
            $pilaArrayIdMaterial = array();

            $arrayMateriales = Materiales::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo', 'LIKE', "%{$query}%")
                ->get();
            foreach ($arrayMateriales as $fila) {
                array_push($pilaArrayIdMaterial, $fila->id);
            }




            // TODAS LAS ENTRADAS DEL PROYECTO
            $arrayEntradas = Entradas::where('id_tipoproyecto', $idproyecto)->get();
            foreach ($arrayEntradas as $fila) {
                array_push($pilaArrayIdEntrada, $fila->id);
            }



            // SOLO MATERIAL DISPONIBLE
            $listado = EntradasDetalle::whereIn('id_entradas', $pilaArrayIdEntrada)
                ->whereIn('id_material', $pilaArrayIdMaterial)
                ->whereColumn('cantidad_entregada', '<', 'cantidad')
                ->orderBy('id') // Ordenar para obtener el primer registro de cada material
                ->get()
                ->unique('id_material') // Filtrar en PHP si la consulta no lo resuelve
                ->values();

            Log::info('ee');
            Log::info($listado);



            $output = '<ul class="dropdown-menu" style="display:block; position:relative; overflow: auto; max-height: 300px; width: 550px">';
            $tiene = true;
            foreach ($listado as $row) {

                $infoMaterial = Materiales::where('id', $row->id_material)->first();
                $infoMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();

                $nombreCompleto = $infoMaterial->nombre . " (" . $infoMedida->nombre . ")";


                // si solo hay 1 fila, No mostrara el hr, salto de linea
                if (count($listado) == 1) {
                    if (!empty($row)) {
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="' . $row->id . '">' .$nombreCompleto . '</li>
                ';
                    }
                } else {
                    if (!empty($row)) {
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="' . $row->id . '">' . $nombreCompleto . '</li>
                   <hr>
                ';
                    }
                }
            }
            $output .= '</ul>';
            if ($tiene) {
                $output = '';
            }
            echo $output;
        }
    }

    public function infoBodegaMaterialDetalleFila(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'idproyecto' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $infoEntradaDeta = EntradasDetalle::where('id', $request->id)->first();
        $infoMaterial = Materiales::where('id', $infoEntradaDeta->id_material)->first();


        // BUSCAR SOLO DE LAS 'ENTRADAS' DEL PROYECTO
        $pilaArrayIdEntradas = array();
        $arrayEntradas = Entradas::where('id_tipoproyecto', $request->idproyecto)->get();
         foreach ($arrayEntradas as $fila) {
             array_push($pilaArrayIdEntradas, $fila->id);
         }

        $listado = EntradasDetalle::whereIn('id_entradas', $pilaArrayIdEntradas)
            ->where('id_material', $infoEntradaDeta->id_material)
            ->whereColumn('cantidad_entregada', '<', 'cantidad')
            ->get();

        foreach ($listado as $fila){
            $infoPadre = Entradas::where('id', $fila->id_entradas)->first();

            // cantidad actual que hay
            $resta = $fila->cantidad - $fila->cantidad_entregada;
            $fila->cantidadActual = $resta;

            $fecha = date("d-m-Y", strtotime($infoPadre->fecha));
            $fila->fechaIngreso = $fecha;
        }

        $disponible = 0;
        if ($listado->isEmpty()) {
            $disponible = 1;
        }

        return ['success' => 1, 'nombreMaterial' => $infoMaterial->nombre,
            'arrayIngreso' => $listado, 'disponible' => $disponible];
    }



    public function guardarSalidaMateriales(Request  $request)
    {
        $regla = array(
            'fecha' => 'required',
            'idproyecto' => 'required',
            'idrecibe' => 'required',
        );

        // numsalida, descripcion, (infoIdEntradaDeta, infoCantidad)

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $infoProyecto = TipoProyecto::where('id', $request->idproyecto)->first();
            if($infoProyecto->cerrado == 1){
                return ['success' => 1];
            }

            $datosContenedor = json_decode($request->contenedorArray, true);

            // EVITAR QUE VENGA VACIO
            if($datosContenedor == null){
                return ['success' => 2];
            }


            // CONTROLAR QUE TODOS LOS MATERIALES DE SALIDA SEAN DEL MISMO MATERIAL
            foreach ($datosContenedor as $filaArray) {

                $info = DB::table('entradas_detalle AS ed')
                    ->join('entradas AS e', 'ed.id_entradas', '=', 'e.id')
                    ->select('e.id_tipoproyecto')
                    ->where('ed.id', $filaArray['infoIdEntradaDeta'])
                    ->first();

                if($info->id_tipoproyecto != $request->idproyecto){
                    return ['success' => 3];
                }
            }


            $usuario = auth()->user();

            $reg = new Salidas();
            $reg->id_usuario = $usuario->id;
            $reg->id_tipoproyecto = $request->idproyecto;
            $reg->id_recibe = $request->idrecibe;
            $reg->fecha = $request->fecha;
            $reg->descripcion = $request->descripcion;
            $reg->orden_salida = $request->numsalida;
            $reg->cierre_proyecto = 0;
            $reg->save();

            // infoIdEntradaDetalle, filaCantidadSalida
            $filaContada = 0;
            foreach ($datosContenedor as $filaArray) {
                $filaContada++;

                // verificar cantidad que hay en la entrada_detalla
                $infoFilaEntradaDetalle = EntradasDetalle::where('id', $filaArray['infoIdEntradaDeta'])->first();

                // VERIFICACION:NO SUPERAR LA CANTIDAD_ENTREGADA TOTAL DE ESE MATERIAL-LOTE SEA MAYOR A LA CANTIDAD INGRESADA POR EL BODEGUERO DE ESE MATERIAL-LOTE
                $suma1 = $infoFilaEntradaDetalle->cantidad_entregada + $filaArray['infoCantidad'];
                if($suma1 > $infoFilaEntradaDetalle->cantidad){
                    return ['success' => 2, 'fila' => $filaContada];
                }

                // Pasa validaciones

                // GUARDAR SALIDA DETALLE
                $detalle = new SalidasDetalle();
                $detalle->id_salida = $reg->id;
                $detalle->id_entrada_detalle = $infoFilaEntradaDetalle->id;
                $detalle->cantidad_salida = $filaArray['infoCantidad'];
                $detalle->save();

                // ACTUALIZAR CANTIDADES DE SALIDA
                EntradasDetalle::where('id', $filaArray['infoIdEntradaDeta'])->update([
                    'cantidad_entregada' => ($filaArray['infoCantidad'] + $infoFilaEntradaDetalle->cantidad_entregada)
                ]);
            }

            DB::commit();
            return ['success' => 10];
        }catch(\Throwable $e){
            Log::info('error ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



    public function buscarInventarioVista(Request $request)
    {
        $regla = array(
            'idproyecto' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $listado = DB::table('entradas_detalle AS ed')
            ->join('entradas AS e', 'ed.id_entradas', '=', 'e.id')
            ->select('ed.id_material', 'ed.cantidad', 'ed.cantidad_entregada')
            ->where('e.id_tipoproyecto', $request->idproyecto)
            ->whereColumn('ed.cantidad_entregada', '<', 'ed.cantidad')
            ->get();

        foreach ($listado as $fila) {
            $infoMaterial = Materiales::where('id', $fila->id_material)->first();
            $fila->nombre = $infoMaterial->nombre;

            $fila->cantidadActual = $fila->cantidad - $fila->cantidad_entregada;
        }

        return ['success' => 1, 'listado' => $listado];
    }








    // **************** TRANSFERENCIA DE PROYECTO    ********************

    public function indexTransferencias(){

        return view('backend.admin.registros.cierres.vistacierreproyecto');
    }


    public function tablaTransferencias()
    {
        $listado = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre', 'asc')
            ->get();

        foreach ($listado as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreAnio = $infoAnio->nombre;
        }

        return view('backend.admin.registros.cierres.tablacierreproyecto', compact('listado'));
    }

    public function indexInventarioProyecto($id)
    {

        return view('backend.admin.registros.cierres.materiales.vistadetallematerial', compact('id'));
    }

    public function tablaInventarioProyecto($id)
    {
        // viene id proyecto

        $listado = DB::table('entradas_detalle AS ed')
            ->join('entradas AS e', 'ed.id_entradas', '=', 'e.id')
            ->select('ed.id_material', 'ed.cantidad', 'ed.cantidad_entregada')
            ->where('e.id_tipoproyecto', $id)
            ->whereColumn('ed.cantidad_entregada', '<', 'ed.cantidad')
            ->get();

        foreach ($listado as $fila) {
            $infoMaterial = Materiales::where('id', $fila->id_material)->first();
            $fila->nombre = $infoMaterial->nombre;

            $fila->cantidadActual = $fila->cantidad - $fila->cantidad_entregada;
        }

        return view('backend.admin.registros.cierres.materiales.tabladetallematerial', compact('listado'));
    }


    public function infoProyectosRecibiran(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $listado = TipoProyecto::where('id', '!=', $request->id)
                  ->where('cerrado', 0)->get();


        $infoPro = TipoProyecto::where('id', $request->id)->first();
        $nombreProyecto = $infoPro->nombre;

        return ['success' => 1, 'listado' => $listado, 'proyecto' => $nombreProyecto];
    }


    public function generarSalidaTransferencia(Request $request)
    {
        $regla = array(
            'identrega' => 'required',
            'idrecibe' => 'required',
            'fecha' => 'required',
        );

        // documento, descripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            $infoProEntrega = TipoProyecto::where('id', $request->identrega)->first();

            // Evitar que proyecto no estaba cerrado
            if($infoProEntrega->cerrado == 1){
                return ['success' => 1];
            }

            // ACTUALIZAR EL CIERRE
            TipoProyecto::where('id', $request->identrega)->update([
                'cerrado' => 1
            ]);


            $pilaArrayIdEntrada = array();

            // TODAS LAS ENTRADAS DEL PROYECTO
            $arrayEntradas = Entradas::where('id_tipoproyecto', $infoProEntrega->id)->get();
            foreach ($arrayEntradas as $fila) {
                array_push($pilaArrayIdEntrada, $fila->id);
            }

            // SOLO MATERIAL DISPONIBLE
            $arrayEntradaDetalle = EntradasDetalle::whereIn('id_entradas', $pilaArrayIdEntrada)
                ->whereColumn('cantidad_entregada', '<', 'cantidad')
                ->get();

            $usuario = auth()->user();
            $nomDocumento = null;

            if ($request->hasFile('documento')) {

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->documento->getClientOriginalExtension();
                $nomDocumento = $nombre . strtolower($extension);
                $avatar = $request->file('documento');
                Storage::disk('archivos')->put($nomDocumento, \File::get($avatar));
            }


            if ($arrayEntradaDetalle->isNotEmpty()) {

                // LAS ENTRADAS YA NO PODRAN ELIMINARSE DEL PROYECTO PORQUE HA FINALIZADO
                $regEntradas = new Entradas();
                $regEntradas->id_usuario = $usuario->id;
                $regEntradas->id_tipoproyecto = $request->idrecibe;
                $regEntradas->fecha = $request->fecha;
                $regEntradas->descripcion = $request->descripcion;
                $regEntradas->cierre_proyecto = 1;
                $regEntradas->save();

                // GENERAR LA SALIDA DEL PROYECTO QUE ENTREGA
                $regSalida = new Salidas();
                $regSalida->id_usuario = $usuario->id;
                $regSalida->id_tipoproyecto = $request->identrega;
                $regSalida->id_recibe = 1; // cierre de proyecto
                $regSalida->fecha = $request->fecha;
                $regSalida->descripcion = $request->descripcion;
                $regSalida->orden_salida = null;
                $regSalida->cierre_proyecto = 1;
                $regSalida->save();


                // GUARDAR UN HISTORIAL PARA REPORTES DE QUE SE PASO
                $registroCi = new CierreProyecto();
                $registroCi->id_usuario = $usuario->id;
                $registroCi->fecha = $request->fecha;
                $registroCi->descripcion = $request->descripcion;
                $registroCi->documento = $nomDocumento;
                $registroCi->id_entrega_proyecto = $request->identrega;  // ID DE PROYECTO QUE FINALIZO
                $registroCi->id_recibe_proyecto = $request->idrecibe;  // ID DE PROYECTO QUE RECIBE
                $registroCi->id_entrada = $regEntradas->id; // ENTREDA PARA QUE RECIBE
                $registroCi->id_salida = $regSalida->id; // SALIDA PARA QUIEN ENTREGA
                $registroCi->save();




                // RECORRER CADA MATERIAL QUE SE VA A TRASPASAR
                foreach ($arrayEntradaDetalle as $fila) {

                    $actualCantidad = $fila->cantidad - $fila->cantidad_entregada;

                    // ANIVELAR
                    EntradasDetalle::where('id', $fila->id)->update([
                        'cantidad_entregada' => $fila->cantidad
                    ]);

                    $detalle = new SalidasDetalle();
                    $detalle->id_salida = $regSalida->id;
                    $detalle->id_entrada_detalle = $fila->id;
                    $detalle->cantidad_salida = $actualCantidad;
                    $detalle->save();

                    $detalle = new EntradasDetalle();
                    $detalle->id_entradas = $regEntradas->id;
                    $detalle->id_material = $fila->id_material;
                    $detalle->cantidad = $actualCantidad;
                    $detalle->nombre_copia = $fila->nombre_copia;
                    $detalle->cantidad_entregada = 0;
                    $detalle->save();
                }
            }else{
                // ACTUALIZAR FECHA DE CIERRE
                TipoProyecto::where('id', $request->identrega)->update([
                    'fecha_cierre' => $request->fecha
                ]);
            }


            DB::commit();
            return ['success' => 10];

        } catch (\Throwable $e) {
            Log::info('error ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



}
