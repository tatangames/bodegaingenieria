<?php

namespace App\Http\Controllers\Backend\Repuestos;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\HistorialEntradas;
use App\Models\HistorialEntradasDeta;
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

            // OBTENER CANTIDAD DE CADA MATERIAL, SUMANDO DE TODOS LOS PROYECTOS
            // EN VISTA DETALLE SE MOSTRARA DE QUE PROYECTO SON CADA UNO

            $arrayEntradas = Entradas::where('id_material', $item->id)->get();

            $sumatoria = 0;
            foreach ($arrayEntradas as $data){

                // SIEMPRE SUMARA TODOS, YA QUE PARA SACAR CANTIDAD LLEGARA HASTA 0
                $sumatoria += $data->cantidad;
            }

            $item->total = $sumatoria;
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

            // PRIMERO GUARDAR UN HISTORIAL
            $histoEntrada = new HistorialEntradas();
            $histoEntrada->fecha = $request->fecha;
            $histoEntrada->descripcion = $request->descripcion;
            $histoEntrada->id_tipoproyecto = $request->tipoproyecto;
            $histoEntrada->save();

            // HOY GUARDAR HISTORIAL DEL DETALLE
            for ($i = 0; $i < count($request->cantidad); $i++) {

                $histoDetalle = new HistorialEntradasDeta();
                $histoDetalle->id_historial = $histoEntrada->id;
                $histoDetalle->id_material = $request->datainfo[$i];
                $histoDetalle->cantidad = $request->cantidad[$i];
                $histoDetalle->save();
            }


            // GUARDAR LA CANTIDAD
            // SI EL MATERIAL EXISTE, SOLO SE SUMARA
            // SI EL MATERIAL NO EXISTE, SE CREARA

            for ($i = 0; $i < count($request->cantidad); $i++) {


                if($info = Entradas::where('id_tipoproyecto', $request->tipoproyecto)
                    ->where('id_material', $request->datainfo[$i])
                    ->first()){
                    // MATERIAL ENCONTRADO PARA EL PROYECTO SELECCIONADO
                    // SOLO SE SUMARA LA CANTIDAD

                    $suma = $info->cantidad + $request->cantidad[$i];

                    // ACTUALIZAR CANTIDAD
                    Entradas::where('id', $info->id)->update([
                        'cantidad' => $suma
                    ]);
                }else{

                    // MATERIAL NO EXISTE, ASI QUE CREAR CON SU TIPO PROYECTO
                    $nuevoIngreso = new Entradas();
                    $nuevoIngreso->id_material = $request->datainfo[$i];
                    $nuevoIngreso->id_tipoproyecto = $request->tipoproyecto;
                    $nuevoIngreso->cantidad = $request->cantidad[$i];
                    $nuevoIngreso->save();
                }
            }

            // ENTRADA COMPLETADA

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

        // SOLO HABRA 1 MATERIAL POR CADA PROYECTO
        $arrayEntradas =  Entradas::where('id_material', $id)->get();

        $pilaArrayEntrada = array();


        foreach ($arrayEntradas as $data){

            // VERIFICAR QUE LA CANTIDAD SEA MAYOR A 0 PARA PODER
            // MOSTRARLO
           if($data->cantidad > 0){
               array_push($pilaArrayEntrada, $data->id);
           }
        }

        $lista = Entradas::whereIn('id', $pilaArrayEntrada)
            ->orderBy('id_tipoproyecto', 'ASC')
            ->get();

        foreach ($lista as $info){
            // OBTENER NOMBRE DE PROYECTO

            $infoProyecto = TipoProyecto::where('id', $info->id_tipoproyecto)->first();
            $info->nombrepro = $infoProyecto->nombre;
        }

        return view('backend.admin.inventario.detalle.tabladetallematerial', compact('lista'));
    }

}
