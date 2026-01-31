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
            $table->string('customer_email')->index();
            $table->string('customer_phone');
            $table->string('country_code', 5);
            $table->string('country', 2);
            $table->string('profession');
            $table->text('expectations');
            $table->integer('amount');
            $table->string('currency', 3)->default('COP');
            $table->enum('status', [
                'pending', 
                'approved', 
                'declined', 
                'error', 
                'voided', 
                'refunded', 
                'failed'
            ])->default('pending');
            $table->string('payment_method');
            $table->string('payment_method_type')->nullable();
            $table->string('wompi_id')->nullable()->index();
            $table->json('wompi_response')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->index(['reference', 'status']);
            $table->index(['customer_email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};