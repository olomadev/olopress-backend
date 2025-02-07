<?php

namespace App\Schema\Comments;

/**
 * @OA\Schema()
 */
class CommentsFindAllByPagingObject
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $postTitle;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
    /**
     * @var string
     * @OA\Property()
     */
    public $body;
    /**
     * @var integer
     * @OA\Property()
     */
    public $published;
    /**
     * @var string
     * @OA\Property()
     */
    public $createdAt;
}
