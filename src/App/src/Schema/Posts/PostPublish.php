<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostPublish
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $postId;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishStatus;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishedAt;
}
