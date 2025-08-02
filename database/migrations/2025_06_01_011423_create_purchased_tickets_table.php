<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasedTicketsTable extends Migration
{
    public function up(): void
    {
        Schema::create('purchased_tickets', function (Blueprint $table) {
            $table->id('purchase_id');
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id');
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->string('status', 20);
            $table->timestamps();

            $table->foreign('ticket_id')->references('ticket_id')->on('tickets')->onDelete('restrict');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchased_tickets');
    }
}
