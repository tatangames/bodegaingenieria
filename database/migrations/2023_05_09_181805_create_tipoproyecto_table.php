<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoproyectoTable extends Migration
{
    /**
     * LISTADO DE PROYECTOS
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipoproyecto', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_anio')->unsigned();

            $table->string('codigo', 100)->nullable();
            $table->string('nombre', 800);

            // PROYECTOS FINALIZADO
            $table->boolean('cerrado');

            // CUANDO UN PROYECTO NO TENGA MATERIALES, Y SE CIERRA.
            // NO SE REGISTRA NINGUN CIERRE_PROYECTO, SOLO TENDREMOS FECHA DE CIERRE
            $table->date('fecha_cierre')->nullable();

            $table->foreign('id_anio')->references('id')->on('anio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipoproyecto');
    }
}
