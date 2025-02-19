<?php

namespace App\Http\Controllers\Backend\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Anios;
use App\Models\CierreProyecto;
use App\Models\Encargados;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\Herramientas;
use App\Models\HistorialEntradas;
use App\Models\HistorialSalidas;
use App\Models\HistorialSalidasDeta;
use App\Models\HistorialTransferido;
use App\Models\HistorialTransferidoDetalle;
use App\Models\Materiales;
use App\Models\ProyectoEncargado;
use App\Models\QuienRecibe;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
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

    public function indexEntradasReporte(){

        $arrayProyectos = TipoProyecto::orderBy('nombre', 'ASC')->get();

        foreach ($arrayProyectos as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreCompleto = "(" . $infoAnio->nombre . ") " . $item->nombre;
        }

        return view('backend.admin.reportes.entradas.vistaentradas', compact('arrayProyectos'));
    }

    public function reportePdfEntradas($idproyecto){

        $resultsBloque = array();
        $index = 0;

        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';

        $infoProyecto = TipoProyecto::where('id', $idproyecto)->first();
        $infoAnio = Anios::where('id', $infoProyecto->id_anio)->first();
        $arrayEncargados = ProyectoEncargado::where('id_tipoproyecto', $idproyecto)->get();
        foreach ($arrayEncargados as $item) {
            $infoEncargado = Encargados::where('id', $item->id_encargado)->first();
            $item->nombreEncargado = $infoEncargado->nombre;
        }

        $mpdf->SetTitle('Entradas');

        // lista de entradas
        $arrayEntradas = Entradas::where('id_tipoproyecto', $idproyecto)->orderBy('fecha', 'ASC')->get();

        foreach ($arrayEntradas as $item){
            array_push($resultsBloque, $item);

            $item->fecha = date("d-m-Y", strtotime($item->fecha));

            // obtener detalle
            $listaDetalle = DB::table('entradas_detalle AS ed')
            ->join('materiales AS m', 'ed.id_material', '=', 'm.id')
            ->select('m.nombre', 'm.codigo', 'ed.cantidad', 'm.id_medida')
            ->where('ed.id_entradas', $item->id)
            ->get();

            foreach ($listaDetalle as $dd){
                $info = UnidadMedida::where('id', $dd->id_medida)->first();
                $dd->medida = $info->nombre;
            }

            $listaDetalle = $listaDetalle->sortBy('nombre');
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
                </div>
              ";

            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Año: <strong>$infoAnio->nombre</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Codigo: <strong>$infoProyecto->codigo</strong></p>
                <p style='font-size: 14px; margin: 0; color: #000;'>Proyecto: <strong>$infoProyecto->nombre</strong></p>";

                if ($arrayEncargados->isNotEmpty()) {
                    foreach ($arrayEncargados as $item) {
                        $tabla .= " <p style='font-size: 14px; margin: 0; color: #000;'>Encargado: <strong>$item->nombreEncargado</strong></p>";
                    }
                }

            $tabla .= "</div>
              ";

            foreach ($arrayEntradas as $dd) {

                $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

                if($dd->descripcion != null){
                    $tabla .= "<tr>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Fecha</td>
                     <td  width='15%' style='font-weight: bold; font-size: 11px'>Descripción</td>
                    </tr>
                    ";

                        $tabla .= "<tr>
                        <td style='font-size: 10px'>$dd->fecha</td>
                         <td style='font-size: 10px'>$dd->descripcion</td>
                    </tr>
                    ";
                }else{
                    $tabla .= "<tr>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Fecha</td>
                    </tr>
                    ";

                    $tabla .= "<tr>
                        <td style='font-size: 10px'>$dd->fecha</td>
                    </tr>
                    ";
                }

                $tabla .= "</tbody></table>";

                $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
                <tbody>";

                $tabla .= "<tr>
                    <td width='25%' style='font-weight: bold; font-size: 11px'>Material</td>
                    <td width='8%' style='font-weight: bold; font-size: 11px'>Medida</td>
                    <td width='8%' style='font-weight: bold; font-size: 11px'>Cantidad</td>
                </tr>";

                foreach ($dd->detalle as $gg) {
                    $tabla .= "<tr>
                        <td style='font-size: 10px'>$gg->nombre</td>
                        <td style='font-size: 10px'>$gg->medida</td>
                        <td style='font-size: 10px'>$gg->cantidad</td>
                    </tr>";
                }

                $tabla .= "</tbody></table>";
            }

            $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

            $tabla .= "</tbody></table>";

            $stylesheet = file_get_contents('css/cssreportes.css');
            $mpdf->WriteHTML($stylesheet,1);

            $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
            $mpdf->WriteHTML($tabla,2);
            $mpdf->Output();

    }



    ///*********** REPORTE DE SALIDAS *******************************

    public function indexSalidasReporte(){

        $arrayProyectos = TipoProyecto::orderBy('nombre', 'ASC')->get();
        $arrayRecibe = QuienRecibe::orderBy('nombre', 'ASC')->get();

        foreach ($arrayProyectos as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreCompleto = "(" . $infoAnio->nombre . ") " . $item->nombre;
        }

        return view('backend.admin.reportes.salidas.vistasalidas',
            compact('arrayProyectos', 'arrayRecibe'));
    }


    public function reportePdfSalidas($idproyecto, $idrecibe){


        $resultsBloque = array();
        $index = 0;

        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);

        // mostrar errores
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle('Salidas');
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';


        $infoProyecto = TipoProyecto::where('id', $idproyecto)->first();
        $infoAnio = Anios::where('id', $infoProyecto->id_anio)->first();
        $arrayEncargados = ProyectoEncargado::where('id_tipoproyecto', $idproyecto)->get();
        foreach ($arrayEncargados as $item) {
            $infoEncargado = Encargados::where('id', $item->id_encargado)->first();
            $item->nombreEncargado = $infoEncargado->nombre;
        }

        if($idrecibe == '0') { // TODOS
            $arraySalidas = Salidas::where('id_tipoproyecto', $idproyecto)
                ->orderBy('fecha', 'ASC')
                ->get();
        }else {
            $arraySalidas = Salidas::where('id_tipoproyecto', $idproyecto)
                ->where('id_recibe', $idrecibe)
                ->orderBy('fecha', 'ASC')
                ->get();
        }

        foreach ($arraySalidas as $item) {

            array_push($resultsBloque, $item);

            $item->fecha = date("d-m-Y", strtotime($item->fecha));

            $infoRecibe = QuienRecibe::where('id', $item->id_recibe)->first();
            $item->nombreRecibe = $infoRecibe->nombre;

            // obtener detalle
            $listaDetalle = DB::table('salidas_detalle AS ed')
                ->join('entradas_detalle AS end', 'ed.id_entrada_detalle', '=', 'end.id')
                ->select('ed.id_salida', 'ed.id_entrada_detalle', 'ed.cantidad_salida', 'end.id_material')
                ->where('ed.id_salida', $item->id)
                ->get();

            foreach ($listaDetalle as $dd) {
                $infoMaterial = Materiales::where('id', $dd->id_material)->first();

                $infoUniMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();
                $dd->medida = $infoUniMedida->nombre;
                $dd->nombreMaterial = $infoMaterial->nombre;
            }

            $listaDetalle = $listaDetalle->sortBy('nombreMaterial');
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
                </div>
              ";

        $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Año: <strong>$infoAnio->nombre</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Codigo: <strong>$infoProyecto->codigo</strong></p>
                <p style='font-size: 14px; margin: 0; color: #000;'>Proyecto: <strong>$infoProyecto->nombre</strong></p> ";

            if ($arrayEncargados->isNotEmpty()) {
                foreach ($arrayEncargados as $item) {
                    $tabla .= " <p style='font-size: 14px; margin: 0; color: #000;'>Encargado: <strong>$item->nombreEncargado</strong></p>";
                }
            }

        $tabla .="</div>
              ";

        foreach ($arraySalidas as $dd) {

            $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

            if($dd->descripcion != null){
                $tabla .= "<tr>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Fecha</td>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Recibió Materiales</td>
                    <td  width='15%' style='font-weight: bold; font-size: 11px'>Descripción</td>
                    </tr>
                    ";

                $tabla .= "<tr>
                        <td style='font-size: 10px'>$dd->fecha</td>
                        <td style='font-size: 10px'>$dd->nombreRecibe</td>
                        <td style='font-size: 10px'>$dd->descripcion</td>
                    </tr>
                    ";
            }else{
                $tabla .= "<tr>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Fecha</td>
                    <td  width='20%' style='font-weight: bold; font-size: 11px'>Recibió Materiales</td>
                    </tr>
                    ";

                $tabla .= "<tr>
                        <td style='font-size: 10px'>$dd->fecha</td>
                        <td style='font-size: 10px'>$dd->nombreRecibe</td>
                    </tr>
                    ";
            }

            $tabla .= "</tbody></table>";




            $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
                <tbody>";

            $tabla .= "<tr>
                    <td width='25%' style='font-weight: bold; font-size: 11px'>Material</td>
                    <td width='8%' style='font-weight: bold; font-size: 11px'>Medida</td>
                    <td width='8%' style='font-weight: bold; font-size: 11px'>Cantidad</td>
                </tr>";

            foreach ($dd->detalle as $gg) {
                $tabla .= "<tr>
                        <td style='font-size: 10px'>$gg->nombreMaterial</td>
                        <td style='font-size: 10px'>$gg->medida</td>
                        <td style='font-size: 10px'>$gg->cantidad_salida</td>
                    </tr>";
            }

            $tabla .= "</tbody></table>";
        }

        $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";

        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssreportes.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }









    ///*********** REPORTE DE INVENTARIO *******************************

    public function vistaParaReporteInventario(){

        $arrayProyectos = TipoProyecto::where('cerrado', 0)
            ->orderBy('nombre', 'ASC')
            ->get();

        foreach ($arrayProyectos as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreCompleto = "(" . $infoAnio->nombre . ") " . $item->nombre;
        }

        return view('backend.admin.reportes.inventario.vistainventario',
            compact('arrayProyectos'));
    }


    public function reportePdfInventario($idproyecto){

        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);

        // mostrar errores
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle('Inventario');
        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';

        $infoProyecto = TipoProyecto::where('id', $idproyecto)->first();
        $infoAnio = Anios::where('id', $infoProyecto->id_anio)->first();
        $arrayEncargados = ProyectoEncargado::where('id_tipoproyecto', $idproyecto)->get();
        foreach ($arrayEncargados as $item) {
            $infoEncargado = Encargados::where('id', $item->id_encargado)->first();
            $item->nombreEncargado = $infoEncargado->nombre;
        }

        // fecha actual
        $fechaActual = date("d-m-Y", strtotime(Carbon::now('America/El_Salvador')));


        // BUSCAR SOLO DE LAS 'ENTRADAS' DEL PROYECTO
        $pilaArrayIdEntradas = array();
        $arrayEntradas = Entradas::where('id_tipoproyecto', $idproyecto)->get();
        foreach ($arrayEntradas as $fila) {
            array_push($pilaArrayIdEntradas, $fila->id);
        }

        $arrayEntradaDetalle = EntradasDetalle::whereIn('id_entradas', $pilaArrayIdEntradas)
            ->whereColumn('cantidad_entregada', '<', 'cantidad')
            ->get();

        $resultadoAgrupado = [];

        foreach ($arrayEntradaDetalle as $fila) {
            $infoPadre = Entradas::where('id', $fila->id_entradas)->first();

            $infoMaterial = Materiales::where('id', $fila->id_material)->first();
            $fila->nombreMaterial = $infoMaterial->nombre;

            $infoMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();
            $fila->nombreMedida = $infoMedida->nombre;


            // cantidad actual que hay
            $resta = $fila->cantidad - $fila->cantidad_entregada;
            $fila->cantidadActual = $resta;

            $fecha = date("d-m-Y", strtotime($infoPadre->fecha));
            $fila->fechaIngreso = $fecha;

            // Verificar si el id_material ya está en el array resultadoAgrupado
            if (isset($resultadoAgrupado[$fila->id_material])) {
                // Sumar la cantidadActual
                $resultadoAgrupado[$fila->id_material]->cantidadActual += $fila->cantidadActual;
            } else {
                // Si no existe, agregar el nuevo elemento
                $resultadoAgrupado[$fila->id_material] = $fila;
            }
        }

        // Convertir el array agrupado en un array de resultados
        $finalResult = array_values($resultadoAgrupado);


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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>INVENTARIO</h1>
                    <p style='font-size: 16px; margin: 0; color: #000;'>Fecha: $fechaActual</p>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                </div>
              ";

        $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Año: <strong>$infoAnio->nombre</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Codigo: <strong>$infoProyecto->codigo</strong></p>
                <p style='font-size: 14px; margin: 0; color: #000;'>Proyecto: <strong>$infoProyecto->nombre</strong></p> ";

            if ($arrayEncargados->isNotEmpty()) {
                foreach ($arrayEncargados as $item) {
                    $tabla .= " <p style='font-size: 14px; margin: 0; color: #000;'>Encargado: <strong>$item->nombreEncargado</strong></p>";
                }
            }

        $tabla .="</div>
              ";


        $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
                <tbody>";

        $tabla .= "<tr>
                    <td width='5%' style='font-weight: bold; font-size: 12px'>#</td>
                    <td width='25%' style='font-weight: bold; font-size: 12px'>Material</td>
                    <td width='6%' style='font-weight: bold; font-size: 12px'>Medida</td>
                    <td width='9%' style='font-weight: bold; font-size: 12px'>Cantidad Actual</td>
                </tr>";

        $contador = 0;
        foreach ($arrayEntradaDetalle as $item) {
            $contador++;

            $tabla .= "<tr>
                    <td style='font-size: 11px'>$contador</td>
                    <td style='font-size: 11px'>$item->nombreMaterial</td>
                    <td style='font-size: 11px'>$item->nombreMedida</td>
                    <td style='font-size: 11px'>$item->cantidadActual</td>
                </tr>";
        }



        $tabla .= "</tbody></table>";

        $stylesheet = file_get_contents('css/cssreportes.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }






    //*********************** REPORTE FINALIZADOS *********************************

    public function vistaParaReporteFinalizados()
    {
        // SOLO FINALIZADOS
        $arrayProyectos = TipoProyecto::where('cerrado', 1)
            ->orderBy('nombre', 'ASC')
            ->get();

        foreach ($arrayProyectos as $item) {
            $infoAnio = Anios::where('id', $item->id_anio)->first();
            $item->nombreCompleto = "(" . $infoAnio->nombre . ") " . $item->nombre;
        }

        return view('backend.admin.reportes.finalizados.vistafinalizados', compact('arrayProyectos'));
    }



    public function reportePdfFinalizados($idproyecto)
    {
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';

        $infoProyecto = TipoProyecto::where('id', $idproyecto)->first();
        $infoAnio = Anios::where('id', $infoProyecto->id_anio)->first();

        $arrayEncargados = ProyectoEncargado::where('id_tipoproyecto', $idproyecto)->get();
        foreach ($arrayEncargados as $item) {
            $infoEncargado = Encargados::where('id', $item->id_encargado)->first();
            $item->nombreEncargado = $infoEncargado->nombre;
        }

        $mpdf->SetTitle('Finalizado');

        $hayDatos = false;
        $descripcionCierre = "";
        $infoProyectoRecibe = null;
        $infoAnioRecibe = null;

        // BUSCAR QUE HUBO SALIDA DE MATERIALES AL FINALIZAR PROYECTO
        if($infoCierre = CierreProyecto::where('id_entrega_proyecto', $idproyecto)->first()){
            $hayDatos = true;

            // FECHA DE CIERRE DE PROYECTO
            $fechaCierre = date("d-m-Y", strtotime($infoCierre->fecha));
            $descripcionCierre = $infoCierre->descripcion;

            $infoProyectoRecibe = TipoProyecto::where('id', $infoCierre->id_recibe_proyecto)->first();
            $infoAnioRecibe = Anios::where('id', $infoProyectoRecibe->id_anio)->first();

            $infoSalida = Salidas::where('id', $infoCierre->id_recibe_proyecto)->first();
            $arraySalidaDeta = SalidasDetalle::where('id_salida', $infoSalida->id)->get();

            foreach ($arraySalidaDeta as $item){
                $infoEntradaDeta = EntradasDetalle::where('id', $item->id_entrada_detalle)->first();
                $infoMaterial = Materiales::where('id', $infoEntradaDeta->id_material)->first();
                $infoMedida = UnidadMedida::where('id', $infoMaterial->id_medida)->first();

                $item->nombreMaterial = $infoMaterial->nombre;
                $item->nombreUnidad = $infoMedida->nombre;

                // cantidad_entregada
            }


            // HOY SE DEBE BUSCAR A QUIEN SE LE DIO LOS MATERIALES


            $infoEntrada = Entradas::where('id', $infoCierre->id_entrada)->first();
            $arrayEntradaDeta = EntradasDetalle::where('id_entradas', $infoEntrada->id)->get();


            foreach ($arrayEntradaDeta as $item){

                $infoMaterial = Materiales::where('id', $item->id_material)->first();
                $item->nombreMaterial = $infoMaterial->nombre;

                $info = UnidadMedida::where('id', $infoMaterial->id_medida)->first();
                $item->medida = $info->nombre;

                // cantidad
            }

        }else{

            // BUSCAR LA FECHA DE CIERRE
            $fechaCierre = date("d-m-Y", strtotime($infoProyecto->fecha_cierre));
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
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>REPORTE DE PROYECTO FINALIZADO</h1>
                    <p style='font-size: 16px; margin: 0; color: #000;'>Fecha de Cierre: $fechaCierre</p>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                </div>
              ";

        $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Año: <strong>$infoAnio->nombre</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Codigo: <strong>$infoProyecto->codigo</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Proyecto: <strong>$infoProyecto->nombre</strong></p> ";


                    if ($arrayEncargados->isNotEmpty()) {
                        foreach ($arrayEncargados as $item) {
                            $tabla .= " <p style='font-size: 14px; margin: 0; color: #000;'>Encargado: <strong>$item->nombreEncargado</strong></p>";
                        }
                    }

              $tabla .="   <p style='font-size: 14px; margin: 0; color: #000;'>Descripción: <strong>$descripcionCierre</strong></p>
                </div>
              ";


        if($hayDatos){


            $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>SALIDA DE MATERIAL</h1>
                </div>
                </div>
              ";

            $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";
            $tabla .= "<tr>
                    <td  width='3%' style='font-weight: bold; font-size: 11px'>#</td>
                    <td  width='50%' style='font-weight: bold; font-size: 11px'>Material</td>
                    <td  width='5%' style='font-weight: bold; font-size: 11px'>Medida</td>
                    <td  width='5%' style='font-weight: bold; font-size: 11px'>Cantidad</td>
                    </tr>
                    ";

                    $contadorSalida = 0;
                    foreach ($arraySalidaDeta as $item) {
                        $contadorSalida++;
                        $tabla .= "<tr>
                        <td style='font-size: 10px'>$contadorSalida</td>
                        <td style='font-size: 10px'>$item->nombreMaterial</td>
                        <td style='font-size: 10px'>$item->nombreUnidad</td>
                        <td style='font-size: 10px'>$item->cantidad_salida</td>
                    </tr>
                    ";
                    }

            $tabla .= "</tbody></table>";






            $tabla .= "
                    <br>
                <div style='text-align: center; margin-top: 20px;'>
                    <h1 style='font-size: 16px; margin: 0; color: #000;'>ENTRADA DE MATERIAL</h1>
                </div>
                </div>
              ";


            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Año: <strong>$infoAnioRecibe->nombre</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Codigo: <strong>$infoProyectoRecibe->codigo</strong></p>
                 <p style='font-size: 14px; margin: 0; color: #000;'>Proyecto: <strong>$infoProyectoRecibe->nombre</strong></p>
                </div>
              ";



            $tabla .= "<table width='100%' id='tablaFor'>
            <tbody>";
            $tabla .= "<tr>
                    <td  width='3%' style='font-weight: bold; font-size: 11px'>#</td>
                    <td  width='50%' style='font-weight: bold; font-size: 11px'>Material</td>
                    <td  width='5%' style='font-weight: bold; font-size: 11px'>Medida</td>
                    <td  width='5%' style='font-weight: bold; font-size: 11px'>Cantidad</td>
                    </tr>
                    ";

            $contadorEntrada = 0;
            foreach ($arrayEntradaDeta as $item) {
                $contadorEntrada++;
                $tabla .= "<tr>
                        <td style='font-size: 10px'>$contadorEntrada</td>
                        <td style='font-size: 10px'>$item->nombreMaterial</td>
                        <td style='font-size: 10px'>$item->medida</td>
                        <td style='font-size: 10px'>$item->cantidad</td>
                    </tr>
                    ";
            }

            $tabla .= "</tbody></table>";



        }else{
            $tabla .= "
                <div style='text-align: left; margin-top: 20px;'>
                    <p style='font-size: 16px; margin: 0; color: #000;'>No se registro ninguna Salida al Finalizar Proyecto</p>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                </div>
              ";
        }


        $stylesheet = file_get_contents('css/cssreportes.css');
        $mpdf->WriteHTML($stylesheet,1);

        $mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }




















}
