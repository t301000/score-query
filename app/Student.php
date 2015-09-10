<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model {

    protected $table = 'students';

    protected $casts = [
        'id' => 'integer',
        'class_id' => 'integer',
        'num' => 'integer',
    ];

    protected $fillable = ['class_id', 'num', 'name', 'link_code'];

    protected $hidden = ['created_at', 'updated_at'];

    public function classroom()
    {
        return $this->belongsTo('App\Classroom', 'class_id');
    }

    public function scores()
    {
        return $this->hasMany('App\Score');
    }

    /**
     * define relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

}
