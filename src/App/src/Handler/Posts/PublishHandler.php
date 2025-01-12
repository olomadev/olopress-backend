<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use App\Schema\Posts\PostPublish;
use App\Filter\Posts\PublishFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublishHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostModel $postModel,
        private DataManagerInterface $dataManager,
        private PublishFilter $filter,
        private Error $error,
    ) 
    {
        $this->postModel = $postModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }

    /**
     * @OA\Patch(
     *   path="/posts/publish/{postId}",
     *   tags={"Posts"},
     *   summary="Publish post",
     *   operationId="posts_publish",
     *
     *   @OA\Parameter(
     *       name="postId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="publishStatus",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Publish post",
     *     @OA\JsonContent(ref="#/components/schemas/PostPublish"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getQueryParams());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(PostPublish::class, 'posts');
            $this->postModel->publish($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
