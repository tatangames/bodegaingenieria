<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Backend\Perfil\PerfilController;
use App\Http\Controllers\Backend\Roles\RolesController;
use App\Http\Controllers\Controles\ControlController;
use App\Http\Controllers\Backend\Roles\PermisoController;
use App\Http\Controllers\Backend\UnidadMedida\UnidadMedidaController;
use App\Http\Controllers\Backend\Repuestos\RepuestosController;
use App\Http\Controllers\Backend\Proyectos\TipoProyectoController;
use App\Http\Controllers\Backend\Repuestos\SalidasController;
use App\Http\Controllers\Backend\Reportes\ReportesController;
use App\Http\Controllers\Backend\Herramientas\HerramientasController;
use App\Http\Controllers\Backend\Reportes\ReporteHerramientaController;
use App\Http\Controllers\Backend\Configuracion\ConfiguracionController;
use App\Http\Controllers\Backend\Historial\HistorialController;


Route::get('/', [LoginController::class,'index'])->name('login');

Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- CONTROL WEB ---
Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');

// --- ROLES ---
Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

// --- PERMISOS ---
Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);

// --- PERFIL ---
Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

// --- SIN PERMISOS VISTA 403 ---
Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');


// registro unidad de medida
Route::get('/admin/unidadmedida/index', [UnidadMedidaController::class,'index'])->name('admin.unidadmedida.index');
Route::get('/admin/unidadmedida/tabla/index', [UnidadMedidaController::class,'tablaUnidadMedida']);
Route::post('/admin/unidadmedida/nuevo', [UnidadMedidaController::class, 'nuevaUnidadMedida']);
Route::post('/admin/unidadmedida/informacion', [UnidadMedidaController::class, 'informacionUnidadMedida']);
Route::post('/admin/unidadmedida/editar', [UnidadMedidaController::class, 'editarUnidadMedida']);

// registro de repuestos para tener un inventario
Route::get('/admin/inventario/index', [RepuestosController::class,'index'])->name('admin.materiales.index');
Route::get('/admin/inventario/tabla/index', [RepuestosController::class,'tablaMateriales']);
Route::post('/admin/inventario/nuevo', [RepuestosController::class, 'nuevoMaterial']);
Route::post('/admin/inventario/informacion', [RepuestosController::class, 'informacionMaterial']);
Route::post('/admin/inventario/editar', [RepuestosController::class, 'editarMaterial']);
Route::post('/admin/informacion/herramienta/descartar', [RepuestosController::class, 'infoHerramientaDescartar']);
Route::post('/admin/descartar/herramienta/inventario', [RepuestosController::class, 'descartarHerramientaInventario']);




// detalle repuestos
Route::get('/admin/detalle/material/cantidad/{id}', [RepuestosController::class,'vistaDetalleMaterial']);
Route::get('/admin/detalle/materialtabla/cantidad/{id}', [RepuestosController::class,'tablaDetalleMaterial']);


// registro de un proyecto
Route::get('/admin/proyecto/index', [TipoProyectoController::class,'index'])->name('admin.tiposproyecto.index');
Route::get('/admin/proyecto/tabla/index', [TipoProyectoController::class,'tablaProyectos']);
Route::post('/admin/proyecto/nuevo', [TipoProyectoController::class, 'nuevoProyecto']);
Route::post('/admin/proyecto/informacion', [TipoProyectoController::class, 'informacionProyecto']);
Route::post('/admin/proyecto/editar', [TipoProyectoController::class, 'editarProyecto']);


// registrar entrada para repuestos
Route::get('/admin/registro/entrada', [RepuestosController::class,'indexRegistroEntrada'])->name('admin.entrada.registro.index');
Route::post('/admin/buscar/material',  [RepuestosController::class,'buscadorMaterial']);
Route::post('/admin/entrada/guardar',  [RepuestosController::class,'guardarEntrada']);


// registrar salida de repuestos
Route::get('/admin/registro/salida', [SalidasController::class,'indexRegistroSalida'])->name('admin.salida.registro.index');
Route::post('/admin/salida/guardar',  [SalidasController::class,'guardarSalida']);

Route::post('/admin/buscar/material/porproyecto',  [SalidasController::class,'buscadorMaterialPorProyecto']);

Route::post('/admin/repuesto/cantidad/bloque', [SalidasController::class,'bloqueCantidades']);


// TRANSFERENCIAS
Route::get('/admin/transferecias/a/huesera', [SalidasController::class,'indexTransferencias'])->name('admin.transferencias.index');
Route::post('/admin/generar/salida/transferencia',  [SalidasController::class,'geenrarSalidaTransferencia']);




// CONFIGURACION

// registrar quien recibe
Route::get('/admin/registrar/quienrecibe/index', [ConfiguracionController::class,'indexVistaRegistroQuienRecibe'])->name('admin.registrar.quienrecibe.index');
Route::get('/admin/registrar/quienrecibe/tabla', [ConfiguracionController::class,'tablaRegistroQuienRecibe']);
Route::post('/admin/registrar/nombre/quienrecibe',  [ConfiguracionController::class,'registrarNombreQuienRecibe']);
Route::post('/admin/informacion/quienrecibe',  [ConfiguracionController::class,'informacionQuienRecibe']);
Route::post('/admin/actualizar/nombre/quienrecibe',  [ConfiguracionController::class,'actualizarNombreQuienRecibe']);


// registrar quien entrega
Route::get('/admin/registrar/quienentrega/index', [ConfiguracionController::class,'indexVistaRegistroQuienEntrega'])->name('admin.registrar.quienentrega.index');
Route::get('/admin/registrar/quienentrega/tabla', [ConfiguracionController::class,'tablaRegistroQuienEntrega']);
Route::post('/admin/registrar/nombre/quienentrega',  [ConfiguracionController::class,'registrarNombreQuienEntrega']);
Route::post('/admin/informacion/quienentrega',  [ConfiguracionController::class,'informacionQuienEntrega']);
Route::post('/admin/actualizar/nombre/quienentrega',  [ConfiguracionController::class,'actualizarNombreQuienEntrega']);



// HISTORIAL

// listado historial de herramientas para Salida
Route::get('/admin/historial/salida/herramienta/index', [HistorialController::class,'indexHistorialHerramientaSalida'])->name('admin.historial.salidas.herramientas');
Route::get('/admin/historial/salida/herramienta/tabla', [HistorialController::class,'tablaHistorialHerramientaSalida']);
Route::post('/admin/historial/salida/herramienta/informacion',  [HistorialController::class,'informacionHistorialSalidaHerramienta']);
Route::post('/admin/historial/salida/herramienta/actualizar',  [HistorialController::class,'actualizarHistorialSalidaHerramienta']);

Route::get('/admin/historial/salida/herramientas/detalle/{id}', [HistorialController::class,'detalleIndexHistorialSalidasHerramientas']);
Route::get('/admin/historial/salida/herramientas/detalletabla/{id}', [HistorialController::class,'detalleTablaHistorialSalidasHerramientas']);


// listado historial de repuestos para salida
Route::get('/admin/historial/salida/repuestos/index', [HistorialController::class,'indexHistorialRepuestosSalida'])->name('admin.historial.salidas.repuestos');
Route::get('/admin/historial/salida/repuestos/tabla', [HistorialController::class,'tablaHistorialRepuestosSalida']);
Route::post('/admin/historial/salida/repuestos/informacion',  [HistorialController::class,'informacionHistorialSalidaRepuesto']);
Route::post('/admin/historial/salida/repuestos/actualizar',  [HistorialController::class,'actualizarHistorialSalidaRepuesto']);

Route::get('/admin/historial/salida/repuestos/detalle/{id}', [HistorialController::class,'detalleIndexHistorialSalidas']);
Route::get('/admin/historial/salida/repuestos/detalletabla/{id}', [HistorialController::class,'detalleTablaHistorialSalidas']);

















// REPORTES DE ENTRADAS Y SALIDAS DE REPUESTOS
Route::get('/admin/entrada/reporte/vista', [ReportesController::class,'indexEntradaReporte'])->name('admin.entrada.reporte.index');
Route::get('/admin/reporte/registro/{tipo}/{desde}/{hasta}', [ReportesController::class,'reportePdfEntradaSalida']);


// REPORTES DE INVENTARIO
Route::get('/admin/reporte/inventario', [ReportesController::class,'vistaParaReporteInventario'])->name('admin.reporte.inventario.index');
Route::get('/admin/reporte/inventario/pdf/{tipo}', [ReportesController::class,'reporteInventarioActual']);

// que ha salido para x proyecto
Route::get('/admin/reporte/inventario/quehasalido/proyecto', [ReportesController::class,'vistaQueHaSalidoProyecto'])->name('admin.reporte.inventario.salidaproyecto.index');
Route::get('/admin/reporte/quehasalido/proyectos/pdf/{idproy}/{desde}/{hasta}/{tipo}', [ReportesController::class,'pdfQueHaSalidoProyectos']);

// inventario que materiales tiene x proyecto ahorita
Route::get('/admin/reporte/inventario/quetengopor/proyecto', [ReportesController::class,'vistaQueTengoPorProyecto'])->name('admin.reporte.inventario.tengoporproyecto.index');
Route::get('/admin/reporte/quetengopor/proyectos/pdf/{idproy}', [ReportesController::class,'reporteQueTengoPorProyecto']);

// ver los materiales que sobraron de un proyecto completado
Route::get('/admin/reporte/inventario/sobranteterminado/proyecto', [ReportesController::class,'vistaProyectoCompletado'])->name('admin.reporte.inventario.proyectocompletado.index');
Route::get('/admin/reporte/inventario/sobranteterminado/proy/{idtrans}', [ReportesController::class,'reporteProyectoTerminado']);






// REPORTE DE HERRAMIENTAS
Route::get('/admin/reporte/herramienta/index', [ReportesController::class,'vistaReporteHerramientas'])->name('admin.reporte.herramientas.index');
Route::get('/admin/pdf/herramientas/inventario/actual', [ReporteHerramientaController::class,'pdfHerramientasActuales']);
Route::get('/admin/pdf/herramientas/salidas/{desde}/{hasta}', [ReporteHerramientaController::class,'pdfHerramientasSalidas']);
Route::get('/admin/pdf/herramientas/reingreso/{desde}/{hasta}', [ReporteHerramientaController::class,'pdfHerramientasReingreso']);
Route::get('/admin/pdf/herramientas/descartadas', [ReporteHerramientaController::class,'pdfHerramientasDescartadas']);
Route::get('/admin/pdf/herramientas/nuevosingresos/{desde}/{hasta}', [ReporteHerramientaController::class,'pdfNuevasHerramientas']);






// *** HERRAMIENTAS ***

Route::get('/admin/inventario/herramientas/index', [HerramientasController::class,'indexInventarioHerramientas'])->name('admin.inventario.herramientas.index');
Route::get('/admin/inventario/herramientas/tabla', [HerramientasController::class,'tablaInventarioHerramientas']);
Route::post('/admin/inventario/herramientas/nuevo', [HerramientasController::class, 'nuevaHerramienta']);
Route::post('/admin/inventario/herramientas/informacion', [HerramientasController::class, 'informacionHerramienta']);
Route::post('/admin/inventario/herramienta/editar', [HerramientasController::class, 'editarMaterial']);


// REGISTRO DE NUEVAS HERRAMIENTAS
Route::get('/admin/registro/herramientas/index', [HerramientasController::class,'indexRegistroHerramientas'])->name('admin.registro.herramientas.index');
Route::post('/admin/buscar/herramienta',  [HerramientasController::class,'buscadorHerramienta']);
Route::post('/admin/entrada/herramienta/guardar',  [HerramientasController::class,'guardarEntradaHerramienta']);

// SALIDA DE UNA HERRAMIENTA A UN USUARIO
Route::get('/admin/salidas/herramientas/index', [HerramientasController::class,'indexSalidaHerramientas'])->name('admin.salida.herramientas.index');
Route::get('/admin/herramienta/cantidad/bloque/{id}', [HerramientasController::class,'bloqueCantidadHerramienta']);
Route::post('/admin/salida/herramienta/a/usuario',  [HerramientasController::class,'salidaHerramientaUsuario']);


// REINGRESO DE HERRAMIENTA
Route::get('/admin/inventario/reingreso/herramientas/index', [HerramientasController::class,'indexReingresoHerramientas'])->name('admin.reingreso.herramientas.index');
Route::get('/admin/inventario/reingreso/herramientas/tabla', [HerramientasController::class,'tablaReingresoHerramientas']);
Route::post('/admin/reingreso/informacion',  [HerramientasController::class,'reingresoInformacion']);
Route::post('/admin/reingreso/cantidad',  [HerramientasController::class,'reingresoCantidadHerramienta']);
Route::post('/admin/descartar/cantidad',  [HerramientasController::class,'descartarCantidadHerramienta']);


// REPORTE SALIDA POR MATERIAL
Route::get('/admin/reporte/salida/pormaterial/index', [ReportesController::class,'vistaSalidaPorMaterial'])->name('admin.reporte.salida.material.index');
Route::get('/admin/pdf/salida/pormaterial/proyecto/{desde}/{hasta}/{materiales}', [ReportesController::class,'pdfReporteMaterialesSalidas']);



