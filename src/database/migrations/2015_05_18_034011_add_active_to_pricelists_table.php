<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveToPricelistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // Add column "active" if NOT exist
        if (!Schema::hasColumn('pricelists', 'active')) {
            Schema::table('pricelists', function(Blueprint $table)
            {
                $table->boolean('active')->default(true);
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
        // Drop column "active" if exists
        if (Schema::hasColumn('pricelists', 'active')) {
            Schema::table('pricelists', function(Blueprint $table)
            {
                $table->dropColumn('active');
            });
        }
	}

}
