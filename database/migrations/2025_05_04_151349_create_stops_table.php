<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStopsTable extends Migration
{
    public function up(): void
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->id('stop_id');
            $table->string('stop_name', 100)->unique();
            $table->double('location_lat');
            $table->double('location_lon');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
}
