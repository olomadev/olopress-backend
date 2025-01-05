<?php

namespace App\Schema\FeaturedImages;

/**
 * @OA\Schema()
 */
class FeaturedImagesFindAll
{
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="fileId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="fileDimension",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="fileName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="fileType",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="fileData",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
