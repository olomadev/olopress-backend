<?php

namespace App\Schema\Files;

/**
 * @OA\Schema()
 */
class FileSave
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
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $fileId;
    /**
     * @var string
     * @OA\Property()
     */
    public $fileName;
    /**
     * @var string
     * @OA\Property()
     */
    public $fileType;
    /**
     * @var integer
     * @OA\Property()
     */
    public $fileSize;
    /**
     * @var string
     * @OA\Property()
     */
    public $fileData;
}
