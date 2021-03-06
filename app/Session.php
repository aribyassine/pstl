<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{

    protected $table = 'sessions';
    public $timestamps = true;
    protected $fillable = array('number', 'title');

    public function ue()
    {
        return $this->belongsTo(Ue::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class);
    }
    public function responses()
    {
        //TODO test
        return $this->hasMany(Response::class,'session_user','session_user_id');
    }


}