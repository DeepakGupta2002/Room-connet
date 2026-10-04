<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table): void {
            $table->string('payment_method', 32)->default('razorpay')->after('currency');
            $table->string('payment_reference', 150)->nullable()->unique()->after('payment_id');
            $table->string('proof_path', 500)->nullable()->after('payment_reference');
            $table->index(['payment_method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table): void {
            $table->dropIndex(['payment_method', 'status']);
            $table->dropUnique(['payment_reference']);
            $table->dropColumn(['payment_method', 'payment_reference', 'proof_path']);
        });
    }
};
