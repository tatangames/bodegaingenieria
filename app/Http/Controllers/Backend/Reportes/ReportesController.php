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


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);


        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';

        // entrada
        if($tipo == 1) {

            $mpdf->SetTitle('Entradas');

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

            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE ENTRADAS</h1>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                <p style='font-size: 14px; margin: 0; color: #000;'>Fecha: $desdeFormat  -  $hastaFormat</p>
                </div>
              ";


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
            $mpdf->WriteHTML($tabla,2);
            $mpdf->Output();
        }else {
            // salida

            $mpdf->SetTitle('Salidas');

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

            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";


            $tabla .= "
                    <div style='text-align: center; margin-top: 20px;'>
                        <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE SALIDAS</h1>
                    </div>
                    <div style='text-align: center; margin-top: 10px;'>
                    <p style='font-size: 14px; margin: 0; color: #000;'>Fecha: $desdeFormat  -  $hastaFormat</p>
                </div>
              ";

            foreach ($listaSalida as $dd) {

                $tabla .= "<table width='100%' id='tablaFor'><tbody>";

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

    public function reporteInventarioActual($tipo){


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';

        // JUNTOS
        if($tipo == 1){

            $mpdf->SetTitle('Inventario Actual');

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

            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INVENTARIO DE RESPUESTOS</h1>
                </div>
              ";


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

        }else{
         // SEPARADOS

            $mpdf->SetTitle('Inventario Repuestos');

            $listaProyPrimero = TipoProyecto::orderBy('nombre')
                ->where('transferido', 0)
                ->get();

            $resultsBloque = array();
            $index = 0;

            $pilaArrayId = array();

            // VERIFICAR QUE HAYA MATERIALES EN UN PROYECTO AL MENOS
            foreach ($listaProyPrimero as $infodata){
                $arrayEntradas = Entradas::where('id_tipoproyecto', $infodata->id)->get();
                foreach ($arrayEntradas as $info){
                    if($info->cantidad > 0){
                        // si entra aqui, si hay 1 material en inventario
                        array_push($pilaArrayId, $infodata->id);
                        break;
                    }
                }
            }

            $listaProy = TipoProyecto::orderBy('nombre')
                ->whereIn('id', $pilaArrayId)
                ->get();

            foreach ($listaProy as $dato){

                array_push($resultsBloque, $dato);
                $arrayEntradas = Entradas::where('id_tipoproyecto', $dato->id)->get();

                foreach ($arrayEntradas as $info){
                    $infoMate = Materiales::where('id', $info->id_material)->first();
                    $infoMedida = UnidadMedida::where('id', $infoMate->id_medida)->first();
                    $info->nombremate = $infoMate->nombre;
                    $info->codigomate = $infoMate->codigo;
                    $info->unimedida = $infoMedida->nombre;
                }

                $resultsBloque[$index]->detalle = $arrayEntradas;
                $index++;
            }

            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INVENTARIO DE RESPUESTOS</h1>
                </div>
              ";


            foreach ($listaProy as $dd) {

                $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

                $tabla .= "<tr>
                    <td  style='font-weight: bold'>Proyecto</td>
                </tr>
                ";

                $tabla .= "<tr>
                    <td>$dd->nombre</td>
                    ";

                $tabla .= "</tbody></table>";

                $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
            <tbody>";

                $tabla .= "<tr>
                    <td width='12%' style='font-weight: bold'>Código</td>
                    <td width='12%' style='font-weight: bold'>Medida</td>
                    <td width='20%' style='font-weight: bold'>Repuesto</td>
                    <td width='14%' style='font-weight: bold'>Cantidad</td>
                </tr>";

                foreach ($dd->detalle as $gg) {
                    $tabla .= "<tr>
                        <td width='12%'>$gg->codigomate</td>
                        <td width='12%'>$gg->unimedida</td>
                        <td width='20%'>$gg->nombremate</td>
                        <td width='14%'>$gg->cantidad</td>
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






    //*****************************

    public function vistaQueHaSalidoProyecto(){

        // necesito todos los proyectos, ya que solo es reporte
        $proyectos = TipoProyecto::orderBy('nombre', 'ASC')
            ->get();

        return view('backend.admin.repuestos.reporte.vistaquehasalidoproyecto', compact('proyectos'));
    }



    public function pdfQueHaSalidoProyectos($idproy, $desde, $hasta, $tipo){


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';


        $infoProyecto = TipoProyecto::where('id', $idproy)->first();
        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();
        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));

            // JUNTOS
        if($tipo == 1){

            $mpdf->SetTitle('Inventario Actual');
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

            usort($dataArray, function ($a, $b) {
                return strcmp($a['nombre'], $b['nombre']);
            });


            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE MATERIALES ENTREGADOS</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";


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
                    <td width='50%' style='text-align: left !important;'>$nombre</td>
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
            $mpdf->SetTitle('Inventario Actual');

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


            $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE MATERIALES ENTREGADOS</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";


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
                        <td width='12%' style='font-weight: normal'>$data->unimedida</td>
                        <td width='30%' style='font-weight: normal'>$data->nombremate</td>
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

        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';


        $infoProyecto = TipoProyecto::where('id', $idproy)->first();
        $arrayInventario = Entradas::where('id_tipoproyecto', $idproy)->get();

        foreach ($arrayInventario as $dato){

            $infoMate = Materiales::where('id', $dato->id_material)->first();

            $dato->nombreMate = $infoMate->nombre;
            $dato->codigoMate = $infoMate->codigo;
        }

        $fechahoy = Carbon::parse(Carbon::now());
        $fechaFormat = date("d-m-Y", strtotime($fechahoy));



        $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

        $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INVENTARIO ACTUAL</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $fechaFormat</h2>
                </div>
                 <div style='text-align: left; margin-top: 20px;'>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Proyecto: $infoProyecto->nombre</h2>
                </div>
              ";

        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

        $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Material</td>
                <td width='15%' style='font-weight: bold'>Cantidad</td>
            <tr>";

        foreach ($arrayInventario as $info) {

            if($info->cantidad > 0){
                $tabla .= "<tr>
                <td width='15%'>$info->codigoMate</td>
                <td width='50%'>$info->nombreMate</td>
                <td width='15%'>$info->cantidad</td>
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


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Transferido');

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


        $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";

        $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE PROYECTO COMPLETADO</h1>
                </div>
                 <div style='text-align: left; margin-top: 20px;'>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Proyecto: $infoProyecto->nombre</h2>
                </div>
              ";


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
                     <td  width='14%'>$dd->nomunidad</td>
                     <td  width='22%'>$dd->nommaterial</td>
                     <td  width='12%'>$dd->cantidad</td>
                </tr>
                ";
        }

        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }


    public function vistaSalidaPorMaterial(){

        $arrayMateriales = Materiales::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.repuestos.reporte.vistasalidapormaterial', compact('arrayMateriales'));
    }




    public function pdfReporteMaterialesSalidas($desde, $hasta, $materiales){

        $porciones = explode("-", $materiales);
        // Filtrar todos el historial entradas salidas, obtener su id
        $arrayIdSalidas = HistorialSalidasDeta::whereIn('id_material', $porciones)->get();
        $pilaIdSalidas = array();

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $resultsBloque = array();
        $index = 0;

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));

        foreach ($arrayIdSalidas as $dato){
            array_push($pilaIdSalidas, $dato->id_historial_salidas);
        }

        // todos los historial salidas donde por lo menos existe el material para reporte
        $arraySalidas = HistorialSalidas::whereIn('id', $pilaIdSalidas)
            ->whereBetween('fecha', [$start, $end])
            ->orderBy('fecha', 'ASC')
            ->get();

        $pilaIdSalidasFormat = array();
        foreach ($arraySalidas as $dato){
            array_push($pilaIdSalidasFormat, $dato->id);
        }


        foreach ($arraySalidas as $infoFila){
            array_push($resultsBloque, $infoFila);

            $infoFila->fechaFormat = date("d-m-Y", strtotime($infoFila->fecha));

            $infoTipoProy = TipoProyecto::where('id', $infoFila->id_tipoproyecto)->first();
            $infoFila->nombreProy = $infoTipoProy->nombre;

            $arrayDetalle = DB::table('historial_salidas_deta AS deta')
                ->join('materiales AS ma', 'ma.id', '=', 'deta.id_material')
                ->select('ma.nombre', 'deta.id_material', 'deta.id_historial_salidas', 'deta.cantidad')
                ->where('deta.id_historial_salidas', $infoFila->id)
                ->whereIn('deta.id_material', $porciones)
                ->orderBy('ma.nombre', 'ASC')
                ->get();

            $resultsBloque[$index]->detalle = $arrayDetalle;
            $index++;
        }


        // CONTEO INDIVIDUAL
        $arrayMaterial = Materiales::whereIn('id', $porciones)->get();

        $dataArray = array();

        foreach ($arrayMaterial as $dato){

            $conteoDetalle = HistorialSalidasDeta::whereIn('id_historial_salidas', $pilaIdSalidasFormat)
                ->where('id_material', $dato->id)
                ->sum('cantidad');

            $conteoDetalle = number_format((float)$conteoDetalle, 2, '.', ',');

            $dataArray[] = [
                'nombre' => $dato->nombre,
                'cantidadtotal' => $conteoDetalle,
            ];
        }



        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Salida por Materiales');




        $tabla = "
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <!-- Logo izquierdo -->
                    <td style='width: 15%; text-align: left;'>
                        <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                    </td>
                    <!-- Texto centrado -->
                    <td style='width: 60%; text-align: center;'>
                        <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                        <h2 style='font-size: 14px; margin: 0; color: #003366; text-transform: uppercase;'>UNIDAD ELÉCTRICA</h2>
                    </td>
                    <!-- Logo derecho -->
                    <td style='width: 10%; text-align: right;'>
                        <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                    </td>
                </tr>
            </table>
            <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
            ";


        $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE SALIDA DE MATERIAL</h1>
                </div>
                 <div style='text-align: left; margin-top: 20px;'>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";

        foreach ($arraySalidas as $info) {

            $tabla .= "<table width='100%' id='tablaFor'>
                        <tbody>";

                $tabla .= "<tr>
                        <td width='15%' style='font-weight: bold'>Fecha</td>
                        <td width='50%' style='font-weight: bold'>Proyecto</td>
                        <td width='15%' style='font-weight: bold'>Descripción</td>
                    <tr>";

                $tabla .= "<tr>
                        <td width='15%'>$info->fechaFormat</td>
                        <td width='50%'>$info->nombreProy</td>
                        <td width='15%'>$info->descripcion</td>
                    <tr>";

            $tabla .= "</tbody></table>";


            $tabla .= "<table width='100%' id='tablaFor'>
                        <tbody>";

                $tabla .= "<tr>
                        <td width='15%' style='font-weight: bold'>Material</td>
                        <td width='10%' style='font-weight: bold'>Cantidad</td>
                    <tr>";

                foreach ($info->detalle as $dato) {
                    $tabla .= "<tr>
                        <td width='15%'>$dato->nombre</td>
                        <td width='10%'>$dato->cantidad</td>
                    <tr>";
                }

            $tabla .= "</tbody></table>";
        }



        $tabla .= "<p style='font-weight: bold; margin-top: 30px'>MATERIALES ENTREGADOS</p>";

        $tabla .= "<table width='100%' id='tablaFor'>
                        <tbody>";

        $tabla .= "<tr>
                        <td width='50%' style='font-weight: bold'>Material</td>
                        <td width='15%' style='font-weight: bold'>Cantidad Total</td>
                    <tr>";

        foreach ($dataArray as $info){

            $infoNombre = $info['nombre'];
            $infoConteo = $info['cantidadtotal'];

            $tabla .= "<tr>
                        <td width='50%'>$infoNombre</td>
                        <td width='15%'>$infoConteo</td>
                    <tr>";

        }

        $tabla .= "</tbody></table>";


        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }



}
