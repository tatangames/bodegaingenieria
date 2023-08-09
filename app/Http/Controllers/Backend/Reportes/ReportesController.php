<?php

namespace App\Http\Controllers\Backend\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\Herramientas;
use App\Models\HistorialEntradas;
use App\Models\HistorialSalidas;
use App\Models\HistorialSalidasDeta;
use App\Models\HistorialTransferido;
use App\Models\HistorialTransferidoDetalle;
use App\Models\Materiales;
use App\Models\TipoProyecto;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


    public function indexEntradaReporte(){
        return view('backend.admin.repuestos.reporte.vistaentradasalidareporte');
    }


    public function vistaParaReporteInventario(){
        return view('backend.admin.repuestos.reporte.vistareporteinventario');
    }

    public function reportePdfEntradaSalida($tipo, $desde, $hasta){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $resultsBloque = array();
        $index = 0;

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));

        // entrada
        if($tipo == 1) {

            // lista de entradas
            $listaEntrada = HistorialEntradas::whereBetween('fecha', [$start, $end])
                ->orderBy('fecha', 'ASC')
                ->get();

            foreach ($listaEntrada as $ll){

                $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

                $infoProyecto = TipoProyecto::where('id', $ll->id_tipoproyecto)->first();

                $ll->nombreproy = $infoProyecto->nombre;

                array_push($resultsBloque, $ll);

                // obtener detalle
                $listaDetalle = DB::table('historial_entradas_deta AS ed')
                    ->join('materiales AS m', 'ed.id_material', '=', 'm.id')
                    ->select('m.nombre', 'm.codigo', 'ed.cantidad', 'm.id_medida')
                    ->where('ed.id_historial', $ll->id)
                    ->orderBy('m.id', 'ASC')
                    ->get();

                foreach ($listaDetalle as $dd){
                    if($info = UnidadMedida::where('id', $dd->id_medida)->first()){
                        $dd->medida = $info->nombre;
                    }else{
                        $dd->medida = "";
                    }
                }

                $resultsBloque[$index]->detalle = $listaDetalle;
                $index++;
            }


            //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
            $mpdf->SetTitle('Entradas');

            // mostrar errores
            $mpdf->showImageErrors = false;

            $logoalcaldia = 'images/logo2.png';

            $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reporte de Entradas<br>
            Fecha: $desdeFormat  -  $hastaFormat</p>
            </div>";

            foreach ($listaEntrada as $dd) {

                $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

                $tabla .= "<tr>
                    <td  width='20%' style='font-weight: bold'>Fecha</td>
                     <td  width='45%' style='font-weight: bold'>Proyecto</td>
                     <td  width='15%' style='font-weight: bold'>Descripción</td>
                </tr>
                ";

                $tabla .= "<tr>
                    <td  width='20%'>$dd->fecha</td>
                     <td  width='45%'>$dd->nombreproy</td>
                     <td  width='15%'>$dd->descripcion</td>
                </tr>
                ";



                $tabla .= "</tbody></table>";

                $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
            <tbody>";

                $tabla .= "<tr>
                    <td width='25%' style='font-weight: bold'>Repuesto</td>
                    <td width='8%' style='font-weight: bold'>Medida</td>
                    <td width='8%' style='font-weight: bold'>Cantidad</td>
                </tr>";

                foreach ($dd->detalle as $gg) {
                    $tabla .= "<tr>
                    <td width='25%'>$gg->nombre</td>
                    <td width='8%'>$gg->medida</td>
                    <td width='8%'>$gg->cantidad</td>
                </tr>";
                }

                $tabla .= "</tbody></table>";
            }


            $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

            $tabla .= "</tbody></table>";

            $stylesheet = file_get_contents('css/cssregistro.css');
            $mpdf->WriteHTML($stylesheet,1);

            $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
            //$mpdf->WriteHTML($tabla,2);
            $mpdf->WriteHTML($tabla, 2);

            $mpdf->Output();

        }else {
            // salida



            // lista de salidas
            $listaSalida = HistorialSalidas::whereBetween('fecha', [$start, $end])
                ->orderBy('fecha', 'ASC')
                ->get();



            foreach ($listaSalida as $ll){

                $infoProyecto = TipoProyecto::where('id', $ll->id_tipoproyecto)->first();

                $ll->nombreproy = $infoProyecto->nombre;

                $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

                array_push($resultsBloque, $ll);

                // obtener detalle
                $listaDetalle = DB::table('historial_salidas_deta AS ed')
                    ->join('materiales AS m', 'ed.id_material', '=', 'm.id')
                    ->select( 'm.id', 'm.nombre', 'm.codigo', 'ed.cantidad', 'm.id_medida', 'ed.id_historial_salidas')
                    ->where('ed.id_historial_salidas', $ll->id)
                    ->orderBy('m.id', 'ASC')
                    ->get();

                foreach ($listaDetalle as $dd){
                    if($info = UnidadMedida::where('id', $dd->id_medida)->first()){
                        $dd->medida = $info->nombre;
                    }else{
                        $dd->medida = "";
                    }
                }

                $resultsBloque[$index]->detalle = $listaDetalle;
                $index++;
            }


            //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
            $mpdf->SetTitle('Salidas');

            // mostrar errores
            $mpdf->showImageErrors = false;

            $logoalcaldia = 'images/logo2.png';

            $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reporte de Salidas Repuestos<br>
            Fecha: $desdeFormat  -  $hastaFormat </p>
            </div>";

            foreach ($listaSalida as $dd) {

                $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

                $tabla .= "<tr>
                     <td  width='20%'>Fecha</td>
                     <td  width='45%'>Proyecto</td>
                     <td  width='15%'>Descripción</td>
                </tr>
                ";

                $tabla .= "<tr>
                    <td  width='20%'>$dd->fecha</td>
                     <td  width='45%'>$dd->nombreproy</td>
                     <td  width='15%'>$dd->descripcion</td>
                </tr>
                ";



                $tabla .= "</tbody></table>";

                $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
            <tbody>";

                $tabla .= "<tr>
                    <td width='25%'>Repuesto</td>
                    <td width='8%'>Medida</td>
                    <td width='20px'>Cantidad</td>
                </tr>";

                foreach ($dd->detalle as $gg) {
                    $tabla .= "<tr>
                        <td width='25%'>$gg->nombre</td>
                        <td width='8%'>$gg->medida</td>
                        <td width='20px'>$gg->cantidad</td>
                    </tr>";
                }

                $tabla .= "</tbody></table>";
            }



            $stylesheet = file_get_contents('css/cssregistro.css');
            $mpdf->WriteHTML($stylesheet,1);

            $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
            $mpdf->WriteHTML($tabla,2);

            $mpdf->Output();
        }

    }

    public function reporteInventarioActual(){


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

        //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Inventario Actual');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Inventario<br>
            </div>";


        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

        $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Material</td>
                <td width='15%' style='font-weight: bold'>Cantidad</td>
            <tr>";

        foreach ($lista as $info) {

            if($info->total > 0){

                $tabla .= "<tr>
                <td width='15%'>$info->codigo</td>
                <td width='50%'>$info->nombre</td>
                <td width='15%'>$info->total</td>
            <tr>";

            }
        }

        $tabla .= "</tbody></table>";


        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);

        $mpdf->Output();
    }




    public function vistaReporteHerramientas(){

        return view('backend.admin.herramientas.reportes.vistareporteherramienta');
    }




    //*****************************

    public function vistaQueHaSalidoProyecto(){

        // necesito todos los proyectos, ya que solo es reporte
        $proyectos = TipoProyecto::orderBy('nombre', 'ASC')
            ->get();

        return view('backend.admin.repuestos.reporte.vistaquehasalidoproyecto', compact('proyectos'));
    }



    public function pdfQueHaSalidoProyectos($idproy, $desde, $hasta, $tipo){

        $infoProyecto = TipoProyecto::where('id', $idproy)->first();

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));


            // JUNTOS
        if($tipo == 1){


            $pilaArray = array();

            $arrayHistoSalida = HistorialSalidas::where('id_tipoproyecto', $idproy)
                ->whereBetween('fecha', [$start, $end])
                ->orderBy('fecha', 'ASC')
                ->get();


            foreach ($arrayHistoSalida as $data){
                array_push($pilaArray, $data->id);
            }

            $dataArray = array();

            $arraySalidaDetalle = HistorialSalidasDeta::whereIn('id_historial_salidas', $pilaArray)->get();

            $arrayMateriales = Materiales::all();

            foreach ($arrayMateriales as $data){

                $infoMedida = UnidadMedida::where('id', $data->id_medida)->first();

                $cantidad = 0;

                foreach ($arraySalidaDetalle as $item) {

                    if($item->id_material == $data->id){
                        $cantidad = $cantidad + $item->cantidad;
                    }
                }

                if($cantidad > 0){
                    $dataArray[] = [
                        'nombre' => $data->nombre,
                        'codigo' => $data->codigo,
                        'cantidad' => $cantidad,
                        'medida' => $infoMedida->nombre
                    ];
                }
            }


            //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
            $mpdf->SetTitle('Inventario Actual');

            // mostrar errores
            $mpdf->showImageErrors = false;

            $logoalcaldia = 'images/logo2.png';

            $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reporte de Materiales Entregados <br>
            Fecha: $desdeFormat  -  $hastaFormat
            </div>";


            $tabla .= "<p style='font-weight: bold; font-size: 15px'> Proyecto: $infoProyecto->nombre <p>";


            $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

            $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Material</td>
                <td width='15%' style='font-weight: bold'>Cantidad</td>
            <tr>";

            foreach ($dataArray as $info) {

                $codigo = $info['codigo'];
                $nombre = $info['nombre'];
                $cantidad = $info['cantidad'];

                $tabla .= "<tr>
                <td width='15%'>$codigo</td>
                <td width='50%'>$nombre</td>
                <td width='15%'>$cantidad</td>
            <tr>";

            }

            $tabla .= "</tbody></table>";


            $stylesheet = file_get_contents('css/cssregistro.css');
            $mpdf->WriteHTML($stylesheet,1);

            $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
            $mpdf->WriteHTML($tabla,2);

            $mpdf->Output();


        }else{
            // SEPARADOS


            $arrayHistoSalida = HistorialSalidas::where('id_tipoproyecto', $idproy)
                ->whereBetween('fecha', [$start, $end])
                ->orderBy('fecha', 'ASC')
                ->get();

            $resultsBloque = array();
            $index = 0;


            foreach ($arrayHistoSalida as $data){

                array_push($resultsBloque, $data);

                $data->fecha = date("d-m-Y", strtotime($data->fecha));

                $arrayDetalle = HistorialSalidasDeta::where('id_historial_salidas', $data->id)->get();

                foreach ($arrayDetalle as $deta){

                    $infoMate = Materiales::where('id', $deta->id_material)->first();
                    $infoMedida = UnidadMedida::where('id', $infoMate->id_medida)->first();

                    $deta->nombremate = $infoMate->nombre;
                    $deta->codigo = $infoMate->codigo;
                    $deta->unimedida = $infoMedida->nombre;
                }

                $resultsBloque[$index]->detalle = $arrayDetalle;
                $index++;
            }


            //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
            $mpdf->SetTitle('Inventario Actual');

            // mostrar errores
            $mpdf->showImageErrors = false;

            $logoalcaldia = 'images/logo2.png';

            $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reporte de Materiales Entregados <br>
            Fecha: $desdeFormat  -  $hastaFormat
            </div>";


            $tabla .= "<p style='font-weight: bold; font-size: 15px'> Proyecto: $infoProyecto->nombre <p>";


            foreach ($arrayHistoSalida as $info) {


                $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

                    $tabla .= "<tr>
                        <td width='15%' style='font-weight: bold'>Fecha</td>
                        <td width='50%' style='font-weight: bold'>Descripción</td>
                    <tr>";

                    $tabla .= "<tr>
                        <td width='15%' style='font-weight: normal'>$info->fecha</td>
                        <td width='50%' style='font-weight: normal'>$info->descripcion</td>
                    <tr>";

                $tabla .= "</tbody></table>";



                $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

                    $tabla .= "<tr>
                    <td width='12%' style='font-weight: bold'>Código</td>
                    <td width='12%' style='font-weight: bold'>Medida</td>
                    <td width='30%' style='font-weight: bold'>Material</td>
                    <td width='12%' style='font-weight: bold'>Cantidad</td>
                <tr>";


                    foreach ($info->detalle as $data) {
                                $tabla .= "<tr>
                        <td width='12%' style='font-weight: normal'>$data->codigo</td>
                        <td width='12%' style='font-weight: normal'>$data->nombremate</td>
                        <td width='30%' style='font-weight: normal'>$data->unimedida</td>
                        <td width='12%' style='font-weight: normal'>$data->cantidad</td>
                    <tr>";

                }

                $tabla .= "</tbody></table>";

            }


            $stylesheet = file_get_contents('css/cssregistro.css');
            $mpdf->WriteHTML($stylesheet,1);

            $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
            $mpdf->WriteHTML($tabla,2);

            $mpdf->Output();
        }



    }




    public function vistaQueTengoPorProyecto(){

        $terminados = HistorialTransferido::all();
        $pilaIdTransfe = array();

        foreach ($terminados as $data){
            array_push($pilaIdTransfe, $data->id_tipoproyecto);
        }

        $proyectos = TipoProyecto::orderBy('nombre', 'ASC')
            ->whereNotIn('id', $pilaIdTransfe)
            ->get();

        return view('backend.admin.repuestos.reporte.vistaquetengoporproyecto', compact('proyectos'));
    }



    public function reporteQueTengoPorProyecto($idproy){

        $infoProyecto = TipoProyecto::where('id', $idproy)->first();

        $arrayInventario = Entradas::where('id_tipoproyecto', $idproy)->get();

        foreach ($arrayInventario as $dato){

            $infoMate = Materiales::where('id', $dato->id_material)->first();

            $dato->nombreMate = $infoMate->nombre;
            $dato->codigoMate = $infoMate->codigo;
        }


        //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Inventario Actual');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Inventario de Proyecto <br>
            </div>";


        $tabla .= "<p style='font-weight: bold; font-size: 15px'> Proyecto: $infoProyecto->nombre <p>";


        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

        $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Material</td>
                <td width='15%' style='font-weight: bold'>Cantidad</td>
            <tr>";

        foreach ($arrayInventario as $info) {

            $tabla .= "<tr>
                <td width='15%'>$info->codigoMate</td>
                <td width='50%'>$info->nombreMate</td>
                <td width='15%'>$info->cantidad</td>
            <tr>";

        }

        $tabla .= "</tbody></table>";


        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);

        $mpdf->Output();
    }



    public function vistaProyectoCompletado(){

        $transferido = HistorialTransferido::orderBy('fecha', 'ASC')->get();

        foreach ($transferido as $dato){

            $dato->fecha = date("d-m-Y", strtotime($dato->fecha));

            $infoProy = TipoProyecto::where('id', $dato->id_tipoproyecto)->first();

            $dato->nomproy = $infoProy->nombre;
        }

        return view('backend.admin.repuestos.reporte.vistaproyectocompletado', compact('transferido'));
    }



    public function reporteProyectoTerminado($idtrans){


        $infoTrans = HistorialTransferido::where('id', $idtrans)->first();

        $infoProyecto = TipoProyecto::where('id', $infoTrans->id_tipoproyecto)->first();

        $listado = HistorialTransferidoDetalle::where('id_historial_transf', $idtrans)->get();

        foreach ($listado as $dato){

            $infoMaterial = Materiales::where('id', $dato->id_material)->first();

            $dato->nommaterial = $infoMaterial->nombre;
            $dato->codmaterial = $infoMaterial->codigo;

            $infoUnidad = UnidadMedida::where('id', $infoMaterial->id_medida)->first();
            $dato->nomunidad = $infoUnidad->nombre;

        }


        //$mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Transferido');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reporte de Proyecto Completado<br>
            </div>";


        $tabla .= "<p style='font-weight: bold; font-size: 15px'> Proyecto: $infoProyecto->nombre <p>";

        $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";


        $tabla .= "<tr>
                    <td  width='14%' style='font-weight: bold'>Código</td>
                    <td  width='14%' style='font-weight: bold'>Medida</td>
                    <td  width='22%' style='font-weight: bold'>Material</td>
                    <td  width='12%' style='font-weight: bold'>Cantidad</td>
                </tr>
                ";


        foreach ($listado as $dd) {

            $tabla .= "<tr>
                     <td  width='14%'>$dd->codmaterial</td>
                     <td  width='14%'>$dd->codmaterial</td>
                     <td  width='22%'>$dd->nomunidad</td>
                     <td  width='12%'>$dd->cantidad</td>
                </tr>
                ";
        }


        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        //$mpdf->WriteHTML($tabla,2);
        $mpdf->WriteHTML($tabla, 2);

        $mpdf->Output();
    }





}
