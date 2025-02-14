<?php

namespace App\Http\Controllers\Backend\Historial;

use App\Http\Controllers\Controller;
use App\Models\Anios;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
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

class HistorialController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function indexHistorialEntradas()
    {
        $arrayProyectos = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre', 'ASC')
            ->get();

        $primerId = optional($arrayProyectos->first())->id;

        return view('backend.admin.historial.entradas.vistaentradabodega', compact('arrayProyectos', 'primerId'));
    }

    public function tablaHistorialEntradas($id)
    {
        // viene idproyecto

        $listado = Entradas::where('id_tipoproyecto', $id)
            ->orderBy('fecha', 'asc')
            ->get();

        foreach ($listado as $fila) {
            $fila->fechaFormat = date("d-m-Y", strtotime($fila->fecha));

            $infoProyecto = TipoProyecto::where('id', $fila->id_tipoproyecto)->first();
            $fila->nombreProyecto = $infoProyecto->nombre;
        }

        return view('backend.admin.historial.entradas.tablaentradabodega', compact('listado'));
    }


    public function historialEntradaBorrarLote(Request $request)
    {
        $regla = array(
            'id' => 'required', //tabla: bodega_entradas
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        // VERIFICAR QUE EXISTA LA ENTRADA
        if(Entradas::where('id', $request->id)->first()){

            DB::beginTransaction();

            try {
                // OBTENER TODOS LOS DETALLES DE ESA ENTRADA
                $arrayEntradaDetalle = EntradasDetalle::where('id_entradas', $request->id)->get();
                $pilaIdEntradaDetalle = array();

                foreach ($arrayEntradaDetalle as $fila) {
                    // GUARDAR ID DE CADA ENTRADA DETALLE
                    array_push($pilaIdEntradaDetalle, $fila->id);
                }

                // BORRAR SALIDAS DETALLE
                SalidasDetalle::whereIn('id_entrada_detalle', $pilaIdEntradaDetalle)->delete();
                // BORRAR SALIDAS
                Salidas::whereNotIn('id', SalidasDetalle::pluck('id_salida'))->delete();

                // BORRAR ENTRADAS FINALMENTE
                EntradasDetalle::where('id_entradas', $request->id)->delete();
                Entradas::where('id', $request->id)->delete();

                Log::info("completado");

                DB::commit();
                return ['success' => 1];

            } catch (\Throwable $e) {
                Log::info('ee ' . $e);
                DB::rollback();
                return ['success' => 99];
            }
        }else{
            return ['success' => 99];
        }
    }

    public function historialEntradaDetalleBorrarItem(Request $request)
    {
        $regla = array(
            'id' => 'required', //tabla: entradas_detalle
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        if($infoEntradaDeta = EntradasDetalle::where('id', $request->id)->first()){

            DB::beginTransaction();

            try {
                // OBTENER TODOS LOS DETALLES DE ESA ENTRADA

                // BORRAR SALIDAS DETALLE
                SalidasDetalle::where('id_entrada_detalle', $infoEntradaDeta->id)->delete();
                // BORRAR SALIDAS
                Salidas::whereNotIn('id', SalidasDetalle::pluck('id_salida'))->delete();

                // BORRAR ENTRADAS FINALMENTE
                EntradasDetalle::where('id', $infoEntradaDeta->id)->delete();

                // SI YA NO HAY ENTRADAS SE DEBERA BORRAR
                Entradas::whereNotIn('id', EntradasDetalle::pluck('id_entradas'))->delete();

                DB::commit();
                return ['success' => 1];

            } catch (\Throwable $e) {
                Log::info('ee ' . $e);
                DB::rollback();
                return ['success' => 99];
            }
        }else{
            return ['success' => 99];
        }
    }


    public function indexHistorialEntradasDetalle($id)
    {
        $info = Entradas::where('id', $id)->first();

        return view('backend.admin.historial.entradas.detalle.vistaentradadetallebodega', compact('id', 'info'));
    }

    public function tablaHistorialEntradasDetalle($id){

        $listado = DB::table('entradas_detalle AS bo')
            ->join('materiales AS bm', 'bo.id_material', '=', 'bm.id')
            ->join('unidadmedida AS uni', 'bm.id_medida', '=', 'uni.id')
            ->select('bo.id', 'bo.cantidad', 'bm.nombre', 'uni.nombre AS nombreUnidad')
            ->where('bo.id_entradas', $id)
            ->get();

        return view('backend.admin.historial.entradas.detalle.tablaentradadetallebodega', compact('listado'));
    }

    public function indexNuevoIngresoEntradaDetalle($id)
    {
        // id: es de entradas
        $info = Entradas::where('id', $id)->first();

        return view('backend.admin.historial.entradas.detalle.vistaingresoextra', compact('id', 'info'));
    }


    public function registrarProductosExtras(Request $request)
    {
        $regla = array(
            'identrada' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            // Obtiene los datos enviados desde el formulario como una cadena JSON y luego decódificala
            $datosContenedor = json_decode($request->contenedorArray, true); // El segundo argumento convierte el resultado en un arreglo

            foreach ($datosContenedor as $filaArray) {

                $infoProducto = Materiales::where('id', $filaArray['infoIdProducto'])->first();

                $detalle = new EntradasDetalle();
                $detalle->id_entradas = $request->identrada;
                $detalle->id_material = $filaArray['infoIdProducto'];
                $detalle->cantidad = $filaArray['infoCantidad'];
                $detalle->nombre_copia = $infoProducto->nombre;
                $detalle->cantidad_entregada = 0;
                $detalle->save();
            }

            DB::commit();
            return ['success' => 1];

        } catch (\Throwable $e) {
            Log::info('error ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }












    //**************** HISTORIAL DE SALIDAS ************************


    public function indexHistorialSalidas()
    {
        $arrayProyectos = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre', 'ASC')
            ->get();

        $primerId = optional($arrayProyectos->first())->id;

        return view('backend.admin.historial.salidas.vistasalidabodega', compact('arrayProyectos', 'primerId'));
    }

    public function tablaHistorialSalidas($id)
    {
        // viene id proyecto

        $listado = Salidas::where('id_tipoproyecto', $id)
            ->orderBy('fecha', 'desc')
            ->get();

        foreach ($listado as $fila) {
            $fila->fechaFormat = date("d-m-Y", strtotime($fila->fecha));

            $infoRecibe = QuienRecibe::where('id', $fila->id_recibe)->first();
            $fila->nombreRecibe = $infoRecibe->nombre;
        }

        return view('backend.admin.historial.salidas.tablasalidabodega', compact('listado'));
    }



    public function indexHistorialSalidasDetalle($id)
    {
        return view('backend.admin.historial.salidas.detalle.vistasalidadetallebodega', compact('id'));
    }

    public function tablaHistorialSalidasDetalle($id){

        $listado = SalidasDetalle::where('id_salida', $id)->get();

        foreach ($listado as $fila) {

            $infoEntraDeta = EntradasDetalle::where('id', $fila->id_entrada_detalle)->first();
            $infoMaterial = Materiales::where('id', $infoEntraDeta->id_material)->first();
            $fila->nombreMaterial = $infoMaterial->nombre;

            $infoMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();
            $fila->nombreUnidad = $infoMedida->nombre;
        }

        return view('backend.admin.historial.salidas.detalle.tablasalidadetallebodega', compact('listado'));
    }


    public function salidaDetalleBorrarItem(Request $request)
    {
        $regla = array(
            'id' => 'required', //tabla: salidas_detalle
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        if($infoSalidaDeta = SalidasDetalle::where('id', $request->id)->first()){

            DB::beginTransaction();

            try {

                $infoBodegaEntraDeta = EntradasDetalle::where('id', $infoSalidaDeta->id_entrada_detalle)->first();

                $resta = $infoBodegaEntraDeta->cantidad_entregada - $infoSalidaDeta->cantidad_salida;

                // RESTAR CANTIDAD ENTREGADA
                EntradasDetalle::where('id', $infoBodegaEntraDeta->id)->update([
                    'cantidad_entregada' => $resta
                ]);

                // BORRAR SALIDAS DETALLE
                SalidasDetalle::where('id', $request->id)->delete();
                // BORRAR SALIDAS (ESTO VERIFICA QUE SINO TIENE DETALLE, ELIMINA EL bodega_salidas)
                Salidas::whereNotIn('id', SalidasDetalle::pluck('id_salida'))->delete();

                DB::commit();
                return ['success' => 1];

            } catch (\Throwable $e) {
                Log::info('ee ' . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }











}
