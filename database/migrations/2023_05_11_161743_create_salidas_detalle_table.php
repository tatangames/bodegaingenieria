<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidasDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salidas_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_material')->unsigned();

            // solo la entrada detalle segun el proyecto que se selecciono
            $table->bigInteger('id_entrada_detalle')->unsigned();
            $table->bigInteger('id_salida')->unsigned();

            $table->integer('cantidad');

            $table->foreign('id_material')->references('id')->on('materiales');
            $table->foreign('id_entrada_detalle')->references('id')->on('entradas_detalle');
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
        Schema::dropIfExists('salidas_detalle');
    }
}
