<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    protected $table = 'tempohub_dbaudit';

    protected $fillable = [];

    public function user()
    {
        return $this -> belongsTo('App\User','nickname');
    }
}
