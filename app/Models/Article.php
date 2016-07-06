<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    public $table = 'articles';

    public $fillable = [
        'title',
        'link',
        'pubdate',
        'description',
        'content',
    ];
}
