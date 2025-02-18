<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCierreProyectoTable extends Migration
{
    /**
     * CIERRE DE PROYECTO, PASARAN RESTANTE A OTRO PROYECTO
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cierre_proyecto', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_entrega')->unsigned(); // PROYECTOS ENTREGA
            $table->bigInteger('id_recibe')->unsigned(); // PROYECTOS RECIBE

            $table->bigInteger('id_entrada')->unsigned(); // ESTO SERIA PARA PROYECTO RECIBE
            $table->bigInteger('id_salida')->unsigned(); // ESTO SERIA PARA PROYECTO ENTREGA

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // acta de cierre
            $table->string('documento', 100)->nullable();

            $table->foreign('id_usuario')->references('id')->on('usuario');
            $table->foreign('id_entrega')->references('id')->on('tipoproyecto');
            $table->foreign('id_recibe')->references('id')->on('tipoproyecto');
            $table->foreign('id_entrada')->references('id')->on('entradas');
            $table->foreign('id_salida')->references('id')->on('salidas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cierre_proyecto');
    }
}
