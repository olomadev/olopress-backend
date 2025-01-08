<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostsFindOneByIdObject
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
     * @var string
     * @OA\Property()
     */
    public $publishedAt;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    * )
    */
    public $featuredImageId;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *  )
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
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $tags;
}
