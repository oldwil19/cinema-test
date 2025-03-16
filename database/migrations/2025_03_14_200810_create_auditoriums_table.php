<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('auditoriums', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('seats'); // Almacena los asientos de la sala
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->time('opening_time')->default('00:00:00'); // Horario de apertura
            $table->time('closing_time')->default('23:59:59'); // Horario de cierre
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoriums');
    }
};
