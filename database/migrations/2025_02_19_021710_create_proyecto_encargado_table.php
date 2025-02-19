<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectoEncargadoTable extends Migration
{
    /**
     * PROYECTO PUEDE TENER VARIOS ENCARGADOS
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proyecto_encargado', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipoproyecto')->unsigned();
            $table->bigInteger('id_encargado')->unsigned();

            $table->foreign('id_tipoproyecto')->references('id')->on('tipoproyecto');
            $table->foreign('id_encargado')->references('id')->on('encargados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proyecto_encargado');
    }
}
