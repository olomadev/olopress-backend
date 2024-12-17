<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostsFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/PostsFindOneByIdObject",
     * )
     */
    public $data;
}
