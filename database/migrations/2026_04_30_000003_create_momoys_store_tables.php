<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('customer')->after('password');
            }
        });

        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->text('model_url')->nullable();
            $table->integer('stock')->default(0);
            $table->json('variants')->nullable();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('logo_url')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id')->index();
            $table->json('items');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->text('shipping_address')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending_down_payment');
            $table->string('delivery_status')->default('processing');
            $table->json('current_location')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->string('paymongo_checkout_session_id')->nullable();
            $table->string('paymongo_checkout_status')->nullable();
            $table->text('paymongo_checkout_url')->nullable();
            $table->timestamp('paymongo_checkout_created_at')->nullable();
            $table->timestamp('paymongo_paid_at')->nullable();
            $table->timestamp('paymongo_last_synced_at')->nullable();
            $table->text('last_chat_message')->nullable();
            $table->string('last_chat_sender_role')->nullable();
            $table->timestamp('chat_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('sender_id');
            $table->string('sender_email')->nullable();
            $table->string('sender_role')->default('customer');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('order_id')->index();
            $table->string('user_id')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('status')->default('completed');
            $table->string('provider')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_checkout_session_id')->nullable();
            $table->string('provider_event_source')->nullable();
            $table->timestamps();
        });

        Schema::create('queries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('unread');
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('message');
            $table->integer('rating')->nullable();
            $table->string('user_id')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('queries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_messages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('products');
        Schema::dropIfExists('api_tokens');

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
