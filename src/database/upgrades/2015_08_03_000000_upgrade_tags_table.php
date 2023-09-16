<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpgradeTagsTable extends Migration
{
    /**
     * Optional migrations.
     * Upgrade tags table from Redminportal 0.1 to 0.2/0.3
     * Not included by default, read UPGRADE.md for instructions.
     *
     * @return void
     */
    public function up()
    {
        // Check if the 2 tables exist
        if (Schema::hasTable('tags') && Schema::hasTable('taggables')) {
            if (Schema::hasColumn('tags', 'tagable_id') && Schema::hasColumn('tags', 'tagable_type')) {
                $tags = DB::select('select * from tags');
                foreach ($tags as $tag) {
                    // Points to new namespace Redooor\\Redminportal\\App\\Models\\
                    $new_type_array = explode('\\', $tag->tagable_type);
                    $last_model = array_pop($new_type_array);
                    $new_type = 'Redooor\\Redminportal\\App\\Models\\' . $last_model;
                    DB::table('taggables')->insert([
                        'tag_id' => $tag->id,
                        'taggable_id' => $tag->tagable_id,
                        'taggable_type' => $new_type
                    ]);
                }
                Schema::table('tags', function ($table) {
                    $table->dropColumn(['tagable_id', 'tagable_type']);
                });
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
        // Check if the 2 tables exist
        if (Schema::hasTable('tags') && Schema::hasTable('taggables')) {
            if (! Schema::hasColumn('tags', 'tagable_id') && ! Schema::hasColumn('tags', 'tagable_type')) {
                Schema::table('tags', function($table) {
                    $table->integer('tagable_id')->default(1);
                    $table->string('tagable_type', 32)->default('type');
                });
                $tags = DB::table('tags')->get();
                foreach ($tags as $tag) {
                    $tagable = DB::table('taggables')->where('tag_id', '=', $tag->id)->first();
                    if ($tagable) {
                        // Points to old namespace Redooor\\Redminportal\\
                        $old_type_array = explode('\\', $tagable->taggable_type);
                        $last_model = array_pop($old_type_array);
                        $old_type = 'Redooor\\Redminportal\\' . $last_model;
                        DB::table('tags')
                            ->where('id', $tag->id)
                            ->update([
                                'tagable_id' => $tagable->taggable_id,
                                'tagable_type' => $old_type
                            ]);
                    }
                }
            }
        }
    }
}
