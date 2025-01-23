<?php

namespace App\Schema\Pages;

/**
 * @OA\Schema()
 */
class PageSave
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
    public $route;
    /**
     * @var string
     * @OA\Property()
     */
    public $title;
    /**
     * @var string
     * @OA\Property()
     */
    public $keywords;
    /**
     * @var string
     * @OA\Property()
     */
    public $description;
    /**
     * @var string
     * @OA\Property()
     */
    public $contentJson;
    /**
     * @var string
     * @OA\Property()
     */
    public $contentHtml;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishStatus;
}
