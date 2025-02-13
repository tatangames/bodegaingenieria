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
                                                        <input id="inputBuscador" data-idproducto='0' autocomplete="off" class='form-control' style='width:100%' onkeyup='buscarMaterial(this)' maxlength='300' type='text'>
                                                        <div class='droplista' id="midropmenu" style='position: absolute; z-index: 9; width: 75% !important;'></div>
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
                                                <input type="text" autocomplete="off" class="form-control" id="cantidad" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="form-group col-md-2" style="margin-top: 5px">
                                            <label class="control-label" style="color: #686868">Precio: </label>
                                            <div>
                                                <input type="number" min="0" max="1000000" autocomplete="off" class="form-control" id="precio-producto" placeholder="0.00">
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
                            <th style="width: 6%">Precio</th>
                            <th style="width: 5%">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>


                    <table class="table" id="matriz-totales" data-toggle="table" style="float: right">
                        <thead>
                        <tr style="float: right">
                            <th>Precio Total</th>
                        </tr>
                        </thead>
                        <tbody style="float: right">
                        <td style="width: 125px"> <label type="text" class="form-control" id="precioTotal" >$0.00</label></td>

                        </tbody>
                    </table>

                </div>
            </div>
        </section>

        <div class="modal-footer justify-content-end" style="margin-top: 25px;">
            <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Listado de Productos</button>
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
                    $(e).attr('data-idproducto', 0);
                }

                axios.post(url+'/bodega/buscar/producto', {
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
            $(txtContenedorGlobal).attr('data-idproducto', edrop.id);

            document.activeElement.blur();
        }


        function agregarFila(){

            var precioProducto = document.getElementById('precio-producto').value;
            var cantidad = document.getElementById('cantidad').value;
            var inputBuscador = document.querySelector('#inputBuscador');

            var reglaNumeroEntero = /^[0-9]\d*$/;
            var reglaNumeroDiesDecimal = /^([0-9]+\.?[0-9]{0,10})$/;


            if(inputBuscador.dataset.idproducto == 0){
                toastr.error("Producto es requerido");
                return;
            }

            //**************

            if(cantidad === ''){
                toastr.error('Cantidad es requerido');
                return;
            }

            if(!cantidad.match(reglaNumeroEntero)) {
                toastr.error('Cantidad es requerido');
                return;
            }

            if(cantidad < 0){
                toastr.error('Cantidad Mínima no debe tener negativos');
                return;
            }

            if(cantidad > 9000000){
                toastr.error('Cantidad máximo debe ser 9 millones');
                return;
            }

            //**************

            var nomProducto = document.getElementById('inputBuscador').value;


            //*************

            if(precioProducto === ''){
                toastr.error('Precio Producto es requerido');
                return;
            }

            if(!precioProducto.match(reglaNumeroDiesDecimal)) {
                toastr.error('Precio Producto debe ser número Decimal (10 decimales)');
                return;
            }

            if(precioProducto < 0){
                toastr.error('Precio Producto no debe ser negativo');
                return;
            }

            if(precioProducto > 9000000){
                toastr.error('Precio Producto debe ser máximo 9 millones');
                return;
            }



            //**************

            // Crear un objeto Date a partir del valor del input

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayNombre[]' disabled data-idproducto='" + inputBuscador.dataset.idproducto + "' value='" + nomProducto + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='arrayCantidad[]' disabled value='" + cantidad + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='arrayPrecio[]' data-precio='" + precioProducto + "' disabled value='$" + precioProducto + "' class='form-control' type='text'>" +
                "</td>" +


                "<td>" +
                "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
                "</td>" +

                "</tr>";

            $("#matriz tbody").append(markup);


            // CALCULAR TODAS LAS FILAS
            calcularFilas();


            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            })

            $(txtContenedorGlobal).attr('data-idproducto', '0');

            document.getElementById('cantidad').value = '';
            document.getElementById('precio-producto').value = '';
            document.getElementById('inputBuscador').value = '';
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

            calcularFilas();
        }

        function calcularFilas(){

            var cantidad = $("input[name='arrayCantidad[]']").map(function(){return $(this).val();}).get();
            var precio = $("input[name='arrayPrecio[]']").map(function(){return $(this).attr("data-precio");}).get();
            var precioTotal = 0;

            for(var a = 0; a < cantidad.length; a++){

                let infoCantidad = cantidad[a];
                let infoPrecio = precio[a];

                let multiplicado = infoCantidad * infoPrecio;

                precioTotal += multiplicado;
            }

            let precioFormat = '$' + Number(precioTotal).toFixed(2);

            document.getElementById('precioTotal').innerHTML = precioFormat;
        }


        function preguntarGuardar(){

            Swal.fire({
                title: '¿Registrar Productos?',
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

            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro <= 0){
                toastr.error('Productos a Ingresar son requeridos');
                return;
            }

            var arrayIdProducto = $("input[name='arrayNombre[]']").map(function(){return $(this).attr("data-idproducto");}).get();
            var arrayCantidad = $("input[name='arrayCantidad[]']").map(function(){return $(this).val();}).get();
            var arrayPrecio = $("input[name='arrayPrecio[]']").map(function(){return $(this).attr("data-precio");}).get();

            var reglaNumeroEntero = /^[0-9]\d*$/;
            var reglaNumeroDiesDecimal = /^([0-9]+\.?[0-9]{0,10})$/;


            // VALIDACIONES DE CADA FILA, RECORRER 1 ELEMENTO YA QUE TODOS TIENEN LA MISMA CANTIDAD DE FILAS

            colorBlancoTabla();

            for(var a = 0; a < arrayIdProducto.length; a++){

                let idProducto = arrayIdProducto[a];
                let cantidadProducto = arrayCantidad[a];
                let precioProducto = arrayPrecio[a];

                // identifica si el 0 es tipo number o texto
                if(idProducto == 0){
                    colorRojoTabla(a);
                    alertaMensaje('info', 'No encontrado', 'En la Fila #' + (a+1) + " El Producto no se encuentra. Por favor borrar la Fila y buscar de nuevo el Producto");
                    return;
                }

                // **** VALIDAR CANTIDAD DE PRODUCTO

                if (cantidadProducto === '') {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad de producto es requerida. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (!cantidadProducto.match(reglaNumeroEntero)) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad debe ser entero y no negativo. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (cantidadProducto < 0) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad no debe ser negativo. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (cantidadProducto > 9000000) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad máximo 9 millones. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }



                // **** VALIDAR PRECIO DE PRODUCTO

                if (precioProducto === '') {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Precio de producto es requerida. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (!precioProducto.match(reglaNumeroDiesDecimal)) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Precio debe ser decimal (10 decimales) y no negativo. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (precioProducto < 0) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Precio no debe ser negativo. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

                if (precioProducto > 9000000) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Precio máximo 9 millones. Por favor borrar la Fila y buscar de nuevo el Producto');
                    return;
                }

            }

            openLoading();

            let formData = new FormData();

            let identrada = {{ $id }};

            const contenedorArray = [];

            for(var i = 0; i < arrayIdProducto.length; i++){

                let infoIdProducto = arrayIdProducto[i];
                let infoCantidad = arrayCantidad[i];
                let infoPrecio = arrayPrecio[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdProducto, infoCantidad, infoPrecio });
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('identrada', identrada);

            axios.post(url+'/bodega/registrar/productosextras', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
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

            document.getElementById('inputBuscador').value = '';
            document.getElementById('cantidad').value = '';
            document.getElementById('precio-producto').value = '';

            document.getElementById('precioTotal').innerHTML = "$0.00";

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
