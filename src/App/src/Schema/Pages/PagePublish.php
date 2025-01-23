<?php

namespace App\Schema\Pages;

/**
 * @OA\Schema()
 */
class PagePublish
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $pageId;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishStatus;
}
