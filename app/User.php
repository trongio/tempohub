<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname', 'email', 'password', 'name', 'surname', 'tel', 'language'
    ];

    public      $timestamps     = false;
    protected   $primaryKey     = 'nickname';
    public      $incrementing   = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function firma()
    {
        return $this -> belongsTo('App\Firma','firmaid');
    }

    public function login()
    {
        return $this -> hasMany('App\Login', 'nickname');
    }
}
