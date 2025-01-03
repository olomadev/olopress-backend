<?php

namespace App\Schema\Auth;

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
