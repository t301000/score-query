<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model {

    protected $table = "exams";

    protected $fillable = ['grade', 'half', 'name', 'social_merge', 'class_id'];

    //public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
        'grade' => 'integer',
        'class_id' => 'integer',
        'social_merge' => 'boolean'
    ];

    public function scores()
    {
        return $this->hasMany('App\Score');
    }

}
