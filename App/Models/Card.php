<?php
/**
 * Created by PhpStorm.
 * User: aleorn
 * Date: 2018-03-30
 * Time: 16:46
 */

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'cards';

    protected $fillable = ['customer_id', 'lastfour', 'card_type', 'card_expires'];
}