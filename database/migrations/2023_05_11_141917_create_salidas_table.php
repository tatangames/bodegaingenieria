<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_tipoproyecto')->unsigned();
            $table->bigInteger('id_recibe')->unsigned();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();
            $table->string('orden_salida', 30)->nullable();

            // Cuando se registra Cierre de Proyecto se guarda la salida de Materiales finales
            $table->boolean('cierre_proyecto');

            $table->foreign('id_usuario')->references('id')->on('usuario');
            $table->foreign('id_tipoproyecto')->references('id')->on('tipoproyecto');
            $table->foreign('id_recibe')->references('id')->on('quienrecibe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salidas');
    }
}
