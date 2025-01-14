<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Filter\Users\DisplayAvatarFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DisplayAvatarByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,
        private DisplayAvatarFilter $filter,
        private Error $error
    )
    {
        $this->filter = $filter;
        $this->userModel = $userModel;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/users/displayAvatarById/:userId",
     *   tags={"Users"},
     *   summary="Find user avatar by user id",
     *   operationId="users_displayAvatarById",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute("userId");
        $data['userId'] = $userId;
        $this->filter->setInputData($data);
        if ($this->filter->isValid()) {
            $row = $this->userModel->findAvatarById($userId);
            if (empty($row)) {
                return new TextResponse(
                    'No file found',
                    404
                );
            }
            $response = new Response('php://temp', 200);
            $response->getBody()->write($row['avatarImage']);
            $response = $response->withHeader('Pragma', 'public');
            $response = $response->withHeader('Cache-Control', 'max-age=86400');
            $response = $response->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
            $response = $response->withHeader('Content-Disposition', 'inline; filename=avatar.jpg');
            $response = $response->withHeader('Content-Type', $row['mimeType']); //  image/png
            return $response;
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
    }
}


