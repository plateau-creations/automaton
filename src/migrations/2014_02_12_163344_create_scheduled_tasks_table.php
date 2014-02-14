<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateScheduledTasksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('scheduled_tasks', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('label');
			$table->string('type');
			$table->text('parameters'); // Serialized Array of parameters
			$table->boolean('done')->default(false);
			$table->text('errors');
			$table->float('timer')->default(0);
			$table->boolean('running')->default(false);
			$table->timestamps();
			$table->timestamp('scheduled_at');
			$table->boolean('success');
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
		Schema::drop('scheduled_tasks');
	}

}
