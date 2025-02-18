

<section class="content">
    <div class="container-fluid">
        <div class="row">

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 5%">Fecha Ingreso</th>
                                <th style="width: 12%">Observación</th>
                                <th style="width: 6%">Entrada por Cierre Pro.</th>
                                <th style="width: 6%">Opciones</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($listado as $dato)
                                <tr>
                                    <td>{{ $dato->fecha }}</td>
                                    <td>{{ $dato->observacion }}</td>

                                    <td>@if($dato->cierre_proyecto == 1)
                                            <span class="badge bg-success">Entrada por Cierre</span>
                                    @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-xs"
                                                onclick="vistaDetalle({{ $dato->id }})">
                                            <i class="fas fa-eye" title="Detalle"></i>&nbsp; Detalle
                                        </button>

                                        <!-- VERIFICAR QUE NO SE REGISTRO LA ENTRADA POR CIERRE DE OTRO PROYECTO -->
                                        @if($dato->cierre_proyecto == 0)

                                            <!-- PROYECTO COMPLETO NO ESTA FINALIZADO AUN -->
                                            @if($infoProyecto->cerrado == 0)
                                                <button style="margin: 3px" type="button" class="btn btn-danger btn-xs"
                                                        onclick="infoBorrar({{ $dato->id }})">
                                                    <i class="fas fa-trash" title="Borrar"></i>&nbsp; Borrar
                                                </button>

                                                <button style="margin: 3px" type="button" class="btn btn-warning btn-xs"
                                                        onclick="infoNuevoIngreso({{ $dato->id }})">
                                                    <i class="fas fa-plus" title="Nuevo Ingreso"></i>&nbsp; Nuevo Ingreso
                                                </button>
                                            @endif
                                        @endif



                                    </td>
                                </tr>
                            @endforeach

                            <script>
                                setTimeout(function () {
                                    closeLoading();
                                }, 500);
                            </script>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $(function () {
        $("#tabla").DataTable({
            "paging": true,
            "lengthChange": true,
            "order": [[0, 'desc']],
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pagingType": "full_numbers",
            "lengthMenu": [[500, -1], [500, "Todo"]],
            "language": {

                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }

            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });
    });


</script>
