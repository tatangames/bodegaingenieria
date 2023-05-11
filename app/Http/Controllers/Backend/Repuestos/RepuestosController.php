<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\Materiales;
use App\Models\SalidasDetalle;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RepuestosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $lUnidad = UnidadMedida::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.inventario.vistainventario', compact('lUnidad'));
    }

    public function tablaMateriales(){

        $lista = Materiales::orderBy('nombre', 'ASC')->get();

        foreach ($lista as $item) {
            $medida = '';
            if($dataUnidad = UnidadMedida::where('id', $item->id_medida)->first()){
                $medida = $dataUnidad->nombre;
            }
            $item->medida = $medida;

            // obtener todas las entradas detalle de este material

            $entradaDetalle = EntradasDetalle::where('id_material', $item->id)->get();

            $valor = 0;
            foreach ($entradaDetalle as $data){

                // buscar la entrada_detalle de cada salida. obtener la suma de salidas
                $salidaDetalle = SalidasDetalle::where('id_entrada_detalle', $data->id)
                    ->where('id_material', $item->id)
                    ->sum('cantidad');

                // total: es la cantidad actual
                $total = $data->cantidad - $salidaDetalle;

                // valor: es la suma de cantidad actual
                $valor = $valor + $total;
            }

            $item->total = $valor;
        }

        return view('backend.admin.inventario.tablainventario', compact('lista'));
    }

    public function nuevoMaterial(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Materiales::where('nombre', $request->nombre)
            ->where('id_medida', $request->unidad)
            ->where('codigo', $request->codigo)
            ->first()){
            return ['success' => 3];
        }

        $dato = new Materiales();
        $dato->id_medida = $request->unidad;
        $dato->nombre = $request->nombre;
        $dato->codigo = $request->codigo;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function informacionMaterial(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Materiales::where('id', $request->id)->first()){

            $arrayUnidad = UnidadMedida::orderBy('nombre', 'ASC')->get();

            return ['success' => 1, 'material' => $lista, 'unidad' => $arrayUnidad];
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

        if(Materiales::where('id', '!=', $request->id)
            ->where('nombre', $request->nombre)
            ->where('id_medida', $request->unidad)
            ->where('codigo', $request->codigo)
            ->first()){
            return ['success' => 3];
        }

        Materiales::where('id', $request->id)->update([
            'id_medida' => $request->unidad,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo
        ]);

        return ['success' => 1];
    }



    //*******************************************************************

    public function indexRegistroEntrada(){

        $tipoproyecto = TipoProyecto::orderBy('nombre')->get();

        return view('backend.admin.repuestos.registros.vistaentradaregistro', compact('tipoproyecto'));
    }


    public function buscadorMaterial(Request $request){

        if($request->get('query')){
            $query = $request->get('query');
            $data = Materiales::where('nombre', 'LIKE', "%{$query}%")
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

            $r = new Entradas();
            $r->fecha = $request->fecha;
            $r->descripcion = $request->descripcion;
            $r->id_tipoproyecto = $request->tipoproyecto;
            $r->save();

            for ($i = 0; $i < count($request->cantidad); $i++) {

                $rDetalle = new EntradasDetalle();
                $rDetalle->id_entrada = $r->id;
                $rDetalle->id_material = $request->datainfo[$i];
                $rDetalle->cantidad = $request->cantidad[$i];
                $rDetalle->save();
            }

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }




    //*******************************************

    public function vistaDetalleMaterial($id){

        $infomaterial = Materiales::where('id', $id)->first();
        $medida = '';
        if($infoMedida = UnidadMedida::where('id', $infomaterial->id_medida)->first()){
            $medida = $infoMedida->nombre;
        }

        return view('backend.admin.inventario.detalle.vistadetalle', compact('id', 'infomaterial', 'medida'));
    }


    public function tablaDetalleMaterial($id){

        $lista =  EntradasDetalle::where('id_material', $id)->get();

        foreach ($lista as $data){

            // buscar la entrada_detalle de cada salida. obtener la suma de salidas
            $salidaDetalle = SalidasDetalle::where('id_entrada_detalle', $data->id)
                ->where('id_material', $id)
                ->sum('cantidad');

            $infoEntrada = Entradas::where('id', $data->id_entrada)->first();
            $data->fecha = date("d-m-Y", strtotime($infoEntrada->fecha));

            $infoProyecto = TipoProyecto::where('id', $infoEntrada->id_tipoproyecto)->first();
            $data->nomproyecto = $infoProyecto->nombre;

            // total de la cantidad actual
            $total = $data->cantidad - $salidaDetalle;
            $data->total = $total;
        }

        return view('backend.admin.inventario.detalle.vistatabladetallematerial', compact('lista'));
    }

}
