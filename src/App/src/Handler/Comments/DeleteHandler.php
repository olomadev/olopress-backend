<?php

declare(strict_types=1);

namespace App\Handler\Comments;

use App\Model\CommentModel;
use App\Filter\Comments\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private CommentModel $commentModel,
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->commentModel = $commentModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/comments/delete/{postId}",
     *   tags={"comments"},
     *   summary="Delete comment",
     *   operationId="comments_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="commentId",
     *       required=true,
     *       description="Comment uuid",
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
            $this->commentModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
