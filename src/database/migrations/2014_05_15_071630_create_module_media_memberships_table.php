<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateModuleMediaMembershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('module_media_memberships')) {
            Schema::create('module_media_memberships', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('module_id')->unsigned();
                $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
                $table->integer('media_id')->unsigned();
                $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');
                $table->integer('membership_id')->unsigned();
                $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
                $table->unique(array('module_id', 'media_id', 'membership_id'));
                $table->timestamps();
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
        Schema::dropIfExists('module_media_memberships');
    }
}
