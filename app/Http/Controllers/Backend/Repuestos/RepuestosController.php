<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\Herramientas;
use App\Models\HistoHerramientaDescartada;
use App\Models\HistorialEntradas;
use App\Models\HistorialEntradasDeta;
use App\Models\Materiales;
use App\Models\SalidasDetalle;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RepuestosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }



    //************** REGISTRO DE INGRESO DE MATERIALES  *****************************

    public function indexRegistroEntrada(){
        $arrayProyectos = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre')
            ->get();

        foreach ($arrayProyectos as $item) {
            if($item->codigo != null){
                $item->nombreCodigo = "(" . $item->codigo . ") " . $item->nombre;
            }else{
                $item->nombreCodigo = $item->nombre;
            }
        }

        return view('backend.admin.registros.nuevo.vistaentradaregistro', compact('arrayProyectos'));
    }


    public function buscadorMaterialGlobal(Request $request){

        if($request->get('query')){
            $query = $request->get('query');
            $arrayMateriales = Materiales::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo', 'LIKE', "%{$query}%")
                ->get();


            $output = '<ul class="dropdown-menu" style="display:block; position:relative; overflow: auto; ">';
            $tiene = true;
            foreach($arrayMateriales as $row){

                $medida = "";
                $code = "";

                if($info = UnidadMedida::where('id', $row->id_medida)->first()){
                    $medida = "- " . $info->nombre;
                }

                if($row->codigo != null){
                    $code = "- " . $row->codigo;
                }

                // si solo hay 1 fila, No mostrara el hr, salto de linea
                if(count($arrayMateriales) == 1){
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px; color: black">'.$row->nombre . '  ' .$medida . ' ' .$code .'</a></li>
                ';
                    }
                }

                else{
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px; color: black">'.$row->nombre . ' ' .$row->medida . ' ' .$row->code .'</a></li>
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

    // GUARDAR ENTRADAS
    public function guardarEntrada(Request $request){

        $rules = array(
            'fecha' => 'required',
            'tipoproyecto' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {
            $datosContenedor = json_decode($request->contenedorArray, true);

            $usuario = auth()->user();

            $registro = new Entradas();
            $registro->id_usuario = $usuario->id;
            $registro->id_tipoproyecto = $request->tipoproyecto;
            $registro->fecha = $request->fecha;
            $registro->descripcion = $request->observacion;
            $registro->save();

            // idMaterial    infoCantidad

            // SUMAR CANTIDAD
            foreach ($datosContenedor as $filaArray) {

                $infoProducto = Materiales::where('id', $filaArray['idMaterial'])->first();

                $detalle = new EntradasDetalle();
                $detalle->id_entradas = $registro->id;
                $detalle->id_material = $filaArray['idMaterial'];
                $detalle->cantidad = $filaArray['infoCantidad'];
                $detalle->nombre_copia = $infoProducto->nombre;
                $detalle->cantidad_entregada = 0;
                $detalle->save();
            }

            // ENTRADA COMPLETADA

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info("error: " . $e);
            DB::rollback();
            return ['success' => 2];
        }
    }




    //*******************************************

    public function vistaDetalleMaterial($id){
        return view('backend.admin.materiales.detalle.vistadetallematerial', compact('id'));
    }


    public function tablaDetalleMaterial($idmaterial){

        $listado = EntradasDetalle::where('id_material', $idmaterial)
            ->whereColumn('cantidad_entregada', '<', 'cantidad')
            ->get();

        foreach ($listado as $fila) {
            $infoEntrada = Entradas::where('id', $fila->id_entradas)->first();
            $fila->fechaFormat = date("d-m-Y", strtotime($infoEntrada->fecha));

            $fila->cantidadDisponible = ($fila->cantidad - $fila->cantidad_entregada);

            // NOMBRE DE PROYECTO
            $infoProyecto = TipoProyecto::where('id', $infoEntrada->id_tipoproyecto)->first();
            $fila->nombreProyecto = $infoProyecto->nombre;
        }

        return view('backend.admin.materiales.detalle.tabladetallematerial', compact('listado'));
    }

}
