<?php

namespace App\Schema\Tags;

/**
 * @OA\Schema()
 */
class TagSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $tagId;
    /**
     * @var string
     * @OA\Property()
     */
    public $tagName;
}
