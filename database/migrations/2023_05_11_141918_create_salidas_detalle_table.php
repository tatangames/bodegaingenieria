<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidasDetalleTable extends Migration
{
    /**
     * SALIDAS DETALLE
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salidas_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_salida')->unsigned();

            $table->bigInteger('id_entrada_detalle')->unsigned();
            // la entradas puede ser decimales
            $table->decimal('cantidad', 10, 2);

            $table->foreign('id_salida')->references('id')->on('salidas');
            $table->foreign('id_entrada_detalle')->references('id')->on('entradas_detalle');
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
