<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';

    protected $fillable = ['customer_id', 'token', 'created_at'];
}