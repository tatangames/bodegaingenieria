<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaSalidaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_salida', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();
            $table->string('quien_recibe', 200);
            $table->string('quien_entrega', 200);

            // # de salida de herramienta
            $table->string('num_salida', 100)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histo_herramienta_salida');
    }
}
