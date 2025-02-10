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
</style>

<div id="divcontenedor" style="display: none">

    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h2>Transferencia de Proyectos</h2>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-9">

                    <div class="card card-gray-dark">
                        <div class="card-header">
                            <h3 class="card-title">Información</h3>
                        </div>

                        <div class="card-body">

                            <div class="card-body col-md-6">
                                <div class="row">
                                    <label>Fecha de Transferencia:</label>
                                    <input style="width: 35%; margin-left: 25px;" type="date" class="form-control" id="fecha">
                                </div>
                            </div>

                            <div style="margin-left: 15px; margin-right: 15px; margin-top: 15px;">
                                <div class="form-group">
                                    <label>Asignar Proyecto:</label>
                                    <select id="select-tipoproyecto" class="form-control">
                                        @foreach($tipoproyecto as $item)
                                            <option value="{{$item->id}}">{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div style="margin-left: 15px; margin-right: 15px; margin-top: 15px;">
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <input type="text" class="form-control" autocomplete="off" maxlength="800" id="descripcion">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Documento Acta de Cierre (opcional)</label>
                                <input type="file" id="documento" class="form-control" accept="image/jpeg, image/jpg, image/png, .pdf"/>
                            </div>

                            <div class="form-group" style="float: right">
                                <br>
                                <button type="button" onclick="guardarTransferencia()" class="btn btn-primary btn-sm float-right" style="margin-top:10px; margin-right: 15px;">
                                    <i class="fas fa-edit" title="Guardar Transferencia"></i> Guardar Transferencia</button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


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

            $('#select-tipoproyecto').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Busqueda no encontrada";
                    }
                },
            });
        });
    </script>

    <script>

           function preguntaGuardar(){
            colorBlancoTabla();

            Swal.fire({
                title: 'Guardar Transferencia?',
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

        function guardarTransferencia(){

            var fecha = document.getElementById('fecha').value;
            var descripc = document.getElementById('descripcion').value; // max 800
            var idproyecto = document.getElementById('select-tipoproyecto').value;

            var documento = document.getElementById('documento');

            if(fecha === ''){
                toastr.error('Fecha es requerida');
            }

            if(descripc.length > 800){
                toastr.error('Descripción máximo 800 caracteres');
                return;
            }

            if(idproyecto === ''){
                toastr.error('Proyecto es requerido');
                return;
            }

            if(documento.files && documento.files[0]){ // si trae doc
                if (!documento.files[0].type.match('image/jpeg|image/jpeg|image/png|.pdf')){
                    toastr.error('formato permitidos: .png .jpg .jpeg .pdf');
                    return;
                }
            }

            openLoading();

            let formData = new FormData();
            formData.append('fecha', fecha);
            formData.append('descripcion', descripc);
            formData.append('idproyecto', idproyecto);
            formData.append('documento', documento.files[0]);


            axios.post(url+'/generar/salida/transferencia', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        Swal.fire({
                            title: 'No Guardado',
                            text: "Este Proyecto ya tiene 1 Transferencia",
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
                        // NO TIENE REGISTRADO MATERIALES O NO TIENE CANTIDAD YA PARA TRANSFERIR
                        Swal.fire({
                            title: 'No Guardado',
                            text: "el Proyecto no tiene Registrados Materiales o No tiene cantidad ya disponibles",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }
                    else if(response.data.success === 3){

                        // TRANSFERENCIA CORRECTA
                        toastr.success('Transferencia Correcta');


                        Swal.fire({
                            title: 'Transferencia Correcta',
                            text: "Los materiales han sido agregados al Inventario General",
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar',
                            closeOnClickOutside: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload()
                            }
                        })
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





    </script>


@endsection
