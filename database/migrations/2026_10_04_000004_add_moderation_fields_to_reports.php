<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->string('moderation_action', 32)->default('none')->after('status');
            $table->text('moderation_note')->nullable()->after('moderation_action');
            $table->index(['status', 'moderation_action']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex(['status', 'moderation_action']);
            $table->dropColumn(['moderation_action', 'moderation_note']);
        });
    }
};
