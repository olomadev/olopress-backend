<?php

declare(strict_types=1);

namespace App\Handler\Pages;

use App\Model\PageModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private PageModel $pageModel)
    {
        $this->pageModel = $pageModel;
    }

    /**
     * @OA\Get(
     *   path="/pages/findAll",
     *   tags={"Pages"},
     *   summary="Find all pages",
     *   operationId="pages_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->pageModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
