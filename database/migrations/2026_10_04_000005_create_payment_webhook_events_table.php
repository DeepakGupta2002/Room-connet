<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40);
            $table->string('event_id', 180)->unique();
            $table->string('event_name', 100);
            $table->json('payload');
            $table->string('signature', 128)->nullable();
            $table->string('status', 32)->default('received')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'event_name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
