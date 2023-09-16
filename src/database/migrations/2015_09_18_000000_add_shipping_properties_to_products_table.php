<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddShippingPropertiesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns to products
        if (Schema::hasTable('products')) {
            Schema::table('products', function(Blueprint $table)
            {
                if (!Schema::hasColumn('products', 'weight_unit')) {
                    $table->string('weight_unit', 3)->nullable();
                }
                if (!Schema::hasColumn('products', 'volume_unit')) {
                    $table->string('volume_unit', 3)->nullable();
                }
                if (!Schema::hasColumn('products', 'length')) {
                    $table->decimal('length', 8, 3)->nullable();
                }
                if (!Schema::hasColumn('products', 'width')) {
                    $table->decimal('width', 8, 3)->nullable();
                }
                if (!Schema::hasColumn('products', 'height')) {
                    $table->decimal('height', 8, 3)->nullable();
                }
                if (!Schema::hasColumn('products', 'weight')) {
                    $table->decimal('weight', 8, 3)->nullable();
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
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'weight_unit')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['weight_unit']);
                });
            }
            if (Schema::hasColumn('products', 'volume_unit')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['volume_unit']);
                });
            }
            if (Schema::hasColumn('products', 'length')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['length']);
                });
            }
            if (Schema::hasColumn('products', 'width')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['width']);
                });
                }
            if (Schema::hasColumn('products', 'height')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['height']);
                });
            }
            if (Schema::hasColumn('products', 'weight')) {
                Schema::table('products', function(Blueprint $table)
                {
                    $table->dropColumn(['weight']);
                });
            }
            
        }
    }
}
