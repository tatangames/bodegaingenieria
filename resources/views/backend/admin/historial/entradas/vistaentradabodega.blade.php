@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
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

    <section class="content" style="margin-top: 15px">
        <div class="container-fluid">
            <div class="card card-gray-dark">
                <div class="card-header">
                    <h3 class="card-title">Listado de Proyectos</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label style="color: #686868">Proyecto</label>
                        <div>
                            <select id="select-proyectos" class="form-control">
                                @foreach($arrayProyectos as $item)
                                    <option value="{{$item->id}}">{{$item->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="col-auto">
                        <button type="button" onclick="buscarListado()" class="btn btn-success btn-sm">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </div>

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

            $('#select-proyectos').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Busqueda no encontrada";
                    }
                },
            });

            var id = @json($primerId); // idproyecto

            if (id != null) {
                openLoading()
                var ruta = "{{ URL::to('/admin/bodega/historial/entrada/tabla') }}/" + id;
                $('#tablaDatatable').load(ruta);
            }

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function buscarListado(){
            var idproyecto = document.getElementById('select-proyectos').value;

            if(idproyecto === ''){
                toastr.error('Proyecto es requerida');
                return;
            }
            openLoading()
            var ruta = "{{ URL::to('/admin/bodega/historial/entrada/tabla') }}/" + idproyecto;
            $('#tablaDatatable').load(ruta);
        }

        function recargar(){
            var id = document.getElementById('select-proyectos').value;
            var ruta = "{{ URL::to('/admin/bodega/historial/entrada/tabla') }}/" + id;
            $('#tablaDatatable').load(ruta);
        }

        function vistaDetalle(idsolicitud){
            window.location.href="{{ url('/admin/bodega/historial/entradadetalle/index') }}/" + idsolicitud;
        }

        function infoBorrar(id){
            Swal.fire({
                title: 'ADVERTENCIA',
                text: "Esto eliminará todo el ingreso de productos. Si hubo salidas de producto también se eliminarán. Las solicitudes pueden pasar a pendiente, ya que si tuvo salidas, este se eliminará",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    borrarRegistro(id)
                }
            })
        }

        // BORRAR LOTE DE ENTRADA COMPLETO
        function borrarRegistro(id){

            openLoading();
            var formData = new FormData();
            formData.append('id', id);

            axios.post(url+'/bodega/historial/entrada/borrarlote', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.error('Proyecto ya esta Finalizado');
                        recargar();
                    }
                    else if(response.data.success === 2){
                        toastr.success('Borrado correctamente');
                        recargar();
                    }
                    else {
                        toastr.error('Error al borrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al borrar');
                    closeLoading();
                });
        }


        function infoNuevoIngreso(id){
            window.location.href="{{ url('/admin/bodega/historial/nuevoingresoentradadetalle/index') }}/" + id;
        }

    </script>


@endsection
