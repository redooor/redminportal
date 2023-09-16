<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddAutomaticallyApplyToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to products
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function(Blueprint $table)
            {
                if (!Schema::hasColumn('coupons', 'automatically_apply')) {
                    $table->boolean('automatically_apply')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from products
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function(Blueprint $table)
            {
                if (Schema::hasColumn('coupons', 'automatically_apply')) {
                    $table->dropColumn(['automatically_apply']);
                }
            });
        }
    }
}
