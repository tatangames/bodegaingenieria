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
        $arrayAnio = Anios::orderBy('nombre', 'ASC')->get();

        $primerId = optional($arrayAnio->first())->id;

        return view('backend.admin.historial.entradas.vistaentradabodega', compact('arrayAnio', 'primerId'));
    }

    public function tablaHistorialEntradas($id)
    {
        // OBTENER LISTADO DE ENTRADAS QUE SON EL AÑO
        $pilaIDProyectosAnio = array();
        $arrayTipo = TipoProyecto::where('id_anio', $id)->get();
        foreach ($arrayTipo as $item) {
            array_push($pilaIDProyectosAnio, $item->id);
        }

        $listado = Entradas::whereIn('id_tipoproyecto', $pilaIDProyectosAnio)
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





}
