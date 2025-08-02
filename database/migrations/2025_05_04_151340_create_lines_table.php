<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinesTable extends Migration
{
    public function up(): void
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->id('line_id');
            $table->string('line_number', 10);
            $table->string('line_name', 100);
            $table->string('direction', 50)->nullable();
            $table->timestamps();

            $table->unique(['line_number', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
}
