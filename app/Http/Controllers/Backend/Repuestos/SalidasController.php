<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\Materiales;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalidasController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }


    public function indexRegistroSalida(){

        $tipoproyecto = TipoProyecto::orderBy('nombre')->get();
        return view('backend.admin.repuestos.salidas.vistasalidaregistro', compact('tipoproyecto'));
    }

    public function guardarSalida(Request $request){

        $rules = array(
            'fecha' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $r = new Salidas();
            $r->fecha = $request->fecha;
            $r->descripcion = $request->descripcion;
            $r->save();

            for ($i = 0; $i < count($request->salida); $i++) {

                // sacar id del material
                $infoEntradaDetalle = EntradasDetalle::where('id', $request->identrada[$i])->first();

                // iterar todas las entradas detalle

                $lista = EntradasDetalle::where('id', $request->identrada[$i])->get();
                $total = 0;

                foreach ($lista as $data){

                    // buscar la entrada_detalle de cada salida. obtener la suma de salidas
                    $salidaDetalle = SalidasDetalle::where('id_entrada_detalle', $data->id)
                        ->where('id_material', $infoEntradaDetalle->id_material)
                        ->sum('cantidad');

                    // total de la cantidad actual
                    $total = $data->cantidad - $salidaDetalle;
                }

                if($total < $request->salida[$i]){
                    return ['success' => 3, 'fila' => ($i), 'cantidad' => $total];
                }

                $rDetalle = new SalidasDetalle();
                $rDetalle->id_salida = $r->id;
                $rDetalle->id_material = $infoEntradaDetalle->id_material;
                $rDetalle->cantidad = $request->salida[$i];
                $rDetalle->id_entrada_detalle = $request->identrada[$i];
                $rDetalle->save();
            }

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('ee' . $e);
            DB::rollback();
            return ['success' => 2];
        }
    }


    public function buscadorMaterialPorProyecto(Request $request){

        if($request->get('query')){
            $nombre = $request->get('query');
            $idproyecto = $request->tipoproyecto;

            $datamm = Materiales::where('nombre', 'LIKE', "%{$nombre}%")
                ->orWhere('codigo', 'LIKE', "%{$nombre}%")
                ->get();

            $pilaArrayIdMaterial = array();
            foreach ($datamm as $info){
                array_push($pilaArrayIdMaterial, $info->id);
            }

            $lista1 = EntradasDetalle::whereIn('id_material', $pilaArrayIdMaterial)
                ->where('id_entrada', $idproyecto)
                ->select('id_material')
                ->groupBy('id_material')
                ->get();

            $filtrado = Materiales::whereIn('id', $lista1)->get();

            // hoy filtrar esos materiales para que sean del Proyecto que elegi
            /*$data = DB::table('materiales AS ma')
                ->join('entradas_detalle AS entradeta', 'ma.id_material', '=', 'ma.id')
                ->join('entradas AS entra', 'detalle.id_entrada', '=', 'entra.id')
                ->select('ma.id','ma.nombre', 'ma.id_medida', 'ma.codigo', 'entra.id_tipoproyecto')
                ->where('entra.id_tipoproyecto', $request->tipoproyecto)
                ->where(function ($query) use ($nombre) {
                    $query->where('ma.nombre', 'like', "%{$nombre}%")
                        ->orWhere('ma.codigo', 'like', "%{$nombre}%");
                })
                ->orderBy('ma.nombre', 'ASC')
                ->groupBy('ma.id')
                ->get();*/

            foreach ($filtrado as $dd){
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
            foreach($filtrado as $row){

                // si solo hay 1 fila, No mostrara el hr, salto de linea
                if(count($filtrado) == 1){
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


    public function bloqueCantidades($id){

        // obtener todas las entradas y obtener cada fila de cantidad

        $lista = EntradasDetalle::where('id_material', $id)
            ->where('cantidad', '>', 0)
            ->get();

        $dataArray = array();

        $hayCantidad = false;

        foreach ($lista as $dd){

            $infoEntrada = Entradas::where('id', $dd->id_entrada)->first();

            // buscar la entrada_detalle de cada salida. obtener la suma de salidas
            $salidaDetalle = SalidasDetalle::where('id_entrada_detalle', $dd->id)
                ->where('id_material', $id)
                ->sum('cantidad');

            // total de la cantidad actual
            $cantidadtotal = $dd->cantidad - $salidaDetalle;

            if($cantidadtotal > 0){
                $dataArray[] = [
                    'id' => $dd->id,
                    'fecha' => date("d-m-Y", strtotime($infoEntrada->fecha)),
                    'cantidadtotal' => $cantidadtotal,
                ];
            }
        }

        if(sizeof($dataArray) > 0){
            $hayCantidad = true;
        }

        return view('backend.admin.repuestos.salidas.modal.vistamodalbloquesalida', compact('dataArray', 'hayCantidad'));
    }




    // *****************************

    public function indexTransferencias(){

        $tipoproyecto = TipoProyecto::orderBy('nombre')
            ->where('id', '!=', 1)
            ->where('transferido', '!=', 1)
            ->get();

        return view('backend.admin.repuestos.registros.vistatransferidos', compact('tipoproyecto'));
    }


    public function geenrarSalidaTransferencia(Request $request){

        $rules = array(
            'fecha' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $r = new Salidas();
            $r->fecha = $request->fecha;
            $r->descripcion = $request->descripcion;
            $r->save();

            for ($i = 0; $i < count($request->salida); $i++) {

                // sacar id del material
                $infoEntradaDetalle = EntradasDetalle::where('id', $request->identrada[$i])->first();

                // iterar todas las entradas detalle

                $lista = EntradasDetalle::where('id', $request->identrada[$i])->get();
                $total = 0;

                foreach ($lista as $data){

                    // buscar la entrada_detalle de cada salida. obtener la suma de salidas
                    $salidaDetalle = SalidasDetalle::where('id_entrada_detalle', $data->id)
                        ->where('id_material', $infoEntradaDetalle->id_material)
                        ->sum('cantidad');

                    // total de la cantidad actual
                    $total = $data->cantidad - $salidaDetalle;
                }

                if($total < $request->salida[$i]){
                    return ['success' => 3, 'fila' => ($i), 'cantidad' => $total];
                }

                $rDetalle = new SalidasDetalle();
                $rDetalle->id_salida = $r->id;
                $rDetalle->id_material = $infoEntradaDetalle->id_material;
                $rDetalle->cantidad = $request->salida[$i];
                $rDetalle->id_entrada_detalle = $request->identrada[$i];
                $rDetalle->save();
            }

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('ee' . $e);
            DB::rollback();
            return ['success' => 2];
        }
    }


}
