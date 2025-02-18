@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />

@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }
</style>

<div id="divcontenedor" style="display: none">


    <section class="content" style="margin-top: 15px">
        <div class="container-fluid">
            <div class="card card-gray-dark">
                <div class="card-header">
                    <h3 class="card-title">Listado de Proyectos (No Finalizados)</h3>
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


    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Finalizar Proyectos</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="formulario-editar">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <!-- ID PARA PROYECTOS QUE PASARA LAS COSAS -->
                                    <div class="form-group">
                                        <input type="hidden" id="id-editar">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>Fecha</label>
                                        <input type="date" class="form-control" id="fecha-cierre">
                                    </div>

                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" class="form-control" maxlength="800" id="descripcion-cierre">
                                    </div>

                                    <div class="form-group">
                                        <label>Proyecto que Finalizara</label>
                                        <input type="text" class="form-control" id="textoFin" disabled>
                                    </div>


                                    <div class="form-group">
                                        <label>Proyecto que recibira los Materiales</label>
                                        <br>
                                        <select class="form-control" id="select-proyectos">
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Documento Acta de Cierre (opcional)</label>
                                        <input type="file" id="documento" class="form-control" accept="image/jpeg, image/jpg, image/png, .pdf"/>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="infoFinalizar()">Guardar</button>
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

    <script type="text/javascript">
        $(document).ready(function(){

            var ruta = "{{ URL::to('/admin/transferecia/proyecto/tabla') }}";
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function infoMateriales(id){
            window.location.href="{{ url('/admin/transferecia/materiales/index') }}/" + id;
        }

        function infoProyectosFinalizados(id){

            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/transferencia/proyectos/listarecibiran',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);

                        $('#textoFin').val(response.data.proyecto);

                        document.getElementById("select-proyectos").options.length = 0;

                        $.each(response.data.listado, function( key, val ){
                            $('#select-proyectos').append('<option value="' +val.id +'" selected="selected">'+ val.nombre +'</option>');
                        });
                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        function infoFinalizar(){
            Swal.fire({
                title: 'Cerrar Proyecto?',
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

            openLoading();

            // ID PROYECTO QUE ENTREGA MATERIALES
            var identrega = document.getElementById('id-editar').value;
            // ID QUE RECIBE MATERIALES
            var idrecibe = document.getElementById('select-proyectos').value;

            var fecha = document.getElementById('fecha-cierre').value;
            var descripcion = document.getElementById('descripcion-cierre').value;


            var documento = document.getElementById('documento');

            if(idrecibe === ''){
                toastr.error('Proyecto Recibe es requerido');
                return
            }

            if(fecha === ''){
                toastr.error('Fecha es requerida');
                return;
            }

            if(documento.files && documento.files[0]){ // si trae doc
                if (!documento.files[0].type.match('image/jpeg|image/jpeg|image/png|.pdf')){
                    toastr.error('formato permitidos: .png .jpg .jpeg .pdf');
                    return;
                }
            }

            var formData = new FormData();
            formData.append('identrega', identrega);
            formData.append('idrecibe', idrecibe);
            formData.append('fecha', fecha);
            formData.append('descripcion', descripcion);
            formData.append('documento', documento.files[0]);

            axios.post(url+'/transferencia/general/salida', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.error('El Proyecto (Finalizara) ya estaba Cerrado.');
                    }
                    else if(response.data.success === 10){

                        // SALIDA CORRECTA
                        salidaCorrecta()

                    }else{
                        toastr.error('Error al Cerrar Proyecto');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al Cerrar Proyecto');
                });
        }

        function salidaCorrecta(){
            Swal.fire({
                title: 'Salida Registrada',
                text: "",
                icon: 'success',
                showCancelButton: false,
                allowOutsideClick: false,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            })
        }


    </script>


@endsection
