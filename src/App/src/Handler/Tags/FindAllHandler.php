<?php

declare(strict_types=1);

namespace App\Handler\Tags;

use App\Model\TagModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private TagModel $tagModel)
    {
        $this->tagModel = $tagModel;
    }

    /**
     * @OA\Get(
     *   path="/tags/findAll",
     *   tags={"Tags"},
     *   summary="Find all tags",
     *   operationId="tags_findAll",
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
        $data = $this->tagModel->findAll($request->getQueryParams());
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
