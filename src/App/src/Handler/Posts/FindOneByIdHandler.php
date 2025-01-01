<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Olobase\Mezzio\DataManagerInterface;
use App\Schema\Posts\PostsFindOneById;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostModel $postModel,
        private DataManagerInterface $dataManager
    )
    {
        $this->postModel = $postModel;
        $this->dataManager = $dataManager;
    }

    /**
     * @OA\Get(
     *   path="/posts/findOneById/{postId}",
     *   tags={"Posts"},
     *   summary="Find one post data",
     *   operationId="posts_findOneById",
     *
     *   @OA\Parameter(
     *       name="postId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PostsFindOneById"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $postId = $request->getAttribute("postId");
        $row = $this->postModel->findOneById($postId);
        if ($row) {
            $data = $this->dataManager->getViewData(PostsFindOneById::class, $row);
            return new JsonResponse($data);   
        }
        return new JsonResponse([], 404);
    }

}
