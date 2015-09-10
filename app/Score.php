<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model {

    protected $table = 'scores';

    //protected $fillable = [];

    //protected $guarded = array('*');
    protected $guarded = ['id'];

    public $timestamps = false;

    public function exam()
    {
        return $this->belongsTo('App\Exam');
    }

    public function student()
    {
        return $this->belongsTo('App\Student');
    }

}
