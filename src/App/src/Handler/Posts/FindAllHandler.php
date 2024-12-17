<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private PostModel $postModel)
    {
        $this->postModel = $postModel;
    }

    /**
     * @OA\Get(
     *   path="/posts/findAll",
     *   tags={"Posts"},
     *   summary="Find all posts",
     *   operationId="roles_findAll",
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
        $data = $this->postModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
