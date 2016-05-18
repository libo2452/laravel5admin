<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmzdmFeed extends Model {
    //
    public $table = 'smzdm_feeds';


    public $fillable = [
        'title' ,
        'link' ,
        'focus_pic' ,
        'pubdate' ,
        'description' ,
        'content' ,
    ];
}
