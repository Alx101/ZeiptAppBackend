<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = ['name', 'cid', 'password'];

    public function cards()
    {
        return $this->hasMany('App\Models\Card')->whereNotNull('lastfour');
    }
}