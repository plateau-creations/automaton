<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCronExpressionSupport extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::table('scheduled_tasks', function(Blueprint $table)
		{
			$table->boolean('is_cron')->default(false);
			$table->string('cron_expression', 20)->nullable();
		});

		Schema::create('cron_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('label');
			$table->text('parameters'); // Serialized Array of parameters
			$table->string('type');
			$table->text('errors');
			$table->boolean('success');
			$table->float('timer')->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		Schema::table('scheduled_tasks', function(Blueprint $table)
		{
			$table->dropColumn('is_cron');
			$table->dropColumn('cron_expression');
		});
		Schema::drop('cron_logs');
	}

}
