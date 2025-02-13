<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
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

        $arrayProyectos = TipoProyecto::orderBy('nombre')->get();
        $arrayRecibe = QuienRecibe::orderBy('nombre')->get();
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
                ->get();

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

            $datosContenedor = json_decode($request->contenedorArray, true);

            // EVITAR QUE VENGA VACIO
            if($datosContenedor == null){
                return ['success' => 1];
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












    // *****************************

    public function indexTransferencias(){

        // LISTADO DE PROYECTOS (MENOS EL ID 1 YA QUE SERA EL INVENTARIO GENERAL)
        // Y QUE NO HAYAN SIDO TRANSFERIDOS

        $tipoproyecto = TipoProyecto::orderBy('nombre')
            ->where('id', '!=', 1)
            ->where('transferido', '!=', 1)
            ->get();

        return view('backend.admin.repuestos.registros.vistatransferidos', compact('tipoproyecto'));
    }



}
