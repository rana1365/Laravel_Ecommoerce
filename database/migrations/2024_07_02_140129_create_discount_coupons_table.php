<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discount_coupons', function (Blueprint $table) {

            $table->id();

            /* The Discount Coupon Code */

            $table->string( 'code' );

            /* The Discount Coupon Name */

            $table->string( 'name' )->nullable();

            /* The Discount Coupon Description (not necessary) */

            $table->text( 'description' )->nullable();

            /* The max uses of Coupon Code has */

            $table->integer( 'max_uses' )->nullable();

            /* How many times a user can use Coupon Code */

            $table->integer( 'max_uses_user' )->nullable();

            /* Whether the coupon code is a percentage or a fixed price */

            $table->enum( 'type',['percent','fixed'] )->default('fixed');

            /* The amount to discount based on type */

            $table->double( 'discount_amount', 10, 2 );

            /* Set minimum amount to discount based on type */

            $table->double( 'min_amount', 10, 2 )->nullable();
            
            /* Checking the status */

            $table->integer( 'status' )->default(1);

            /* When the coupon code begins */

            $table->timestamp( 'starts_at' )->nullable();

            /* When the coupon code ends */

            $table->timestamp( 'expires_at' )->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_coupons');
    }
};
