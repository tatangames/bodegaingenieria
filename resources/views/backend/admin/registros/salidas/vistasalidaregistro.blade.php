@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }

    .cursor-pointer:hover {
        cursor: pointer;
        color: #401fd2;
        font-weight: bold;
    }

    *:focus {
        outline: none;
    }
</style>

<div id="divcontenedor" style="display: none">

    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h2>Registro de Salidas</h2>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-10">

                    <div class="card card-gray-dark">
                        <div class="card-header">
                            <h3 class="card-title">Información</h3>
                        </div>

                        <div class="card-body">
                            <div class="card-body col-md-6">
                                <div class="row">
                                    <label>Fecha:</label>
                                    <input style="width: 35%; margin-left: 25px;" type="date" class="form-control" id="fecha">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Asignar Proyecto (No saldran los Finalizados):</label>
                                <select id="select-tipoproyecto" class="form-control" onchange="borrarTabla(this)">
                                    <option value="">Seleccionar Proyecto</option>
                                    @foreach($arrayProyectos as $item)
                                        <option value="{{$item->id}}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Asignar Quien Recibe:</label>
                                <select id="select-quienrecibe" class="form-control">
                                    @foreach($arrayRecibe as $item)
                                        <option value="{{$item->id}}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label># de Salida:</label>
                                <input type="text" class="form-control" id="numero-salida">
                            </div>

                            <div class="form-group">
                                <label>Descripción (Opcional):</label>
                                <input type="text" class="form-control" autocomplete="off" maxlength="800" id="descripcion">
                            </div>

                            <div class="form-group" style="float: right">
                                <br>
                                <button type="button" id="botonaddmaterial" onclick="abrirModal()" class="btn btn-primary btn-sm float-right" style="margin-top:10px; margin-right: 15px;">
                                    <i class="fas fa-search" title="Buscar Material"></i> Buscar Material</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="modalRepuesto" >
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Buscar Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="formulario-repuesto">
                        <div class="card-body">

                            <div class="form-group">
                                <label class="control-label">Material (Regresa: Nombre - Medida)</label>

                                <table class="table" id="matriz-busqueda" data-toggle="table">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <input id="inputBuscador" autocomplete="off" class='form-control' style='width:100%' onkeyup='buscarMaterial(this)' maxlength='300' type='text'>
                                            <div class='droplista' id="midropmenu" style='position: absolute; z-index: 9; width: 75% !important;'></div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- cargara vista de selección -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="tablaRepuesto">

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>



    <!-- LISTADO DE MATERIALES A DESCARGAR DEL BUSCADOR -->



    <div class="modal fade" id="modalCantidad">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Salida de Material</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-material">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="hidden" disabled class="form-control" id="id-entradadetalle" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Material</label>
                                        <input type="text" disabled class="form-control" id="info-material">
                                    </div>

                                    <hr>

                                    <!-- ** TABLA ** -->

                                    <table class="table" id="matrizM" data-toggle="table" style="margin-right: 15px; margin-left: 15px;">
                                        <thead>
                                        <tr>
                                            <th style="width: 5%">Fecha Ingreso</th>
                                            <th style="width: 5%">Cantidad Actual</th>
                                            <th style="width: 5%">Cantidad Salida</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" style="font-weight: bold; background-color: #28a745; color: white !important;" class="button button-rounded button-pill button-small" onclick="agregarAlDetalle()">Agregar</button>
                </div>
            </div>
        </div>
    </div>









    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h2>Detalle de Salida</h2>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-gray-dark">
                <div class="card-header">
                    <h3 class="card-title">Información de Ingreso</h3>
                </div>

                <table class="table" id="matriz" data-toggle="table" style="margin-right: 15px; margin-left: 15px;">
                    <thead>
                    <tr>
                        <th style="width: 3%">#</th>
                        <th style="width: 10%">Material</th>
                        <th style="width: 6%">Salida</th>
                        <th style="width: 5%">Opciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>

            </div>
        </div>
    </section>

    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-success" onclick="preguntaGuardar()">Guardar</button>
    </div>







</div>

@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/bootstrap-input-spinner.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/custom-editors.js') }}" type="text/javascript"></script>



    <script type="text/javascript">
        $(document).ready(function(){
            document.getElementById("divcontenedor").style.display = "block";

            var fecha = new Date();
            document.getElementById('fecha').value = fecha.toJSON().slice(0,10);

            window.seguroBuscador = true;

            $(document).click(function(){
                $(".droplista").hide();
            });

            $(document).ready(function() {
                $('[data-toggle="popover"]').popover({
                    placement: 'top',
                    trigger: 'hover'
                });
            });


            $('#select-tipoproyecto').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Búsqueda no encontrada";
                    }
                },
            });

            document.querySelector('#botonaddmaterial').disabled = true;
        });
    </script>

    <script>

        function abrirModal(){
            document.getElementById('tablaRepuesto').innerHTML = "";
            document.getElementById("formulario-repuesto").reset();
            $('#select-equipo').prop('selectedIndex', 0).change();
            $('#modalRepuesto').modal('show');
        }

        function validateInput(event) {
            const key = event.key;

            // Permitir teclas de navegación y control
            if (["Backspace", "ArrowLeft", "ArrowRight", "Delete", "Tab"].includes(key)) {
                return true;
            }

            // Bloquear la tecla "e", signos negativos y todos excepto números
            if (key === "e" || key === "E" || key === "-" || isNaN(Number(key))) {
                return false;
            }

            return true;
        }

        function buscarMaterial(e){

            var tipoproyecto = document.getElementById('select-tipoproyecto').value;

            if(tipoproyecto == ''){
                toastr.error('Seleccionar un Proyecto');
                return;
            }

            // seguro para evitar errores de busqueda continua
            if(seguroBuscador){
                seguroBuscador = false;

                var row = $(e).closest('tr');
                let texto = e.value;


                axios.post(url+'/buscar/material/porproyecto', {
                    'query' : texto,
                    'tipoproyecto' : tipoproyecto
                })
                    .then((response) => {

                        seguroBuscador = true;
                        $(row).each(function (index, element) {
                            $(this).find(".droplista").fadeIn();
                            $(this).find(".droplista").html(response.data);
                        });
                    })
                    .catch((error) => {
                        seguroBuscador = true;
                    });
            }
        }


        function modificarValor(edrop) {

            // Viene entradas_detalle
            var tipoproyecto = document.getElementById('select-tipoproyecto').value;

            if(tipoproyecto === ''){
                return
            }

            openLoading()

            var formData = new FormData();
            formData.append('id', edrop.id); // entradas_detalle
            formData.append('idproyecto', tipoproyecto);
            $("#matrizM tbody tr").remove();

            axios.post(url+'/buscar/material/proyecto/disponibilidad', formData, {
            })
                .then((response) => {
                    closeLoading();

                    console.log(response)

                    if(response.data.success === 1){
                        $('#id-entradadetalle').val(edrop.id);
                        $('#info-material').val(response.data.nombreMaterial);

                        $.each(response.data.arrayIngreso, function( key, val ){

                            var nFilas = $('#matrizM >tbody >tr').length;
                            nFilas += 1;

                            var markup = "<tr>" +

                                "<td>" +
                                "<input disabled value='" + val.fechaIngreso + "' class='form-control' type='text'>" +
                                "</td>" +

                                "<td>" +
                                "<input name='arrayCantidadActual[]' disabled data-cantidadActualFila='" + val.cantidadActual + "'  value='" + val.cantidadActual + "' class='form-control' type='number'>" +
                                "</td>" +

                                "<td>" +
                                "<input " +
                                "class='form-control' data-idfilaentradadetalle='" + val.id + "' name='arrayCantidadSalida[]' min='0' max='" + val.cantidad + "' " +
                                "type='number' " +
                                "onkeydown=\"return validateInput(event);\" " +
                                "oninput=\"validateCantidadSalida(this, " + val.cantidad + ");\">" +
                                "</td>" +

                                "</tr>";

                            $("#matrizM tbody").append(markup);
                        });
                        $('#modalCantidad').modal('show');
                    }
                    else {
                        toastr.error('Error');
                    }
                })
                .catch((error) => {
                    toastr.error('Error');
                    closeLoading();
                });
        }


        // AGREGAR AL DETALLE
        function agregarAlDetalle(){



            // id entrada_detalle
            var arrayIdEntradaDetalle = $("input[name='arrayCantidadSalida[]']").map(function(){return $(this).attr("data-idfilaentradadetalle");}).get();
            // cantidad salida
            var arrayCantidadSalida = $("input[name='arrayCantidadSalida[]']").map(function(){return $(this).val();}).get();
            // cantidad actual de cada fila
            var arrayCantidadActual = $("input[name='arrayCantidadActual[]']").map(function(){return $(this).attr("data-cantidadActualFila");}).get();


            colorBlancoTabla()
            var habraSalida = true;

            // recorrer y verificar
            for(var a = 0; a < arrayCantidadSalida.length; a++){

                let filaCantidad = arrayCantidadSalida[a];
                let infoFilaCantidadActual = arrayCantidadActual[a];

                if(filaCantidad !== ''){
                    if(filaCantidad <= 0){
                        colorRojoTabla(a);
                        alertaMensaje('info', 'Error', 'En la Fila #' + (a+1) + " No se permite ingreso de Cero, por favor borrarlo");
                        return
                    }
                    habraSalida = false;
                }

                // VERIFICAR QUE NO SUPERE CANTIDAD SALIDA AL CANTIDAD ACTUAL DE CADA FILA DE LA TABLA
                if(filaCantidad > Number(infoFilaCantidadActual)){
                    colorRojoTabla(a);
                    alertaMensaje('info', 'Error', 'En la Fila #' + (a+1) + " La cantidad de Salida supera a la Cantidad Actual");
                    return
                }
            }

            if(habraSalida){
                toastr.error('Registrar mínimo 1 salida');
                return
            }


            // RECORRER PARA AGREGAR CADA UNA AL DETALLE

            // nombre TXT del material
            var nombreTexto = document.getElementById('info-material').value;
            var nFilas = $('#matriz >tbody >tr').length;

            for(var z = 0; z < arrayCantidadSalida.length; z++){
                nFilas += 1;
                let infoFilaIdEntradaDetalle = arrayIdEntradaDetalle[z];
                let filaCantidad = arrayCantidadSalida[z];

                var markup = "<tr>" +

                    "<td>" +
                    "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                    "</td>" +

                    "<td>" +
                    "<input name='idmaterialArray[]' type='hidden' data-idmaterialArray='" + infoFilaIdEntradaDetalle + "'>" +
                    "<input disabled value='" + nombreTexto + "' class='form-control' type='text'>" +
                    "</td>" +

                    "<td>" +
                    "<input name='salidaArray[]' disabled data-cantidadSalida='" + filaCantidad + "'" +
                    " value='" + filaCantidad + "' class='form-control' type='text'>" +
                    "</td>" +

                    "<td>" +
                    "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
                    "</td>" +

                    "</tr>";

                $("#matriz tbody").append(markup);
            }


            $('#modalCantidad').modal('hide');
            document.getElementById('inputBuscador').value = '';

            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            })
        }

        function preguntaGuardar(){
            colorBlancoTabla();

            Swal.fire({
                title: 'Guardar Salida?',
                text: "",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    guardarSalida();
                }
            })
        }




        function guardarSalida(){

            // fecha
            var fecha = document.getElementById('fecha').value;
            // idproyecto
            var idProyecto = document.getElementById('select-tipoproyecto').value;
            // quien recibe
            var idRecibe = document.getElementById('select-quienrecibe').value;
            // # de salida
            var numSalida = document.getElementById('numero-salida').value;
            // descripcion
            var descripc = document.getElementById('descripcion').value;

            if(fecha === ''){
                toastr.error('Fecha es requerida');
            }

            if(idProyecto === ''){
                toastr.error('ID proyecto es requerido');
                return
            }


            var reglaNumeroEntero = /^[0-9]\d*$/;


            var nRegistro = $('#matriz > tbody >tr').length;
            let formData = new FormData();

            if (nRegistro <= 0){
                toastr.error('Registro Salida son requeridos');
                return;
            }

            var idEntradaDetalle = $("input[name='idmaterialArray[]']").map(function(){return $(this).attr("data-idmaterialArray");}).get();
            var salidaCantidad = $("input[name='idmaterialArray[]']").map(function(){return $(this).attr("data-cantidadSalida");}).get();


            //*******************


            for(var p = 0; p < salidaDetalle.length; p++){


            }

            openLoading();

            formData.append('fecha', fecha);
            formData.append('descripcion', descripc);
            formData.append('idproyecto', idProyecto);

            axios.post(url+'/salida/guardar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    // CANTIDAD NO ALCANZA PARA RETIRAR
                    if(response.data.success === 1){

                        let fila = response.data.fila;
                        let cantidad = response.data.cantidadactual;
                        let cantidadsalida = response.data.cantidadrestar;
                        colorRojoTabla(fila);
                        Swal.fire({
                            title: 'Cantidad no Disponible',
                            text: "Fila #" + (fila+1) + ", el repuesto cuenta con: " + cantidad + " unidades disponible, y se quiere retirar " + cantidadsalida + " Unidades",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }
                    else if(response.data.success === 2){
                        // MATERIAL NO ENCONTRADO
                        let fila = response.data.fila;
                        colorRojoTabla(fila);
                        Swal.fire({
                            title: 'Repuesto no Encontrado',
                            text: "Fila #" + (fila+1) + ", el repuesto no se Encontro, por favor borrar Fila y volver a ingresarlo",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    } else if(response.data.success === 3){

                        // REGISTRADO CORRECTAMENTE
                        toastr.success('Salida Registrada');
                        limpiar();
                    }
                    else{
                        toastr.error('Error al guardar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al guardar');
                    closeLoading();
                });
        }





        function divColorRojo(pos){
            var divs = document.getElementsByClassName('arraycolor');
            $(divs[pos]).css("background-color", "red");
        }

        function borrarFila(elemento){
            var tabla = elemento.parentNode.parentNode;
            tabla.parentNode.removeChild(tabla);
            setearFila()
        }

        // cambiar # de fila cada vez que se borra la fila de
        // tabla nuevo material
        function setearFila(){

            var table = document.getElementById('matriz');
            var conteo = 0;
            for (var r = 1, n = table.rows.length; r < n; r++) {
                conteo +=1;
                var element = table.rows[r].cells[0].children[0];
                document.getElementById(element.id).innerHTML = ""+conteo;
            }
        }

        function colorRojoTabla(index){
            $("#matriz tr:eq("+(index+1)+")").css('background', '#F1948A');
        }

        function colorBlancoTabla(){
            $("#matriz tbody tr").css('background', 'white');
        }

        function limpiar(){
            document.getElementById('descripcion').value = '';
            document.getElementById('numero-salida').value = '';

            $("#matriz tbody tr").remove();
        }


        function borrarTabla(e){
            let id = $(e).val();

            if(id == ''){
                $("#matriz tbody tr").remove();
                document.querySelector('#botonaddmaterial').disabled = true;
            }else{
                document.querySelector('#botonaddmaterial').disabled = false;
            }
        }



    </script>


@endsection
