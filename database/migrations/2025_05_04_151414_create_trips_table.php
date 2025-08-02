<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id('trip_id');
            $table->unsignedBigInteger('line_id');
            $table->unsignedBigInteger('day_id');
            $table->timestamps();

            $table->foreign('line_id')->references('line_id')->on('lines')->onDelete('cascade');
            $table->foreign('day_id')->references('day_id')->on('operating_days')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
}
