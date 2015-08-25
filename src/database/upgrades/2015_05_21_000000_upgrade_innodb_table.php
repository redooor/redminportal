<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeInnodbTable extends Migration
{
    /**
     * Run the migrations.
     * Upgrade tables with foreign keys to InnoDB type
     *
     * @return void
     */
    public function up()
    {
        // CAUTION: only works and tested on MySQL
        // Legacy support, default type MyISAM doesn't support foreign key
        // Convert table to InnoDB
        if (Schema::hasTable('products')) {
            DB::statement('ALTER TABLE products ENGINE = InnoDB');
        }
        
        if (Schema::hasTable('categories')) {
            DB::statement('ALTER TABLE categories ENGINE = InnoDB');
        }

        if (Schema::hasTable('pricelists')) {
            DB::statement('ALTER TABLE pricelists ENGINE = InnoDB');
        }
        
        if (Schema::hasTable('medias')) {
            DB::statement('ALTER TABLE medias ENGINE = InnoDB');
        }
        
        if (Schema::hasTable('modules')) {
            DB::statement('ALTER TABLE modules ENGINE = InnoDB');
        }
        
        if (Schema::hasTable('memberships')) {
            DB::statement('ALTER TABLE memberships ENGINE = InnoDB');
        }
        
        if (Schema::hasTable('coupons')) {
            DB::statement('ALTER TABLE coupons ENGINE = InnoDB');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No turning back
    }
}
