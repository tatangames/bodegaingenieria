@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }
    .select2-container{
        height: 30px !important;
    }


</style>

<div id="divcontenedor" style="display: none">

    <section class="content-header">
        <div class="row">
            <h1 style="margin-left: 5px">Reingreso de Herramienta</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado Herramientas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="tablaDatatable">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalReingreso">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reingreso de Herramienta</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-reingreso">
                        <div class="card-body">

                            <div class="form-group">
                                <input type="hidden" id="id-reingreso">
                            </div>


                            <div class="form-group">
                                <label>Fecha Salio Herramienta:</label>
                                <input type="text" disabled class="form-control" autocomplete="off" id="fecha-salio">
                            </div>



                            <div class="form-group">
                                <label>Herramienta:</label>
                                <input type="text" disabled class="form-control" autocomplete="off" id="nombre-reingreso">
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cantidad fuera de Bodega:</label>
                                    <input type="number" disabled class="form-control" autocomplete="off" id="cantidad-inventario-reingreso">
                                </div>
                            </div>


                            <hr>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Descripción (Opcional):</label>
                                    <input type="text" class="form-control" autocomplete="off" id="descripcion-reingreso" maxlength="800">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cantidad a Reingresar a Bodega:</label>
                                    <input type="number" min="1" class="form-control" autocomplete="off" id="cantidad-reingresar" maxlength="10">
                                </div>
                            </div>



                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="verificarReingreso()">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalDescartar">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Descartar Herramienta</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-descartar">
                        <div class="card-body">

                            <div class="form-group">
                                <input type="hidden" id="id-descartar">
                            </div>

                            <div class="form-group">
                                <label>Herramienta:</label>
                                <input type="text" disabled class="form-control" autocomplete="off" id="nombre-descartar">
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cantidad fuera de Bodega:</label>
                                    <input type="number" disabled class="form-control" autocomplete="off" id="cantidad-inventario-descartar">
                                </div>
                            </div>


                            <hr>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <input type="text" class="form-control" autocomplete="off" id="descripcion-descartar" maxlength="800">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cantidad a Descartar:</label>
                                    <input type="number" min="1" class="form-control" autocomplete="off" id="cantidad-descartar" maxlength="10">
                                </div>
                            </div>



                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="verificarDescartar()">Guardar</button>
                </div>
            </div>
        </div>
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

    <script type="text/javascript">
        $(document).ready(function(){
            var ruta = "{{ URL::to('/admin/inventario/reingreso/herramientas/tabla') }}";
            $('#tablaDatatable').load(ruta);

            $('#select-unidad-nuevo').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Busqueda no encontrada";
                    }
                },
            });

            $('#select-unidad-editar').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Busqueda no encontrada";
                    }
                },
            });

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ url('/admin/inventario/reingreso/herramientas/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // información para ver la cantidad que debe reingresar
        function modalReingreso(id){

            openLoading();
            document.getElementById("formulario-reingreso").reset();

            axios.post(url+'/reingreso/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalReingreso').modal({backdrop: 'static', keyboard: false})

                        $('#id-reingreso').val(id);
                        $('#nombre-reingreso').val(response.data.lista2.nombre);
                        $('#cantidad-inventario-reingreso').val(response.data.lista.cantidad);
                        $('#fecha-salio').val(response.data.fechasalio);


                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }


        function verificarReingreso(){

            var id = document.getElementById('id-reingreso').value;
            var cantidadRe = document.getElementById('cantidad-reingresar').value;
            var descripcion = document.getElementById('descripcion-reingreso').value;

            var reglaNumeroEntero = /^[0-9]\d*$/;

            if(cantidadRe === ''){
                toastr.error('Cantidad de Reingreso es requerido');
                return;
            }

            if(!cantidadRe.match(reglaNumeroEntero)) {
                toastr.error('Cantidad de Reingreso debe ser número Entero y no Negativo');
                return;
            }

            if(cantidadRe <= 0){
                toastr.error('Cantidad no Reingreso no debe ser negativo o cero');
                return;
            }

            if(cantidadRe > 9000000){
                toastr.error('Cantidad de reingreso no debe ser mayor 9 millones');
                return;
            }


            openLoading();

            var formData = new FormData();
            formData.append('id', id);
            formData.append('cantidad', cantidadRe);
            formData.append('descripcion', descripcion);

            axios.post(url+'/reingreso/cantidad', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        Swal.fire({
                            title: 'Error',
                            text: "La cantidad a Reingresar es Mayor a la disponible",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }

                    else if(response.data.success === 2){

                        toastr.success('Registrado correctamente');
                        $('#modalReingreso').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
                    closeLoading();
                });
        }




        //******************************************************


        function modalDescartar(id){

            openLoading();
            document.getElementById("formulario-descartar").reset();

            axios.post(url+'/reingreso/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalDescartar').modal({backdrop: 'static', keyboard: false})

                        $('#id-descartar').val(id);
                        $('#nombre-descartar').val(response.data.lista2.nombre);
                        $('#cantidad-inventario-descartar').val(response.data.lista.cantidad);



                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }



        function verificarDescartar(){

            var id = document.getElementById('id-descartar').value;
            var cantidadRe = document.getElementById('cantidad-descartar').value;
            var descripcion = document.getElementById('descripcion-descartar').value;

            var reglaNumeroEntero = /^[0-9]\d*$/;

            if(cantidadRe === ''){
                toastr.error('Cantidad a Descartar es requerido');
                return;
            }

            if(!cantidadRe.match(reglaNumeroEntero)) {
                toastr.error('Cantidad a Descartar debe ser número Entero y no Negativo');
                return;
            }

            if(cantidadRe <= 0){
                toastr.error('Cantidad a Descartar no debe ser negativo o cero');
                return;
            }

            if(cantidadRe > 9000000){
                toastr.error('Cantidad a Descartar no debe ser mayor 9 millones');
                return;
            }


            if(descripcion === ''){
                toastr.error('Descripción es requerido');
                return;
            }


            openLoading();

            var formData = new FormData();
            formData.append('id', id);
            formData.append('cantidad', cantidadRe);
            formData.append('descripcion', descripcion);

            axios.post(url+'/descartar/cantidad', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        Swal.fire({
                            title: 'Error',
                            text: "La cantidad a Descartar es Mayor a la disponible",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }

                    else if(response.data.success === 2){

                        toastr.success('Herramienta Descartada Correctamente');
                        $('#modalDescartar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
                    closeLoading();
                });

        }







    </script>


@endsection
