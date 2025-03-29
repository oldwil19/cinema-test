<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->string('movie_id'); // ID de la película en OMDb
            $table->string('movie_title'); // Nombre de la película
            $table->unsignedBigInteger('auditorium_id'); // Relación con auditoriums
            $table->dateTime('start_time'); // Fecha y hora de inicio de la función
            $table->json('available_seats'); // Asientos disponibles
            $table->json('reserved_seats')->nullable(); // Asientos reservados
            $table->timestamps();

            $table->foreign('auditorium_id')->references('id')->on('auditoriums')->onDelete('restrict');

        });
    }

    public function down()
    {
        Schema::dropIfExists('showtimes');
    }
};
