<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReaccionsTable extends Migration
{

    public function up()
    {
        Schema::create('reaccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foto_id')->constrained('fotos');
            $table->boolean('tipo_reaccion');
            $table->dateTime('fecha');
            $table->string('terminal_ip',20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reaccions');
    }
}
