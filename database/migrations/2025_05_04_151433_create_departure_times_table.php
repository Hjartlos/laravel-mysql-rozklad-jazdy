<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeparturetimesTable extends Migration
{
    public function up(): void
    {
        Schema::create('departure_times', function (Blueprint $table) {
            $table->id('time_id');
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('stop_id');
            $table->time('departure_time');
            $table->timestamps();

            $table->unique(['trip_id', 'stop_id', 'departure_time']);

            $table->foreign('trip_id')->references('trip_id')->on('trips')->onDelete('cascade');
            $table->foreign('stop_id')->references('stop_id')->on('stops')->onDelete('cascade');

            $table->index('trip_id');
            $table->index('stop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departure_times');
    }
}
