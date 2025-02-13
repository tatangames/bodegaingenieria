<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('images/logosantaana_blanco.png') }}" alt="Logo" class="brand-image img-circle elevation-3" >
        <span class="brand-text font-weight" style="color: white">PANEL DE CONTROL</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">


                @can('sidebar.roles.y.permisos')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Roles y Permisos
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.permisos.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endcan



                <li class="nav-item">
                    <a href="{{ route('admin.materiales.index') }}" target="frameprincipal" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Inventario</p>
                    </a>
                </li>


                <li class="nav-item">
                    <a href="{{ route('admin.tiposproyecto.index') }}" target="frameprincipal" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Lista de Proyectos</p>
                    </a>
                </li>


                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Registros
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('admin.entrada.registro.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Registrar Entrada</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.salida.registro.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Registrar Salida</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.transferencias.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cierre de Proyecto</p>
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Historial
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">


                        <li class="nav-item">
                            <a href="{{ route('sidebar.bodega.historial.entradas') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Entradas Registros</p>
                            </a>
                        </li>







                    </ul>
                </li>




                <li class="nav-item">

                    <a href="#" class="nav-link nav-">
                        <i class="far fa-edit"></i>
                        <p>
                            Configuración
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">


                        <li class="nav-item">
                            <a href="{{ route('admin.anio.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Año</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.registrar.quienrecibe.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Quien Recibe</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.unidadmedida.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Unidad de Medida</p>
                            </a>
                        </li>


                    </ul>
                </li>










                        <li class="nav-item">

                            <a href="#" class="nav-link nav-">
                                <i class="far fa-edit"></i>
                                <p>
                                    Reportes
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entradas y Salidas</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.inventario.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Inventario</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.inventario.salidaproyecto.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Salida por Proyecto</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.inventario.tengoporproyecto.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Inventario Proyecto</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.inventario.proyectocompletado.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Proyecto Completado</p>
                                    </a>
                                </li>





                            </ul>
                        </li>





            </ul>
        </nav>

    </div>
</aside>
