<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use Olobase\Mezzio\DataManagerInterface;
use App\Schema\Pages\PagesFindOneById;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private PageModel $pageModel,
        private DataManagerInterface $dataManager
    )
    {
        $this->pageModel = $pageModel;
        $this->dataManager = $dataManager;
    }

    /**
     * @OA\Get(
     *   path="/pages/findOneById/{pageId}",
     *   tags={"Pages"},
     *   summary="Find one page data",
     *   operationId="posts_findOneById",
     *
     *   @OA\Parameter(
     *       name="pageId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PagesFindOneById"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = $request->getAttribute("pageId");
        $row = $this->pageModel->findOneById($pageId);
        if ($row) {
            $data = $this->dataManager->getViewData(PagesFindOneById::class, $row);
            return new JsonResponse($data);   
        }
        return new JsonResponse([], 404);
    }

}
