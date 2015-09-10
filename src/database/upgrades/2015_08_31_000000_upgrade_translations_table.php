<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeTranslationsTable extends Migration
{
    /**
     * Optional migrations.
     * Upgrade translations table from Redminportal 0.1 to 0.2/0.3
     * Not included by default, read UPGRADE.md for instructions.
     *
     * @return void
     */
    public function up()
    {
        // Check if the table exists
        if (Schema::hasTable('translations')) {
            if (Schema::hasColumn('translations', 'translatable_type')) {
                $items = DB::select('select * from translations');
                foreach ($items as $item) {
                    // Points to new namespace Redooor\\Redminportal\\App\\Models\\
                    $new_type_array = explode('\\', $item->translatable_type);
                    $last_model = array_pop($new_type_array);
                    $new_type = 'Redooor\\Redminportal\\App\\Models\\' . $last_model;
                    DB::table('translations')->where('id', $item->id)
                        ->update([
                        'translatable_type' => $new_type
                    ]);
                }
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
        // Check if the table exists
        if (Schema::hasTable('translations')) {
            if (Schema::hasColumn('translations', 'translatable_type')) {
                $items = DB::select('select * from translations');
                foreach ($items as $item) {
                    // Points to old namespace Redooor\\Redminportal\\
                    $old_type_array = explode('\\', $item->translatable_type);
                    $last_model = array_pop($new_type_array);
                    $old_type = 'Redooor\\Redminportal\\' . $last_model;
                    DB::table('translations')->where('id', $item->id)
                        ->update([
                        'translatable_type' => $old_type
                    ]);
                }
            }
        }
    }
}
