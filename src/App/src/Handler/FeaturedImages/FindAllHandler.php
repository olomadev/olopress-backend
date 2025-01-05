<?php

declare(strict_types=1);

namespace App\Handler\FeaturedImages;

use App\Model\FileModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private FileModel $fileModel)
    {
        $this->fileModel = $fileModel;
    }

    /**
     * @OA\Get(
     *   path="/featured-images/findAll",
     *   tags={"Featured Images"},
     *   summary="Find all images to set featured image",
     *   operationId="featuredImages_findAll",
     *
     *   @OA\Parameter(
     *       in="query",
     *       name="postId",
     *       required=true,
     *       description="Post id",
     *       @OA\Schema(
     *           type="string",
     *           format="uuid"
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/FeaturedImagesFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->fileModel->findAllPostImages($request->getQueryParams());
        return new JsonResponse([
            'data' => is_array($data) ? $data : array()
        ]);
    }

}
