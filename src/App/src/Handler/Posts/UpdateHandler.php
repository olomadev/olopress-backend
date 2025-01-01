<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use App\Schema\Posts\PostSave;
use App\Filter\Posts\SaveFilter;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostModel $postModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->postModel = $postModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }

    /**
     * @OA\Put(
     *   path="/posts/uptdate/{postId}",
     *   tags={"Posts"},
     *   summary="Update post",
     *   operationId="posts_create",
     *
     *   @OA\Parameter(
     *       name="postId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update post",
     *     @OA\JsonContent(ref="#/components/schemas/PostSave"),
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
        $user = $request->getAttribute(UserInterface::class); // get id from current token
        $userId = $user->getId();
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(PostSave::class, 'posts');
            $data['posts']['authorId'] = $userId;
            $this->postModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
