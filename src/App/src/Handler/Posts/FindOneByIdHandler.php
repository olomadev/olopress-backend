<?php

declare(strict_types=1);

namespace App\Handler\Posts;

use App\Model\PostModel;
use Olobase\Mezzio\DataManagerInterface;
use App\Schema\Posts\PostsFindOneById;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private PostModel $postModel,
        private DataManagerInterface $dataManager
    )
    {
        $this->postModel = $postModel;
        $this->dataManager = $dataManager;
    }

    /**
     * @OA\Get(
     *   path="/roles/findOneById/{roleId}",
     *   tags={"Posts"},
     *   summary="Find item data",
     *   operationId="roles_findOneById",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PostsFindOneById"),
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roleId = $request->getAttribute("roleId");
        $row = $this->postModel->findOneById($roleId);
        if ($row) {
            $data = $this->dataManager->getViewData(PostsFindOneById::class, $row);
            return new JsonResponse($data);   
        }
        return new JsonResponse([], 404);
    }

}
