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
        $infoUnidad = UnidadMedida::where('id', $infoMaterial->id_medida)->first();


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

        return ['success' => 1, 'nombreMaterial' => $infoMaterial->nombre,
            'arrayIngreso' => $listado];
    }




    public function bloqueCantidades(Request $request){

        // OBTENER CANTIDAD DEL ITEM SELECCIONADO


        if($infoEntrada = Entradas::where('id_material', $request->idmaterial)
            ->where('id_tipoproyecto', $request->idproy)
            ->first()){
            // MATERIAL ENCONTRADO

            $infoMaterial = Materiales::where('id', $request->idmaterial)->first();
            $infoMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();

            return ['success' => 1,
                'infomaterial' => $infoMaterial,
                'medida' => $infoMedida->nombre,
                'cantidad' => $infoEntrada->cantidad];
        }else{
            return ['success' => 2,];
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

            // EVITAR QUE SEA TRANSFERIDO 2 VECES
            if(TipoProyecto::where('id', $request->idproyecto)
                ->where('transferido', 1)->first()){

                return ['success' => 1];
            }

            // ESTABLECER A TRANSFERIDO
            TipoProyecto::where('id', $request->idproyecto)->update([
                'transferido' => 1
            ]);



            if ($request->hasFile('documento')) {

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->documento->getClientOriginalExtension();
                $nomDocumento = $nombre . strtolower($extension);
                $avatar = $request->file('documento');
                $archivo = Storage::disk('archivos')->put($nomDocumento, \File::get($avatar));

                if ($archivo) {


                    // GUARDAR UN HISTORIAL DE TRANSFERENCIA

                    $histoSalida = new HistorialTransferido();
                    $histoSalida->fecha = $request->fecha;
                    $histoSalida->descripcion = $request->descripcion;
                    $histoSalida->id_tipoproyecto = $request->idproyecto;
                    $histoSalida->documento = $nomDocumento;
                    $histoSalida->save();

                    // HOY GUARDAR HISTORIAL DEL DETALLE DE LAS CANTIDADES MAYOR A 0
                    $arrayMateriales = Entradas::where('id_tipoproyecto', $request->idproyecto)->get();

                    $boolHayMateriales = false;

                    foreach ($arrayMateriales as $info){

                        if($info->cantidad > 0){
                            $boolHayMateriales = true;

                            $histoDetalle = new HistorialTransferidoDetalle();
                            $histoDetalle->id_historial_transf = $histoSalida->id;
                            $histoDetalle->id_material = $info->id_material;
                            $histoDetalle->cantidad = $info->cantidad;
                            $histoDetalle->save();


                            // ACTUALIZAR CANTIDAD A INVENTARIO GENERAL O AGREGAR EL NUEVO MATERIAL SINO EXISTE

                            if($infoEn = Entradas::where('id_tipoproyecto', 1)->where('id_material', $info->id_material)
                                ->first()){
                                // EXISTE EN INVENTARIO GENERAL, ASI QUE SOLO SUMAR LA CANTIDAD
                                $suma = $infoEn->cantidad + $info->cantidad;

                                Entradas::where('id', $infoEn->id)->update([
                                    'cantidad' => $suma
                                ]);

                            }else{
                                //MATERIAL NO EXISTE EN INVENTARIO GENERAL, CREAR NUEVO MATERIAL

                                $nuevo = new Entradas();
                                $nuevo->id_material = $info->id_material;
                                $nuevo->id_tipoproyecto = 1;
                                $nuevo->cantidad = $info->cantidad;
                                $nuevo->save();
                            }

                            // ELIMINAR LA CANTIDAD DEL PROYECTO QUE TENIA
                            Entradas::where('id', $info->id)->update([
                                'cantidad' => 0
                            ]);
                        }
                    }

                    if(!$boolHayMateriales){
                        // EL PROYECTO NO TIENE MATERIALES O NO TIENE CANTIDAD NINGUNO
                        return ['success' => 2];
                    }


                    // CORRECTO

                    DB::commit();
                    return ['success' => 3];


                }
            }else{


                // GUARDAR UN HISTORIAL DE TRANSFERENCIA

                $histoSalida = new HistorialTransferido();
                $histoSalida->fecha = $request->fecha;
                $histoSalida->descripcion = $request->descripcion;
                $histoSalida->id_tipoproyecto = $request->idproyecto;
                $histoSalida->save();

                // HOY GUARDAR HISTORIAL DEL DETALLE DE LAS CANTIDADES MAYOR A 0
                $arrayMateriales = Entradas::where('id_tipoproyecto', $request->idproyecto)->get();

                $boolHayMateriales = false;

                foreach ($arrayMateriales as $info){

                    if($info->cantidad > 0){
                        $boolHayMateriales = true;

                        $histoDetalle = new HistorialTransferidoDetalle();
                        $histoDetalle->id_historial_transf = $histoSalida->id;
                        $histoDetalle->id_material = $info->id_material;
                        $histoDetalle->cantidad = $info->cantidad;
                        $histoDetalle->save();


                        // ACTUALIZAR CANTIDAD A INVENTARIO GENERAL O AGREGAR EL NUEVO MATERIAL SINO EXISTE

                        if($infoEn = Entradas::where('id_tipoproyecto', 1)->where('id_material', $info->id_material)
                            ->first()){
                            // EXISTE EN INVENTARIO GENERAL, ASI QUE SOLO SUMAR LA CANTIDAD
                            $suma = $infoEn->cantidad + $info->cantidad;

                            Entradas::where('id', $infoEn->id)->update([
                                'cantidad' => $suma
                            ]);

                        }else{
                            //MATERIAL NO EXISTE EN INVENTARIO GENERAL, CREAR NUEVO MATERIAL

                            $nuevo = new Entradas();
                            $nuevo->id_material = $info->id_material;
                            $nuevo->id_tipoproyecto = 1;
                            $nuevo->cantidad = $info->cantidad;
                            $nuevo->save();
                        }


                        // ELIMINAR LA CANTIDAD DEL PROYECTO QUE TENIA
                        Entradas::where('id', $info->id)->update([
                            'cantidad' => 0
                        ]);
                    }
                }

                if(!$boolHayMateriales){
                    // EL PROYECTO NO TIENE MATERIALES O NO TIENE CANTIDAD NINGUNO
                    return ['success' => 2];
                }


                // CORRECTO

                DB::commit();
                return ['success' => 3];


            }






        }catch(\Throwable $e){
            Log::info('ee' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


}
