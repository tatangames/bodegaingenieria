<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCierreProyectoDetalleTable extends Migration
{
    /**
     * DETALLE DE MATERIALES QUE SE PASARON Y SE SUMARON A SUS UNIDADES
     * // SI EL MATERIAL NO EXISTE EN EL NUEVO PROYECTO, SE CREARA
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cierre_proyecto_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_cierre_proyecto')->unsigned();

            // MATERIAL QUE SALDRA CON LA CANTIDAD ACTUAL QUE TENIA PARA SALIR
            $table->bigInteger('id_entradas_detalle')->unsigned();
            $table->integer('cantidad_salida');

            // EN EL OTRO PROYECTO SOLO SE AUMENTARA LA CANTIDAD DISPONIBLE
            // SINO EXISTE SOLO SE CREA


            $table->foreign('id_cierre_proyecto')->references('id')->on('cierre_proyecto');
            $table->foreign('id_entradas_detalle')->references('id')->on('entradas_detalle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cierre_proyecto_detalle');
    }
}
