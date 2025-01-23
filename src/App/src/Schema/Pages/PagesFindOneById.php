<?php

namespace App\Schema\Pages;

/**
 * @OA\Schema()
 */
class PagesFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/PagesFindOneByIdObject",
     * )
     */
    public $data;
}
