<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Backend\Perfil\PerfilController;
use App\Http\Controllers\Backend\Roles\RolesController;
use App\Http\Controllers\Controles\ControlController;
use App\Http\Controllers\Backend\Roles\PermisoController;
use App\Http\Controllers\Backend\Repuestos\RepuestosController;
use App\Http\Controllers\Backend\Repuestos\SalidasController;
use App\Http\Controllers\Backend\Reportes\ReportesController;
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



// --- AÑO ---
Route::get('/admin/anio/index', [ConfiguracionController::class,'indexAnio'])->name('admin.anio.index');
Route::get('/admin/anio/tabla', [ConfiguracionController::class,'tablaAnio']);
Route::post('/admin/anio/nuevo', [ConfiguracionController::class, 'nuevoAnio']);
Route::post('/admin/anio/informacion', [ConfiguracionController::class, 'informacionAnio']);
Route::post('/admin/anio/editar', [ConfiguracionController::class, 'editarAnio']);


// --- UNIDAD DE MEDIDA ---
Route::get('/admin/unidadmedida/index', [ConfiguracionController::class,'indexUnidadMedida'])->name('admin.unidadmedida.index');
Route::get('/admin/unidadmedida/tabla/index', [ConfiguracionController::class,'tablaUnidadMedida']);
Route::post('/admin/unidadmedida/nuevo', [ConfiguracionController::class, 'nuevaUnidadMedida']);
Route::post('/admin/unidadmedida/informacion', [ConfiguracionController::class, 'informacionUnidadMedida']);
Route::post('/admin/unidadmedida/editar', [ConfiguracionController::class, 'editarUnidadMedida']);


// --- PERSONA QUE RECIBE EL MATERIAL ---
Route::get('/admin/registrar/quienrecibe/index', [ConfiguracionController::class,'indexVistaRegistroQuienRecibe'])->name('admin.registrar.quienrecibe.index');
Route::get('/admin/registrar/quienrecibe/tabla', [ConfiguracionController::class,'tablaRegistroQuienRecibe']);
Route::post('/admin/registrar/nombre/quienrecibe',  [ConfiguracionController::class,'registrarNombreQuienRecibe']);
Route::post('/admin/informacion/quienrecibe',  [ConfiguracionController::class,'informacionQuienRecibe']);
Route::post('/admin/actualizar/nombre/quienrecibe',  [ConfiguracionController::class,'actualizarNombreQuienRecibe']);

// --- REGISTRO DE MATERIAL ----
Route::get('/admin/inventario/index', [ConfiguracionController::class,'indexMateriales'])->name('admin.materiales.index');
Route::get('/admin/inventario/tabla/index', [ConfiguracionController::class,'tablaMateriales']);
Route::post('/admin/inventario/nuevo', [ConfiguracionController::class, 'nuevoMaterial']);
Route::post('/admin/inventario/informacion', [ConfiguracionController::class, 'informacionMaterial']);
Route::post('/admin/inventario/editar', [ConfiguracionController::class, 'editarMaterial']);

// - Detalle
Route::get('/admin/detalle/material/cantidad/{id}', [RepuestosController::class,'vistaDetalleMaterial']);
Route::get('/admin/detalle/materialtabla/cantidad/{id}', [RepuestosController::class,'tablaDetalleMaterial']);



// --- LISTA DE PROYECTOS ---
Route::get('/admin/proyecto/index', [ConfiguracionController::class,'indexProyectos'])->name('admin.tiposproyecto.index');
Route::get('/admin/proyecto/tabla/index/{idanio}', [ConfiguracionController::class,'tablaProyectos']);
Route::post('/admin/proyecto/nuevo', [ConfiguracionController::class, 'nuevoProyecto']);
Route::post('/admin/proyecto/informacion', [ConfiguracionController::class, 'informacionProyecto']);
Route::post('/admin/proyecto/editar', [ConfiguracionController::class, 'editarProyecto']);


// --- REGISTRO DE NUEVOS ENTRADAS DE MATERIAL ---
Route::get('/admin/registro/entrada', [RepuestosController::class,'indexRegistroEntrada'])->name('admin.entrada.registro.index');
Route::post('/admin/buscar/material/global',  [RepuestosController::class,'buscadorMaterialGlobal']);
Route::post('/admin/entrada/guardar',  [RepuestosController::class,'guardarEntrada']);


// --- SALIDAS DE MATERIAL ---
Route::get('/admin/registro/salida', [SalidasController::class,'indexRegistroSalida'])->name('admin.salida.registro.index');
Route::post('/admin/buscar/material/porproyecto',  [SalidasController::class,'buscadorMaterialPorProyecto']);
Route::post('/admin/buscar/material/proyecto/disponibilidad', [SalidasController::class, 'infoBodegaMaterialDetalleFila']);
Route::post('/admin/salida/guardar',  [SalidasController::class,'guardarSalidaMateriales']);
Route::post('/admin/salida/buscarinventario',  [SalidasController::class,'buscarInventarioVista']);


// ---- HISTORIAL ENTRADAS ---

// ---- ENTRADAS
Route::get('/admin/bodega/historial/entrada/index', [HistorialController::class,'indexHistorialEntradas'])->name('sidebar.bodega.historial.entradas');
Route::get('/admin/bodega/historial/entrada/tabla/{id}', [HistorialController::class,'tablaHistorialEntradas']);
Route::get('/admin/bodega/historial/entradadetalle/index/{id}', [HistorialController::class,'indexHistorialEntradasDetalle']);
Route::get('/admin/bodega/historial/entradadetalle/tabla/{id}', [HistorialController::class,'tablaHistorialEntradasDetalle']);
// vista para ingresar nuevo producto al lote existente
Route::get('/admin/bodega/historial/nuevoingresoentradadetalle/index/{id}', [HistorialController::class,'indexNuevoIngresoEntradaDetalle']);
Route::post('/admin/bodega/registrar/productosextras',  [HistorialController::class,'registrarProductosExtras']);

// BORRAR ENTRADA COMPLETA DE PRODUCTOS -> ELIMINARA SALIDAS SI HUBIERON
Route::post('/admin/bodega/historial/entrada/borrarlote', [HistorialController::class, 'historialEntradaBorrarLote']);
Route::post('/admin/bodega/historial/entradadetalle/borraritem', [HistorialController::class, 'historialEntradaDetalleBorrarItem']);


// --- HISTORIAL - SALIDAS MANUAL
Route::get('/admin/bodega/historial/salidas/index', [HistorialController::class,'indexHistorialSalidas'])->name('sidebar.bodega.historial.salidas');
Route::get('/admin/bodega/historial/salidas/tabla/{id}', [HistorialController::class,'tablaHistorialSalidas']);
Route::get('/admin/bodega/historial/salidadetalle/index/{id}', [HistorialController::class,'indexHistorialSalidasDetalle']);
Route::get('/admin/bodega/historial/salidadetalle/tabla/{id}', [HistorialController::class,'tablaHistorialSalidasDetalle']);
Route::post('/admin/bodega/historial/salidadetalle/borraritem', [HistorialController::class,'salidaDetalleBorrarItem']);









// TRANSFERENCIAS
Route::get('/admin/transferecias/a/huesera', [SalidasController::class,'indexTransferencias'])->name('admin.transferencias.index');
Route::post('/admin/generar/salida/transferencia',  [SalidasController::class,'geenrarSalidaTransferencia']);




// registrar quien recibe





// HISTORIAL

// listado historial de herramientas para Salida

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










