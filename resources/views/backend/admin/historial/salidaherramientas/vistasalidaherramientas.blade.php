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
            <h1 style="margin-left: 5px">Historial de Salidas de Herramientas</h1>
        </div>

    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-gray-dark">
                <div class="card-header">
                    <h3 class="card-title">Listado</h3>
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



    <!-- modal editar -->
    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-editar">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="hidden" id="id-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <input type="date" class="form-control" id="fecha-editar">
                                    </div>



                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" autocomplete="off" maxlength="800" class="form-control" id="descripcion-editar" placeholder="Descripción">
                                    </div>

                                    <div class="form-group">
                                        <label># de Salida</label>
                                        <input type="text" autocomplete="off" maxlength="100" class="form-control" id="numero-salida" placeholder="# de Salida">
                                    </div>

                                    <div class="form-group">
                                        <label style="color:#191818">Quien Recibe</label>
                                        <br>
                                        <div>
                                            <select class="form-control" id="select-quienrecibe">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label style="color:#191818">Quien Entrega</label>
                                        <br>
                                        <div>
                                            <select class="form-control" id="select-quienentrega">
                                            </select>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editar()">Actualizar</button>
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
        $(document).ready(function() {
            var ruta = "{{ URL::to('/admin/historial/salida/herramienta/tabla') }}";
            $('#tablaDatatable').load(ruta);


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
            var ruta = "{{ url('/admin/historial/salida/herramienta/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();

            $('#modalAgregar').modal({backdrop: 'static', keyboard: false})
        }



        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/historial/salida/herramienta/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        $('#id-editar').val(id);

                        $('#fecha-editar').val(response.data.info.fecha);
                        $('#descripcion-editar').val(response.data.info.descripcion);
                        $('#numero-salida').val(response.data.info.num_salida);


                        document.getElementById("select-quienentrega").options.length = 0;
                        document.getElementById("select-quienrecibe").options.length = 0;


                        $.each(response.data.arrayrecibe, function( key, val ){
                            if(response.data.info.quien_recibe === val.id){
                                $('#select-quienrecibe').append('<option value="' +val.id +'" selected="selected">'+val.nombre+'</option>');
                            }else{
                                $('#select-quienrecibe').append('<option value="' +val.id +'">'+val.nombre+'</option>');
                            }
                        });

                        $.each(response.data.arrayentrega, function( key, val ){
                            if(response.data.info.quien_entrega === val.id){
                                $('#select-quienentrega').append('<option value="' +val.id +'" selected="selected">'+val.nombre+'</option>');
                            }else{
                                $('#select-quienentrega').append('<option value="' +val.id +'">'+val.nombre+'</option>');
                            }
                        });


                        $('#modalEditar').modal('show');


                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }


        function editar(){

            var id = document.getElementById('id-editar').value;
            var fecha = document.getElementById('fecha-editar').value;
            var descripcion = document.getElementById('descripcion-editar').value;
            var recibo = document.getElementById('numero-salida').value;

            var idrecibe = document.getElementById('select-quienrecibe').value;
            var identrega = document.getElementById('select-quienentrega').value;

            if(fecha === ''){
                toastr.error('Fecha es requerido');
                return;
            }

            if(idrecibe === ''){
                toastr.error('Quien Recibe es requerido');
                return;
            }

            if(identrega === ''){
                toastr.error('Quien Entrega es requerido');
                return;
            }


            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('fecha', fecha);
            formData.append('descripcion', descripcion);
            formData.append('recibo', recibo);
            formData.append('identrega', identrega);
            formData.append('idrecibe', idrecibe);


            axios.post(url+'/historial/salida/herramienta/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Actualizado correctamente');
                        $('#modalEditar').modal('hide');
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



        function detalleHistorial(id){
            window.location.href="{{ url('/admin/historial/salida/herramientas/detalle') }}/" + id;
        }



    </script>


@endsection
