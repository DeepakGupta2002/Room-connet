<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone_encrypted', 500)->nullable()->after('name');
            $table->string('phone_hash', 64)->nullable()->unique()->after('phone_encrypted');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_hash');
            $table->string('status', 32)->default('active')->index()->after('phone_verified_at');
            $table->timestamp('donor_access_until')->nullable()->index()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('donor_access_until');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 32)->unique();
            $table->string('display_name', 64);
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('property_owners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('phone_encrypted', 500)->nullable();
            $table->string('phone_hash', 64)->nullable()->index();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('contact_consent_at')->nullable();
            $table->string('status', 32)->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['phone_hash', 'status']);
        });

        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listed_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('property_owners')->nullOnDelete();
            $table->foreignId('contact_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('listed_by_role', 32);
            $table->string('title', 180);
            $table->text('description');
            $table->decimal('rent_amount', 12, 2);
            $table->string('rent_type', 32)->default('monthly');
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->decimal('maintenance_charge', 12, 2)->nullable();
            $table->string('room_type', 32);
            $table->date('leaving_date')->nullable();
            $table->date('available_from');
            $table->date('expires_at')->index();
            $table->string('country', 80)->default('India');
            $table->string('state', 100)->nullable();
            $table->string('city', 100);
            $table->string('area', 120);
            $table->string('locality', 120)->nullable();
            $table->string('pincode', 12)->nullable();
            $table->text('approximate_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('location_radius_meters')->default(400);
            $table->string('slug', 220)->unique();
            $table->string('listing_status', 32)->default('active')->index();
            $table->string('verification_status', 32)->default('unverified')->index();
            $table->string('approval_status', 32)->default('not_required')->index();
            $table->unsignedTinyInteger('trust_score')->default(0);
            $table->boolean('is_flagged')->default(false)->index();
            $table->timestamp('last_owner_confirmed_at')->nullable();
            $table->timestamp('next_confirmation_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['city', 'area', 'listing_status']);
            $table->index(['available_from', 'expires_at', 'listing_status']);
            $table->index(['rent_amount', 'room_type', 'listing_status']);
            $table->index(['listed_by_user_id', 'listing_status']);
        });

        Schema::create('post_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('mime_type', 80)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
            $table->index(['post_id', 'display_order']);
        });

        Schema::create('donations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('INR');
            $table->string('provider', 40)->nullable();
            $table->string('order_id', 150)->nullable()->unique();
            $table->string('payment_id', 150)->nullable()->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('access_granted_until')->nullable()->index();
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::create('donation_public_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('donation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('public_name', 120)->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->boolean('show_amount')->default(false);
            $table->timestamps();
        });

        Schema::create('contact_unlocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
            $table->index(['user_id', 'unlocked_at']);
        });

        Schema::create('favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
        });

        Schema::create('reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 64);
            $table->text('details')->nullable();
            $table->string('status', 32)->default('open')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
            $table->index(['post_id', 'status']);
        });

        Schema::create('owner_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('property_owners')->nullOnDelete();
            $table->string('token_hash', 128)->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['post_id', 'status']);
        });

        Schema::create('login_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_hash', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('device')->nullable();
            $table->string('status', 32);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['phone_hash', 'created_at']);
        });

        Schema::create('blocked_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reason', 160);
            $table->timestamp('blocked_until')->nullable()->index();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('blocked_ips', function (Blueprint $table): void {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 160);
            $table->timestamp('blocked_until')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('rate_limit_events', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_hash', 128)->index();
            $table->string('action', 64);
            $table->unsignedInteger('count')->default(0);
            $table->timestamp('window_started_at')->nullable();
            $table->timestamp('last_request_at')->nullable();
            $table->timestamps();
            $table->unique(['subject_hash', 'action']);
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('meta_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 100);
            $table->string('title', 180);
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('listing_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_hash', 128)->nullable()->index();
            $table->string('ip_hash', 128)->nullable()->index();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['post_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_views');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('rate_limit_events');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('blocked_users');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('owner_approvals');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('contact_unlocks');
        Schema::dropIfExists('donation_public_profiles');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('post_images');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('property_owners');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['phone_hash']);
            $table->dropColumn([
                'phone_encrypted',
                'phone_hash',
                'phone_verified_at',
                'status',
                'donor_access_until',
                'last_login_at',
            ]);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
