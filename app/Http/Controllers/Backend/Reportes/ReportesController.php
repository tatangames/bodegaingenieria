<?php

namespace App\Http\Controllers\Backend\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Entradas;
use App\Models\HistorialEntradas;
use App\Models\HistorialSalidas;
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
                    <td width='8%'>Cantidad</td>
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

            // mostrar erroresq     Q   n
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

            $tabla .= "<tr>
                <td width='15%'>$info->codigo</td>
                <td width='50%'>$info->nombre</td>
                <td width='15%'>$info->total</td>
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
