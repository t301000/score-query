<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model {

	protected $table = "schools";

    protected $fillable = ['code', 'name'];

    public $timestamps = false;

}
