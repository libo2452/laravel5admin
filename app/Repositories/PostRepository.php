<?php

namespace App\Repositories;

use App\Models\Post;
use InfyOm\Generator\Common\BaseRepository;

class PostRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'date'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Post::class;
    }
}
