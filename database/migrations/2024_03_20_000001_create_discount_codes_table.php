<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'percentage',
                'fixed_amount',
                'percentage_with_cap',
                'specific_user',
                'specific_group',
                'specific_product',
                'bogo',
                'buy_x_get_y',
                'with_expiry',
                'first_n_users',
                'minimum_basket',
                'bundle',
                'category_based',
                'shipping',
                'loyalty_points',
                'referral',
                'bulk_purchase',
                'payment_method',
                'first_time_buyer',
                'seasonal',
                'limited_quantity',
                'location_based',
                'customer_segment',
                'membership',
                'gift_card',
                'tiered',
                'flash',
                'user_anniversary',
                'app_exclusive',
                'free_gift',
                'upgrade',
                'subscription',
                'milestone',
                'refer_a_friend',
                'product_launch',
                'donation_based',
                'buy_more_save_more',
                'combo',
                'exchange'
            ]);
            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('minimum_basket_value', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('can_be_combined')->default(false);
            $table->json('conditions')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
}; 