<?php

declare(strict_types=1);

namespace App\Handler\Files;

use App\Model\FileModel;
use App\Filter\Files\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private FileModel $fileModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->fileModel = $fileModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/files/delete",
     *   tags={"Files"},
     *   summary="Delete file",
     *   operationId="files_delete",
     
     *   @OA\Parameter(
     *       in="query",
     *       name="fileName",
     *       required=true,
     *       description="File name",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="postId",
     *       required=false,
     *       description="Post id",
     *       @OA\Schema(
     *           type="string",
     *       ),
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
        if ($this->filter->isValid()) {
            $this->fileModel->delete(
                $this->filter->getValue('fileName'),
                $this->filter->getValue('postId')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
