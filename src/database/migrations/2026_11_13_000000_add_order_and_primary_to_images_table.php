<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('images')) {
            Schema::table('images', function (Blueprint $table) {
                if (! Schema::hasColumn('images', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }

                if (! Schema::hasColumn('images', 'is_primary')) {
                    $table->boolean('is_primary')->default(false);
                }
            });

            // The first image each record already has becomes its primary image
            $firstIds = DB::table('images')
                ->selectRaw('MIN(id) as id')
                ->groupBy('imageable_type', 'imageable_id')
                ->pluck('id')
                ->all();

            foreach (array_chunk($firstIds, 500) as $chunk) {
                DB::table('images')->whereIn('id', $chunk)->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('images')) {
            if (Schema::hasColumn('images', 'sort_order')) {
                Schema::table('images', function (Blueprint $table) {
                    $table->dropColumn('sort_order');
                });
            }

            if (Schema::hasColumn('images', 'is_primary')) {
                Schema::table('images', function (Blueprint $table) {
                    $table->dropColumn('is_primary');
                });
            }
        }
    }
};
