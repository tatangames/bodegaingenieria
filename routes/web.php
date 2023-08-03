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

Route::get('/', [LoginController::class,'index'])->name('login');

Route::post('/login', [LoginController::class, 'login']);
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



// REPORTES DE ENTRADAS Y SALIDAS
Route::get('/admin/entrada/reporte/vista', [ReportesController::class,'indexEntradaReporte'])->name('admin.entrada.reporte.index');
Route::get('/admin/reporte/registro/{tipo}/{desde}/{hasta}', [ReportesController::class,'reportePdfEntradaSalida']);


// REPORTES DE INVENTARIO
Route::get('/admin/reporte/inventario', [ReportesController::class,'vistaParaReporteInventario'])->name('admin.reporte.inventario.index');
Route::get('/admin/reporte/inventario/pdf', [ReportesController::class,'reporteInventarioActual']);



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








// INGRESO DE HERRAMIENTAS AL INVENTARIO
//Route::get('/admin/inventario/herramientas/index', [HerramientasController::class,'indexHerramientas'])->name('admin.herramientas.index');
//Route::get('/admin/inventario/herramientas/tabla/index', [HerramientasController::class,'tablaHerramientas']);
//Route::post('/admin/inventario/nuevo', [RepuestosController::class, 'nuevoMaterial']);
//Route::post('/admin/inventario/informacion', [RepuestosController::class, 'informacionMaterial']);
//Route::post('/admin/inventario/editar', [RepuestosController::class, 'editarMaterial']);
