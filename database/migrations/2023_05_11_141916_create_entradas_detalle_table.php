<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntradasDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entradas_detalle', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_entradas')->unsigned();
            $table->bigInteger('id_material')->unsigned();

            $table->integer('cantidad');

            // SE IRA SUMANDO LA CANTIDAD ENTREGADA
            $table->integer('cantidad_entregada');

            $table->foreign('id_entradas')->references('id')->on('entradas');
            $table->foreign('id_material')->references('id')->on('materiales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entradas_detalle');
    }
}
