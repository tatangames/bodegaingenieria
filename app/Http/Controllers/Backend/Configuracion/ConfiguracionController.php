<?php

namespace App\Http\Controllers\Backend\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Anios;
use App\Models\Encargados;
use App\Models\EntradasDetalle;
use App\Models\Materiales;
use App\Models\ProyectoEncargado;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


    // **************** AÑO *********************************
    public function indexAnio(){
        return view('backend.admin.configuracion.anio.vistaanio');
    }

    public function tablaAnio(){

        $listado = Anios::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.anio.tablaanio', compact('listado'));
    }


    public function nuevoAnio(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new Anios();
        $registro->nombre = $request->nombre;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionAnio(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Anios::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function editarAnio(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Anios::where('id', $request->id)->first()){

            Anios::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




    //**************************** UNIDAD DE MEDIDA ****************************


    public function indexUnidadMedida(){
        return view('backend.admin.configuracion.unidadmedida.vistaunidadmedida');
    }

    public function tablaUnidadMedida(){
        $lista = UnidadMedida::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.unidadmedida.tablaunidadmedida', compact('lista'));
    }

    public function nuevaUnidadMedida(Request $request){
        $regla = array(
            'medida' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new UnidadMedida();
        $dato->nombre = $request->medida;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function informacionUnidadMedida(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = UnidadMedida::where('id', $request->id)->first()){

            return ['success' => 1, 'medida' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    public function editarUnidadMedida(Request $request){

        $regla = array(
            'id' => 'required',
            'medida' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(UnidadMedida::where('id', $request->id)->first()){

            UnidadMedida::where('id', $request->id)->update([
                'nombre' => $request->medida
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




    //**************** PERSONA QUE RECIBE LOS MATERIALES ********************************

    public function indexVistaRegistroQuienRecibe(){

        return view('backend.admin.configuracion.quienrecibe.vistaquienrecibe');
    }


    public function tablaRegistroQuienRecibe(){

        $lista = QuienRecibe::where('id', '!=', 1)
            ->orderBy('nombre', 'ASC')
            ->get();


        return view('backend.admin.configuracion.quienrecibe.tablaquienrecibe', compact('lista'));
    }


    public function registrarNombreQuienRecibe(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new QuienRecibe();
        $dato->nombre = $request->nombre;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionQuienRecibe(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = QuienRecibe::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarNombreQuienRecibe(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(QuienRecibe::where('id', $request->id)->first()){

            QuienRecibe::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }





    //*****************  REGISTRO DE MATERIALES   **********************************


    public function indexMateriales(){
        $arrayUnidades = UnidadMedida::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.materiales.vistamateriales', compact('arrayUnidades'));
    }

    public function tablaMateriales(){

        $lista = Materiales::orderBy('nombre', 'ASC')->get();

        foreach ($lista as $fila) {

            $infoUnidad = UnidadMedida::where('id', $fila->id_medida)->first();
            $fila->medida = $infoUnidad->nombre;

            // CANTIDAD GLOBAL QUE TENGO DE ESE PRODUCTO
            $totalCantidadMate = EntradasDetalle::where('id_material', $fila->id)->sum('cantidad');
            $totalCantidadEntregada = EntradasDetalle::where('id_material', $fila->id)->sum('cantidad_entregada');

            $fila->cantidadGlobal = ($totalCantidadMate - $totalCantidadEntregada);
        }

        return view('backend.admin.materiales.tablamateriales', compact('lista'));
    }

    public function nuevoMaterial(Request $request){

        $regla = array(
            'nombre' => 'required',
            'unidad' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new Materiales();
        $registro->id_medida = $request->unidad;
        $registro->nombre = $request->nombre;
        $registro->codigo = $request->codigo;

        if($registro->save()){
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
            'unidad' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        Materiales::where('id', $request->id)->update([
            'id_medida' => $request->unidad,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo
        ]);

        return ['success' => 1];
    }


    //******************* LISTA DE PROYECTOS  *******************************

    public function indexProyectos(){

        $arrayAnio = Anios::orderBy('nombre', 'ASC')->get();

        $primerId = optional($arrayAnio->first())->id;

        return view('backend.admin.configuracion.proyectos.vistaproyectos', compact('arrayAnio', 'primerId'));
    }

    public function tablaProyectos($idanio){

        $listado = TipoProyecto::where('id_anio', $idanio)
            ->orderBy('nombre', 'ASC')
            ->get();

        foreach ($listado as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreAnio = $infoAnio->nombre;
        }

        return view('backend.admin.configuracion.proyectos.tablalistaproyectos', compact('listado'));
    }

    public function nuevoProyecto(Request $request){
        $regla = array(
            'nombre' => 'required',
            'anio' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new TipoProyecto();
        $registro->id_anio = $request->anio;
        $registro->codigo = $request->codigo;
        $registro->nombre = $request->nombre;
        $registro->cerrado = 0;
        $registro->fecha_cierre = null;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function informacionProyecto(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = TipoProyecto::where('id', $request->id)->first()){

            $arrayAnio = Anios::orderBy('nombre', 'ASC')->get();

            return ['success' => 1, 'info' => $lista, 'arrayAnio' => $arrayAnio];
        }else{
            return ['success' => 2];
        }
    }

    public function editarProyecto(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required',
            'anio' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($info = TipoProyecto::where('id', $request->id)->first()){

            if($info->cerrado == 1){
                return ['success' => 1];
            }

            TipoProyecto::where('id', $request->id)->update([
                'id_anio' => $request->anio,
                'nombre' => $request->nombre,
                'codigo' => $request->codigo
            ]);

            return ['success' => 2];
        }else{
            return ['success' => 2];
        }
    }


    // ***** ENCARGOS DE CADA PROYECTO ******

    public function indexEncargadoProyecto($id)
    {

        $listaEncargados = Encargados::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.proyectos.encargados.vistaproyectoencargados', compact('id', 'listaEncargados'));
    }


    public function tablaEncargadoProyecto($idproyecto)
    {
        $arrayEncargados = ProyectoEncargado::where('id_tipoproyecto', $idproyecto)->get();

        foreach ($arrayEncargados as $item) {
            $infoEncargado = Encargados::where('id', $item->id_encargado)->first();
            $item->nombreEncargado = $infoEncargado->nombre;
        }

        return view('backend.admin.configuracion.proyectos.encargados.tablaproyectoencargados', compact('arrayEncargados'));
    }


    public function nuevoEncargadoProyecto(Request $request){

        $regla = array(
            'id' => 'required', // idproyecto
            'idencargado' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new ProyectoEncargado();
        $registro->id_tipoproyecto = $request->id;
        $registro->id_encargado = $request->idencargado;
        $registro->save();

        return ['success' => 1];
    }


    public function borrarEncargadoProyecto(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        ProyectoEncargado::where('id', $request->id)->delete();

        return ['success' => 1];
    }










    //**************** ENCARGADOS DE PROYECTOS ********************************

    public function indexEncargados(){

        return view('backend.admin.configuracion.encargado.vistaencargado');
    }


    public function tablaEncargados(){

        $lista = Encargados::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.encargado.tablaencargado', compact('lista'));
    }


    public function registrarEncargado(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new Encargados();
        $dato->nombre = $request->nombre;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    public function informacionEncargado(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Encargados::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarEncargado(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Encargados::where('id', $request->id)->first()){

            Encargados::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }





}
