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

            // PROYECTO SALDRAN LOS MATERIALES
            $table->bigInteger('id_tipoproyecto_sale')->unsigned();
            // PROYECTO A CUAL VAMOS A ENTREGAR LO NUEVO
            $table->bigInteger('id_tipoproyecto_entre')->unsigned();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // acta de cierre
            $table->string('documento', 100)->nullable();

            // esta entrada debe ser para un proyecto o inventario general
            $table->foreign('id_tipoproyecto_sale')->references('id')->on('tipoproyecto');
            $table->foreign('id_tipoproyecto_entre')->references('id')->on('tipoproyecto');
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
