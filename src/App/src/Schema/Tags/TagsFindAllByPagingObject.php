<?php

namespace App\Schema\Tags;

/**
 * @OA\Schema()
 */
class TagsFindAllByPagingObject
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
    public $tagName;  
}
