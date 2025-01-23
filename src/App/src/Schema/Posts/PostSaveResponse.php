<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostSaveResponse
{
    /**
     * @var string
     * @OA\Property()
     */
    public $permalink;
}
