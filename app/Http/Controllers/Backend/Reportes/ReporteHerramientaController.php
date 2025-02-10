<?php

namespace App\Http\Controllers\Backend\Reportes;

use App\Http\Controllers\Controller;
use App\Models\HerramientaPendiente;
use App\Models\Herramientas;
use App\Models\HistoHerramientaDescartada;
use App\Models\HistoHerramientaRegistro;
use App\Models\HistoHerramientaRegistroDeta;
use App\Models\HistoHerramientaReingreso;
use App\Models\HistoHerramientaSalida;
use App\Models\HistoHerramientaSalidaDetalle;
use App\Models\QuienEntrega;
use App\Models\QuienRecibe;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteHerramientaController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function pdfHerramientasActuales(){


        $lista = Herramientas::orderBy('nombre', 'ASC')->get();

        foreach ($lista as $item) {
            $medida = '';
            if($dataUnidad = UnidadMedida::where('id', $item->id_medida)->first()){
                $medida = $dataUnidad->nombre;
            }

            $item->medida = $medida;


            // buscar la misma herramienta para salida

            $infoPendiente = HerramientaPendiente::where('id_herramienta', $item->id)->get();

            $cantidadSalida = 0;

            foreach ($infoPendiente as $dato){

                $cantidadSalida = $cantidadSalida + $dato->cantidad;

            }

            $item->inicialherra = $cantidadSalida + $item->cantidad;

            $item->cantisalida = $cantidadSalida;

            $inicial = $cantidadSalida + $item->cantidad;

            $item->actualherramienta = $inicial - $cantidadSalida;
        }


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Inventario Actual');




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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INVENTARIO DE HERRAMIENTAS</h1>
                </div>
              ";


        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

        $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Herramienta</td>
                <td width='15%' style='font-weight: bold'>Inicial Fijo</td>
                <td width='15%' style='font-weight: bold'>Salida</td>
                <td width='15%' style='font-weight: bold'>En Bodega</td>
            <tr>";

        foreach ($lista as $info) {

            if($info->inicialherra > 0){

                $tabla .= "<tr>
                <td width='15%'>$info->codigo</td>
                <td width='50%'>$info->nombre</td>
                <td width='15%'>$info->inicialherra</td>
                <td width='15%'>$info->cantisalida</td>
                <td width='12%'>$info->actualherramienta</td>
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



    public function pdfHerramientasSalidas($desde, $hasta){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $resultsBloque = array();
        $index = 0;

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));



        // lista de salidas
        $listaSalida = HistoHerramientaSalida::whereBetween('fecha', [$start, $end])
            ->orderBy('fecha', 'ASC')
            ->get();

        foreach ($listaSalida as $ll){

            $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

            $infoEntrego = QuienEntrega::where('id', $ll->quien_entrega)->first();
            $infoRecibe = QuienRecibe::where('id', $ll->quien_recibe)->first();

            $ll->nomrecibe = $infoRecibe->nombre;
            $ll->nomentrega = $infoEntrego->nombre;

            array_push($resultsBloque, $ll);

            // obtener detalle
            $listaDetalle = DB::table('histo_herramienta_salida_deta AS histo')
                ->join('herramientas AS he', 'histo.id_herramienta', '=', 'he.id')
                ->select('he.nombre', 'histo.cantidad AS cantisalida', 'he.codigo')
                ->where('histo.id_herra_salida', $ll->id)
                ->orderBy('histo.cantidad', 'ASC')
                ->get();



            $resultsBloque[$index]->detalle = $listaDetalle;
            $index++;
        }


        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Salidas');



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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>SALIDAS DE HERRAMIENTAS</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";


        foreach ($listaSalida as $dd) {

            $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

            $tabla .= "<tr>
                    <td  width='13%' style='font-weight: bold'>Fecha</td>
                    <td  width='15%' colspan='2' style='font-weight: bold'>Descripción</td>
                </tr>
                ";

            $tabla .= "<tr>
                    <td width='13%'>$dd->fecha</td>
                    <td width='15%' colspan='2'>$dd->descripcion</td>
                    ";


            $tabla .= "<tr>
                    <td  width='13%' style='font-weight: bold'>Quien Entrego</td>
                    <td  width='15%' style='font-weight: bold'>Quien Recibio</td>
                     <td  width='15%' style='font-weight: bold'># de Salida</td>
                </tr>
                ";

            $tabla .= "<tr>
                    <td width='13%'>$dd->nomentrega</td>
                    <td width='15%'>$dd->nomrecibe</td>
                    <td width='15%'>$dd->num_salida</td>
                    ";

            $tabla .= "</tbody></table>";

            $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
            <tbody>";

            $tabla .= "<tr>
                    <td width='12%' style='font-weight: bold'>Código</td>
                    <td width='20%' style='font-weight: bold'>Herramienta</td>
                    <td width='14%' style='font-weight: bold'>Cantidad</td>
                </tr>";

            foreach ($dd->detalle as $gg) {
                $tabla .= "<tr>
                        <td width='12%'>$gg->codigo</td>
                        <td width='20%'>$gg->nombre</td>
                        <td width='14%'>$gg->cantisalida</td>
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



    public function pdfHerramientasReingreso($desde, $hasta){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();
        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));


        // lista de salidas
        $listaReingreso = HistoHerramientaReingreso::whereBetween('fecha', [$start, $end])
            ->orderBy('fecha', 'ASC')
            ->get();

        foreach ($listaReingreso as $ll){

            $ll->fechareingreso = date("d-m-Y", strtotime($ll->fecha));

            $infoHerra = Herramientas::where('id', $ll->id_herramienta)->first();
            $ll->nomherra = $infoHerra->nombre;
            $ll->codiherra = $infoHerra->codigo;


            $infoSalida = HistoHerramientaSalida::where('id', $ll->id_histo_herra_salida)->first();
            $ll->fechasalida = date("d-m-Y", strtotime($infoSalida->fecha));
        }





        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Reingreso');




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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REINGRESO DE HERRAMIENTAS</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";


        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";


        $tabla .= "<tr>
                        <td style='font-weight: bold' width='10%'>Salio</td>
                        <td style='font-weight: bold' width='10%'>Reingreso</td>
                        <td style='font-weight: bold' width='16%'>Herramienta</td>
                        <td style='font-weight: bold' width='16%'>Descripción</td>
                        <td style='font-weight: bold' width='16%'>Cantidad</td>
                    </tr>";


        foreach ($listaReingreso as $dd) {

            $tabla .= "<tr>
                        <td width='10%'>$dd->fechasalida</td>
                        <td width='10%'>$dd->fechareingreso</td>
                        <td width='16%'>$dd->nomherra</td>
                        <td width='16%'>$dd->descripcion</td>
                        <td width='16%'>$dd->cantidad</td>
                    </tr>";
        }


        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);

        $mpdf->Output();
    }


    public function pdfHerramientasDescartadas(){

        // listado
        $listado = HistoHerramientaDescartada::orderBy('cantidad', 'ASC')->get();

        foreach ($listado as $dato){

            $dato->fecha = date("d-m-Y", strtotime($dato->fecha));

            $infoHerra = Herramientas::where('id', $dato->id_herramienta)->first();
            $dato->nomherra = $infoHerra->nombre;
            $dato->codiherra = $infoHerra->codigo;

            $infoMedida = UnidadMedida::where('id', $infoHerra->id_medida)->first();
            $dato->herramedida = $infoMedida->nombre;
        }




        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Herramienta Descartada');




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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>HERRAMIENTAS DESCARTADAS</h1>
                </div>
              ";




        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";


        $tabla .= "<tr>
                        <td style='font-weight: bold' width='12%'>Fecha</td>
                        <td style='font-weight: bold' width='12%'>Código</td>
                        <td style='font-weight: bold' width='12%'>Medida</td>
                        <td style='font-weight: bold' width='18%'>Material</td>
                        <td style='font-weight: bold' width='20%'>Descripción</td>
                    </tr>";

        foreach ($listado as $dd) {

            $tabla .= "<tr>
                        <td width='12%'>$dd->fecha</td>
                        <td width='12%'>$dd->codiherra</td>
                        <td width='12%'>$dd->herramedida</td>
                        <td width='18%'>$dd->nomherra</td>
                        <td width='20%'>$dd->descripcion</td>
                    </tr>";
        }

        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssregistro.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);

        $mpdf->Output();

    }




    public function pdfNuevasHerramientas($desde, $hasta){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();



        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));


        // lista de ingresos nuevos
        $listaIngreso = HistoHerramientaRegistro::whereBetween('fecha', [$start, $end])
            ->orderBy('fecha', 'ASC')
            ->get();

        $resultsBloque = array();
        $index = 0;

        foreach ($listaIngreso as $dato){

            array_push($resultsBloque, $dato);

            $dato->fecha = date("d-m-Y", strtotime($dato->fecha));

            $arrayDetalle = HistoHerramientaRegistroDeta::where('id_herra_registro', $dato->id)->get();

            foreach ($arrayDetalle as $deta){

                $infoHerra = Herramientas::where('id', $deta->id_herramienta)->first();

                $nommedida = "";
                if($infoMedida = UnidadMedida::where('id', $infoHerra->id_medida)->first()){
                    $nommedida = $infoMedida->nombre;
                }

                $deta->nombreherra = $infoHerra->nombre;
                $deta->codigo = $infoHerra->codigo;
                $deta->unimedida = $nommedida;
            }

            $resultsBloque[$index]->detalle = $arrayDetalle;
            $index++;
        }





        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        // mostrar errores
        $mpdf->showImageErrors = false;
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Nueva Herramienta');





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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INGRESO DE HERRAMIENTAS</h1>
                    <h2 style='font-size: 16px; margin: 0; color: #000;'> Fecha: $desdeFormat  -  $hastaFormat</h2>
                </div>
              ";



        foreach ($listaIngreso as $info) {


            $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

                $tabla .= "<tr>
                            <td width='15%' style='font-weight: bold'>Fecha</td>
                            <td width='30%' style='font-weight: bold'>Descripción</td>
                        <tr>";

                $tabla .= "<tr>
                            <td width='15%' style='font-weight: normal'>$info->fecha</td>
                            <td width='30%' style='font-weight: normal'>$info->descripcion</td>
                        <tr>";

            $tabla .= "</tbody></table>";

            $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

            $tabla .= "<tr>
                    <td width='10%' style='font-weight: bold'>Código</td>
                    <td width='15%' style='font-weight: bold'>Medida</td>
                    <td width='25%' style='font-weight: bold'>Herramienta</td>
                    <td width='12%' style='font-weight: bold'>Cantidad</td>
                <tr>";

            foreach ($info->detalle as $data) {
                $tabla .= "<tr>
                        <td style='font-weight: normal'>$data->codigo</td>
                        <td style='font-weight: normal'>$data->unimedida</td>
                        <td style='font-weight: normal'>$data->nombreherra</td>
                        <td style='font-weight: normal'>$data->cantidad</td>
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
