<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePortfoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('portfolios')) {
            Schema::create('portfolios', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->boolean('active')->default(true);
                $table->text('options')->nullable();
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                 // created_at | updated_at DATETIME
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
        Schema::dropIfExists('portfolios');
    }
}
