@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
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

                    <div class="row d-flex align-items-center">
                        <div class="form-group col-md-3">
                            <label style="color: #686868">Año Proyecto</label>
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

    <script type="text/javascript">
        $(document).ready(function(){
            openLoading()

            var id = @json($primerId); // idproyecto

            if (id != null) {
                openLoading()
                var ruta = "{{ URL::to('/admin/bodega/historial/salidas/tabla') }}/" + id;
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
            var ruta = "{{ URL::to('/admin/bodega/historial/salidas/tabla') }}/" + idproyecto;
            $('#tablaDatatable').load(ruta);
        }

        function recargar(){
            var id = document.getElementById('select-proyectos').value;
            var ruta = "{{ URL::to('/admin/bodega/historial/salidas/tabla') }}/" + id;
            $('#tablaDatatable').load(ruta);
        }

        function vistaDetalle(idsolicitud){
            window.location.href="{{ url('/admin/bodega/historial/salidadetalle/index') }}/" + idsolicitud;
        }


    </script>


@endsection
