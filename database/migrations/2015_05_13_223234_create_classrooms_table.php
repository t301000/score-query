<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassroomsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::dropIfExists('classrooms');

        Schema::create('classrooms', function(Blueprint $table)
		{
			$today = \Carbon\Carbon::today();
            $month = $today->month;
            $year = $today->year;
            // 預設學年度為現在學年度
            $default_year = ($month < 8) ? $year-1912 : $year-1911;

            $table->increments('id');
            $table->smallInteger('school_year_in')->unsigned()->default($default_year);
			$table->string('class_name');
			$table->string('class_code');
            $table->integer('teacher_id')->unsigned()->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('SET NULL');
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
		Schema::drop('classrooms');
	}

}
