<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class UserProfileObject
{
    /**
     * @var string
     * @OA\Property()
     */
    public $firstname;
    /**
     * @var string
     * @OA\Property()
     */
    public $lastname;
    /**
     * @var string
     * @OA\Property()
     */
    public $jobTitle;
    /**
     * @var string
     * @OA\Property()
     */
    public $themeColor;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    * )
    */
    public $locale;

}