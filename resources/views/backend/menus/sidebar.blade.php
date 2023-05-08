<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" >
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

                @can('sidebar.catalogo')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Catálogo
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">


                            @can('sidebar.registrar.repuestos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.materiales.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Repuestos</p>
                                    </a>
                                </li>
                            @endcan


                            @can('sidebar.registrar.llantas')
                                <li class="nav-item">
                                    <a href="{{ route('admin.llantas.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Llantas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('sidebar.catalogo.aceites.lubricantes')
                                <li class="nav-item">
                                    <a href="{{ route('admin.catalogo.aceites.lubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Aceites y Lubricantes</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan


                @can('sidebar.registros.repuestos')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Registro Repuestos
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('registros.entradas.repuestos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.registro.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Entrada</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.salidas.repuestos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.salida.registro.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Salida</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan

                @can('sidebar.registros.aceites.lubricantes')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Registro Aceites y Lubricantes
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('registros.entradas.aceites.lubricantes')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.registro.aceitelubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Entrada</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.salidas.aceites.lubricantes')
                                <li class="nav-item">
                                    <a href="{{ route('admin.salida.registro.aceitelubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Salida</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.salidas.aceites.enuso')
                                <li class="nav-item">
                                    <a href="{{ route('admin.salida.enuso.aceitelubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>En USO</p>
                                    </a>
                                </li>
                            @endcan

                            @can('sidebar.ver.aceites.finalizados')
                                <li class="nav-item">
                                    <a href="{{ route('admin.finalizados.aceitelubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Finalizados</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan

                @can('sidebar.registros.llantas')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Registro LLantas
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('registros.entradas.llantas')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.llantas.registro.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Entrada</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.salidas.llantas')
                                <li class="nav-item">
                                    <a href="{{ route('admin.salida.llantas.registro.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Salida</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan

                @can('sidebar.configuracion')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Configuración
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            @can('registro.proveedores')

                                <li class="nav-item">
                                    <a href="{{ route('admin.proveedor.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Proveedores</p>
                                    </a>
                                </li>

                            @endcan

                            @can('registro.ubicacion.llanta')
                                <li class="nav-item">
                                    <a href="{{ route('admin.ubicacion.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ubicación LLanta</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registro.marca.llanta')
                                <li class="nav-item">
                                    <a href="{{ route('admin.marcas.llantas.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Marca de LLantas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registro.medidarin.llanta')
                                <li class="nav-item">
                                    <a href="{{ route('admin.rin.llantas.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipo de Llanta</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.unidadmedida')
                                <li class="nav-item">
                                    <a href="{{ route('admin.unidadmedida.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Unidad de Medida</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.equipo')
                                <li class="nav-item">
                                    <a href="{{ route('admin.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registrar Equipos</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registros.ubicacion.repuestos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.ubicacion.repuestos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ubicación de Repuestos</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan

                @can('sidebar.historial.repuestos')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Historial
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            @can('historial.entrada.repuesto')
                                <li class="nav-item">
                                    <a href="{{ route('admin.historial.entrada.repuesto.vista.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Repuesto Entradas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('historial.salida.repuesto')
                                <li class="nav-item">
                                    <a href="{{ route('admin.historial.salida.repuesto.vista.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Repuesto Salidas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('historial.salida.llanta')
                                <li class="nav-item">
                                    <a href="{{ route('admin.historial.entrada.llanta.vista.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entrada Llanta</p>
                                    </a>
                                </li>
                            @endcan

                            @can('historial.entrada.llanta')
                                <li class="nav-item">
                                    <a href="{{ route('admin.historial.salida.llanta.vista.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Salida Llanta</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan


                @can('sidebar.reporte.repuesto')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Reporte Repuesto
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            @can('reporte.repuestos.entradaysalida')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entradas y Salidas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.repuesto.equipos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Por Equipo</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.repuesto.cantidades')
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.cantidad.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cantidad Actual</p>
                                    </a>
                                </li>
                            @endcan

                            @can('sidebar.reporte.catalogo.repuestos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.catalogo.repuestos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Catálogo</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('sidebar.reporte.llantas')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Reporte Llantas
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('reporte.llantas.entradaysalida')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.llanta.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entradas y Salidas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.llantas.equipos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.llanta.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Por Marcas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.llantas.cantidades')
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.llanta.cantidad.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cantidad Actual</p>
                                    </a>
                                </li>
                            @endcan

                            @can('registro.firmas.llantas')
                                <li class="nav-item">
                                    <a href="{{ route('admin.registro.firmas.llanta.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ajuste de Firma</p>
                                    </a>
                                </li>
                            @endcan

                            @can('sidebar.reporte.catalogo.llantas')
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.catalogo.llantas.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Catálogo</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan


                @can('sidebar.reporte.aceitesylubricantes')
                    <li class="nav-item">

                        <a href="#" class="nav-link nav-">
                            <i class="far fa-edit"></i>
                            <p>
                                Reporte Aceite y Lubric.
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            @can('reporte.aceitesylubricantes.entradaysalida')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.aceitelubricantes.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Entradas y Salidas</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.aceitesylubricantes.equipos')
                                <li class="nav-item">
                                    <a href="{{ route('admin.entrada.reporte.aceitelubricantes.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Por Equipo</p>
                                    </a>
                                </li>
                            @endcan

                            @can('reporte.aceitesylubricantes.cantidades')
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.aceitelubricantes.cantidad.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cantidad Actual</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcan


            </ul>
        </nav>

    </div>
</aside>
