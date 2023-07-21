<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fotos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_participante',150)->nullable(true);
            $table->string('titulo',100);
            $table->string('lugar',150);
            $table->text('resenia',600);
            $table->string('motivacion',200);
            $table->string('archivo');
            $table->boolean('activo')->default(true);
            $table->boolean('publicado')->default(true);
            $table->dateTime('fecha_carga');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('album_id')->constrained('albums');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fotos');
    }
}
