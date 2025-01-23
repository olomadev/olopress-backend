<?php

namespace App\Schema\Pages;

/**
 * @OA\Schema()
 */
class PagesFindAllByPagingObject
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
     * @var string
     * @OA\Property()
     */
    public $publishStatus;
}
