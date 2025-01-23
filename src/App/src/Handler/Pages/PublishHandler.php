<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use App\Schema\Pages\PagePublish;
use App\Filter\Pages\PublishFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublishHandler implements RequestHandlerInterface
{
    public function __construct(
        private PageModel $pageModel,
        private DataManagerInterface $dataManager,
        private PublishFilter $filter,
        private Error $error,
    ) 
    {
        $this->pageModel = $pageModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }

    /**
     * @OA\Patch(
     *   path="/pages/publish/{pageId}",
     *   tags={"Pages"},
     *   summary="Publish page",
     *   operationId="pages_publish",
     *
     *   @OA\Parameter(
     *       name="pageId",
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
     *     @OA\JsonContent(ref="#/components/schemas/PagePublish"),
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
            $data = $this->dataManager->getSaveData(PagePublish::class, 'pages');
            $this->pageModel->publish($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
