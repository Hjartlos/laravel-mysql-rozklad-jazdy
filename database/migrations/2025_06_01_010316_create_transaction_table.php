<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('from_stop_id');
            $table->unsignedBigInteger('to_stop_id');
            $table->unsignedBigInteger('line_id')->nullable();
            $table->timestamp('departure_time');
            $table->datetime('arrival_time')->nullable();
            $table->integer('duration_minutes');
            $table->decimal('price', 8, 2);
            $table->string('status', 20);
            $table->string('payment_id')->nullable();
            $table->json('route_data')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('from_stop_id')->references('stop_id')->on('stops')->onDelete('restrict');
            $table->foreign('to_stop_id')->references('stop_id')->on('stops')->onDelete('restrict');
            $table->foreign('line_id')->references('line_id')->on('lines')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}
