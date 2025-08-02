<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperatingdaysTable extends Migration
{
    public function up(): void
    {
        Schema::create('operating_days', function (Blueprint $table) {
            $table->id('day_id');
            $table->string('name', 20)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operating_days');
    }
}
