<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinestopTable extends Migration
{
    public function up(): void
    {
        Schema::create('linestop', function (Blueprint $table) {
            $table->unsignedBigInteger('line_id');
            $table->unsignedBigInteger('stop_id');
            $table->integer('sequence');
            $table->timestamps();

            $table->primary(['line_id', 'stop_id']);
            $table->foreign('line_id')->references('line_id')->on('lines')->onDelete('cascade');
            $table->foreign('stop_id')->references('stop_id')->on('stops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linestop');
    }
}
