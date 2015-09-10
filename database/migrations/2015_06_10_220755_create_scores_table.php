<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scores', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->tinyInteger('chinese')->unsigned();
            $table->tinyInteger('english')->unsigned();
            $table->tinyInteger('math')->unsigned();
			$table->tinyInteger('science')->unsigned();
			$table->decimal('social', 5, 2)->unsigned();
			$table->tinyInteger('history')->default(-1);
			$table->tinyInteger('geo')->default(-1);
			$table->tinyInteger('civic')->default(-1);
			$table->decimal('avg', 5, 2)->unsigned();
			$table->decimal('total', 5, 2)->unsigned();
            $table->tinyInteger('rank')->default(-1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scores');
	}

}
