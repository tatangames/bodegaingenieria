@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
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

        <section class="content" style="margin-top: 20px">
            <div class="container-fluid">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">INGRESO EXTRA</h3>
                    </div>
                    <div class="card-body">

                        <div class="border-box" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                            <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="form-group col-md-5" style="margin-top: 5px">
                                            <h3 class="card-title" style="color: #005eab; font-weight: bold">Buscar Producto</h3>
                                            <div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </section>

                            <section class="content" style="margin-top: 15px">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <table class="table" id="matriz-busqueda" data-toggle="table">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <input id="repuesto" data-info='0' autocomplete="off" class='form-control' style='width:100%' onkeyup='buscarMaterial(this)' maxlength='400'  type='text'>
                                                        <div class='droplista' style='position: absolute; z-index: 9; width: 75% !important;'></div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="content">
                                <div class="container-fluid">

                                    <div class="row">

                                        <div class="form-group col-md-2" style="margin-top: 5px">
                                            <label class="control-label" style="color: #686868">Cantidad: </label>
                                            <div>
                                                <input id="cantidad" min="0" max="1000000"  class='form-control' autocomplete="off" type="number" placeholder="0">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </section>

                        </div>

                        <br>

                        <section class="content">
                            <div class="container-fluid">

                                <div style="margin-right: 30px">
                                    <button type="button" style="float: right" class="btn btn-success" onclick="agregarFila();">Agregar a Tabla</button>

                                </div>

                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>



        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h2>Detalle de Ingreso</h2>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información de Ingreso</h3>
                    </div>

                    <table class="table" id="matriz" data-toggle="table" style="margin-right: 15px; margin-left: 15px;">
                        <thead>
                        <tr>
                            <th style="width: 3%">#</th>
                            <th style="width: 10%">Producto</th>
                            <th style="width: 6%">Cantidad</th>
                            <th style="width: 5%">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>
            </div>
        </section>

        <div class="modal-footer justify-content-end" style="margin-top: 25px;">
            <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Listado</button>
        </div>

</div>


@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            window.seguroBuscador = true;
            window.txtContenedorGlobal = this;

            $(document).click(function(){
                $(".droplista").hide();
            });

            $(document).ready(function() {
                $('[data-toggle="popover"]').popover({
                    placement: 'top',
                    trigger: 'hover'
                });
            });

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function buscarMaterial(e){

            // seguro para evitar errores de busqueda continua
            if(seguroBuscador){
                seguroBuscador = false;

                var row = $(e).closest('tr');
                txtContenedorGlobal = e;

                let texto = e.value;

                if(texto === ''){
                    // si se limpia el input, setear el atributo id
                    $(e).attr('data-info', 0);
                }

                axios.post(url+'/buscar/material/global', {
                    'query' : texto
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


        function modificarValor(edrop){

            // obtener texto del li
            let texto = $(edrop).text();
            // setear el input de la descripcion
            $(txtContenedorGlobal).val(texto);

            // agregar el id al atributo del input descripcion
            $(txtContenedorGlobal).attr('data-info', edrop.id);
        }


        function agregarFila(){

            var repuesto = document.querySelector('#repuesto');
            var nomRepuesto = document.getElementById('repuesto').value;
            var cantidad = document.getElementById('cantidad').value;

            if(repuesto.dataset.info == 0){
                toastr.error("Material es requerido");
                return;
            }

            var reglaNumeroEntero = /^[0-9]\d*$/;

            //*************

            if(cantidad === ''){
                toastr.error('Cantidad es requerida');
                return;
            }

            if(!cantidad.match(reglaNumeroEntero)) {
                toastr.error('Cantidad debe ser número entero y no Negativo');
                return;
            }

            if(cantidad <= 0){
                toastr.error('Cantidad no debe ser negativo o cero');
                return;
            }

            if(cantidad > 1000000){
                toastr.error('Cantidad máximo 1 millón');
                return;
            }

            //**************

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='descripcionArray[]' disabled data-info='" + repuesto.dataset.info + "' value='" + nomRepuesto + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='cantidadArray[]' disabled value='" + cantidad + "' class='form-control' type='number'>" +
                "</td>" +

                "<td>" +
                "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
                "</td>" +

                "</tr>";

            $("#matriz tbody").append(markup);

            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            })

            $(txtContenedorGlobal).attr('data-info', '0');
            document.getElementById("formulario-repuesto").reset();
        }

        function borrarFila(elemento){
            var tabla = elemento.parentNode.parentNode;
            tabla.parentNode.removeChild(tabla);
            setearFila();
        }

        function setearFila(){

            var table = document.getElementById('matriz');
            var conteo = 0;
            for (var r = 1, n = table.rows.length; r < n; r++) {
                conteo +=1;
                var element = table.rows[r].cells[0].children[0];
                document.getElementById(element.id).innerHTML = ""+conteo;
            }
        }

        function preguntarGuardar(){

            Swal.fire({
                title: '¿Registrar?',
                text: '',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                allowOutsideClick: false,
                confirmButtonText: 'SI',
                cancelButtonText: 'NO'
            }).then((result) => {
                if (result.isConfirmed) {
                    registrarProductos();
                }
            })
        }

        function registrarProductos(){

            var idEntrada = {{ $id }};

            var reglaNumeroEntero = /^[0-9]\d*$/;
            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro <= 0){
                toastr.error('Registro Entrada son requeridos');
                return;
            }

            var descripcionAtributo = $("input[name='descripcionArray[]']").map(function(){return $(this).attr("data-info");}).get();
            var cantidad = $("input[name='cantidadArray[]']").map(function(){return $(this).val();}).get();

            for(var a = 0; a < cantidad.length; a++){

                let detalle = descripcionAtributo[a];
                let datoCantidad = cantidad[a];

                // identifica si el 0 es tipo number o texto
                if(detalle == 0){
                    colorRojoTabla(a);
                    alertaMensaje('info', 'No encontrado', 'En la Fila #' + (a+1) + " El material no se encuentra. Por favor buscar de nuevo el Material");
                    return;
                }

                if (datoCantidad === '') {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad es requerida');
                    return;
                }

                if (!datoCantidad.match(reglaNumeroEntero)) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad debe ser Entero y no negativo');
                    return;
                }

                if (datoCantidad <= 0) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad no debe ser negativo');
                    return;
                }

                // Máximo 1 millón
                if (datoCantidad > 1000000) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad máximo 1 millón');
                    return;
                }
            }


            //*******************

            let formData = new FormData();
            const contenedorArray = [];

            // como tienen la misma cantidad de filas, podemos recorrer
            // todas las filas de una vez
            for(var p = 0; p < cantidad.length; p++){

                let infoIdProducto = descripcionAtributo[p];
                let infoCantidad = cantidad[p];

                contenedorArray.push({ infoIdProducto, infoCantidad });
            }

            openLoading();

            formData.append('identrada', idEntrada);
            formData.append('contenedorArray', JSON.stringify(contenedorArray));

            axios.post(url+'/bodega/registrar/productosextras', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.error('El proyecto ya esta Finalizado');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Registrado correctamente');
                        limpiar();
                    }
                    else{
                        toastr.error('error al guardar');
                    }
                })
                .catch((error) => {
                    toastr.error('error al guardar');
                    closeLoading();
                });
        }

        function limpiar(){
            document.getElementById('repuesto').value = '';
            $("#matriz tbody tr").remove();
        }

        function colorRojoTabla(index){
            $("#matriz tr:eq("+(index+1)+")").css('background', '#F1948A');
        }

        function colorBlancoTabla(){
            $("#matriz tbody tr").css('background', 'white');
        }

    </script>

@endsection
