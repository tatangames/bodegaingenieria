<?php

namespace App\Http\Controllers\Backend\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Herramientas;
use App\Models\HistoHerramientaReingreso;
use App\Models\HistoHerramientaSalida;
use App\Models\HistoHerramientaSalidaDetalle;
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

        }

        $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        //$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Inventario Actual');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Inventario de Herramientas<br>
            </div>";


        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

        $tabla .= "<tr>
                <td width='15%' style='font-weight: bold'>Código</td>
                <td width='50%' style='font-weight: bold'>Herramienta</td>
                <td width='15%' style='font-weight: bold'>Cantidad</td>
            <tr>";

        foreach ($lista as $info) {

            $tabla .= "<tr>
                <td width='15%'>$info->codigo</td>
                <td width='50%'>$info->nombre</td>
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

            array_push($resultsBloque, $ll);

            // obtener detalle
            $listaDetalle = DB::table('histo_herramienta_salida_deta AS histo')
                ->join('herramientas AS he', 'histo.id_herramienta', '=', 'he.id')
                ->select('he.nombre', 'histo.cantidad AS cantisalida', 'he.codigo')
                ->where('histo.id_herra_salida', $ll->id)
                ->orderBy('histo.cantidad', 'ASC')
                ->get();

            foreach ($listaDetalle as $dd){

            }

            $resultsBloque[$index]->detalle = $listaDetalle;
            $index++;
        }

        $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        //$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Salidas');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Salidas de Herramientas<br>
            Fecha: $desdeFormat  -  $hastaFormat </p>
            </div>";

        foreach ($listaSalida as $dd) {

            $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";

            $tabla .= "<tr>
                    <td  width='13%' style='font-weight: bold'>Fecha</td>
                    <td  width='15%' style='font-weight: bold'>Descripción</td>
                </tr>
                ";

            $tabla .= "<tr>
                    <td width='13%'>$dd->fecha</td>
                    <td width='15%'>$dd->descripcion</td>
                    ";


            $tabla .= "<tr>
                    <td  width='13%' style='font-weight: bold'>Quien Entrego</td>
                    <td  width='15%' style='font-weight: bold'>Quien Recibio</td>
                </tr>
                ";


            $tabla .= "<tr>
                    <td width='13%'>$dd->quien_recibe</td>
                    <td width='15%'>$dd->quien_entrega</td>
                    ";

            $tabla .= "</tbody></table>";

            $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
            <tbody>";

            $tabla .= "<tr>
                    <td width='12%'>Código</td>
                    <td width='20%'>Herramienta</td>
                    <td width='14%'>Cantidad</td>
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



        $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER']);
        //$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Reingreso');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            Reingreso de Herramientas<br>
            Fecha: $desdeFormat  -  $hastaFormat </p>
            </div>";

        $tabla .= "<table width='100%' id='tablaFor'>
                    <tbody>";


        $tabla .= "<tr>
                        <td style='font-weight: bold' width='12%'>Salio</td>
                        <td style='font-weight: bold' width='12%'>Reingreso</td>
                        <td style='font-weight: bold' width='12%'>Código</td>
                        <td style='font-weight: bold' width='18%'>Herramienta</td>
                        <td style='font-weight: bold' width='20%'>Descripción</td>
                    </tr>";

        foreach ($listaReingreso as $dd) {


            $tabla .= "<tr>
                        <td width='12%'>$dd->fechasalida</td>
                        <td width='12%'>$dd->fechareingreso</td>
                        <td width='12%'>$dd->codiherra</td>
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











}
