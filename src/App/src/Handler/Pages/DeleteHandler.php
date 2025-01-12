<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use App\Filter\Pages\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private PageModel $pageModel,
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->pageModel = $pageModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/pages/delete/{pageId}",
     *   tags={"pages"},
     *   summary="Delete post",
     *   operationId="pages_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="pageId",
     *       required=true,
     *       description="Page uuid",
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
            $this->pageModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
