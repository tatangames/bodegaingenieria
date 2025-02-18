<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntradasTable extends Migration
{
    /**
     * ES QUE LO QUE TENEMOS ACTUALMENTE EN INVENTARIO
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_tipoproyecto')->unsigned();
            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // PARA SABER QUE ESTA ENTRADA FUE QUE RECIBI DE UN X PROYECTO
            $table->boolean('cierre_proyecto');

            $table->foreign('id_tipoproyecto')->references('id')->on('tipoproyecto');
            $table->foreign('id_usuario')->references('id')->on('usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entradas');
    }
}
