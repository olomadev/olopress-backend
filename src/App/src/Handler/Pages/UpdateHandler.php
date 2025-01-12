<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use App\Schema\Pages\PageSave;
use App\Filter\Pages\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private PageModel $pageModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->pageModel = $pageModel;
        $this->dataManager = $dataManager;
        $this->filter = $filter;
        $this->error = $error;
    }

    /**
     * @OA\Put(
     *   path="/pages/uptdate/{postId}",
     *   tags={"Pages"},
     *   summary="Update page",
     *   operationId="pages_update",
     *
     *   @OA\Parameter(
     *       name="pageId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update post",
     *     @OA\JsonContent(ref="#/components/schemas/PageSave"),
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(PageSave::class, 'pages');
            $this->pageModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
