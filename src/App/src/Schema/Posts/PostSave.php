<?php

namespace App\Schema\Posts;

/**
 * @OA\Schema()
 */
class PostSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $postId;
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
    /**
     * @var string
     * @OA\Property()
     */
    public $publishedAt;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $featuredImageId;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
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
    *     ),
    *  );
    */
    public $tags;
}
