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
        Schema::table('shipping_charges', function(Blueprint $table) {

            $table->string('country_id')->after('id');
            $table->double('amount',10,2)->after('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_charges', function(Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('amount');
            
        });
    }
};
