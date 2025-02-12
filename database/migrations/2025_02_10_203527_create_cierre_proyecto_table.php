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

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // acta de cierre
            $table->string('documento', 100)->nullable();

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
        Schema::dropIfExists('cierre_proyecto');
    }
}
