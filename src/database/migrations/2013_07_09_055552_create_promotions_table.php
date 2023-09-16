<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function($table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('active')->default(true);
                $table->text('options')->nullable();
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
        Schema::dropIfExists('promotions');
    }
}
