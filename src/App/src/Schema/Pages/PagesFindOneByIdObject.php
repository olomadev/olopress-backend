<?php

namespace App\Schema\Pages;

/**
 * @OA\Schema()
 */
class PagesFindOneByIdObject
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
     * @var array
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
    /**
     * @var array
     * @OA\Property()
     */
    public $pageFiles;
}
