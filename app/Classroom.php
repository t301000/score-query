<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model {

	protected $table = 'classrooms';

    protected $casts = [
        'id' => 'integer',
        'school_year_in' => 'integer',
        'teacher_id' => 'integer',
    ];

    protected $fillable = ['school_year_in', 'class_name', 'teacher_id', 'class_code'];

    protected $hidden = ['created_at', 'updated_at'];

    public function students()
    {
        return $this->hasMany('App\Student', 'class_id');
    }

    public function exams()
    {
        return $this->hasMany('App\Exam', 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\User', 'teacher_id');
    }

}
