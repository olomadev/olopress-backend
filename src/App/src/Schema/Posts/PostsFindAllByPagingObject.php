<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostsFindAllByPagingObject
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $authorId;
    /**
     * @var string
     * @OA\Property()
     */
    public $title;
    /**
     * @var string
     * @OA\Property()
     */
    public $permalink;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishStatus;
    /**
     * @var string
     * @OA\Property()
     */
    public $publishedAt;
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $screenId;
    /**
     * @var string
     * @OA\Property()
     */
    public $imageType;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           )
    *     ),
    *  );
    */
    public $categories;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           )
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           )
    *     ),
    *  );
    */
    public $tags;
}
