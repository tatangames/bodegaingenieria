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

            // PROYECTO A CUAL ENTRARA
            $table->bigInteger('id_tipoproyecto')->unsigned();
            $table->date('fecha');

            $table->string('descripcion', 800)->nullable();

            $table->foreign('id_tipoproyecto')->references('id')->on('tipoproyecto');
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
