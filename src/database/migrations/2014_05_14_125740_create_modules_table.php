<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('modules')) {
            Schema::create('modules', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('sku', 255)->unique()->nullable();
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->boolean('featured')->default(false);
                $table->boolean('active')->default(true);
                $table->text('options')->nullable();
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                $table->timestamps();
                // Need to use InnoDB to support foreign key
                $table->engine = 'InnoDB';
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
        Schema::dropIfExists('modules');
    }
}
