<?php

declare(strict_types=1);

namespace App\Handler\Screenshots;

use App\Model\ScreenshotModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private ScreenshotModel $screenshotModel
    )
    {
        $this->screenshotModel = $screenshotModel;
    }

    /**
     * @OA\Get(
     *   path="/screenshots/display/{id}",
     *   tags={"Screenshots"},
     *   summary="Display by post or page id",
     *   operationId="screenshots_display",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="id",
     *       required=true,
     *       description="Screenshot post or page uuid",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation (File content returns to Base64 string)",
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");
        $row = $this->screenshotModel->findOneById($id);
        if (empty($row) || empty($row['imageType'])) {
            return new TextResponse(
                'No file found',
                404
            );
        }
        $extArray = explode("/", $row['imageType']);
        $extension = end($extArray);
        $response = new Response('php://temp', 200);
        $response->getBody()->write($row['imageData']);
        $response = $response->withHeader('Pragma', 'public');
        $response = $response->withHeader('Cache-Control', 'max-age=86400');
        $response = $response->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        $response = $response->withHeader('Content-Disposition', 'inline; filename='.$id.'.'.$extension);
        $response = $response->withHeader('Content-Type', $row['imageType']);
        return $response;
    }
}


