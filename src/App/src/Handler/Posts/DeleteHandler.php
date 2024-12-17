<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use App\Filter\Posts\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostModel $postModel,
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->postModel = $postModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/posts/delete/{postId}",
     *   tags={"posts"},
     *   summary="Delete post",
     *   operationId="posts_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="postId",
     *       required=true,
     *       description="Post uuid",
     *       @OA\Schema(
     *           type="string",
     *           format="uuid",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {   
        $this->filter->setInputData($request->getQueryParams());
        if ($this->filter->isValid()) {
            $this->postModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
