<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('country', 2);
            $table->string('profession');
            $table->text('expectations');
            $table->integer('amount');
            $table->string('currency')->default('COP');
            $table->enum('status', [
                'pending', 
                'approved', 
                'declined', 
                'failed', 
                'error', 
                'voided', 
                'expired'
            ])->default('pending');
            $table->string('wompi_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->json('wompi_response')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('customer_email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};