<?php

declare(strict_types=1);

namespace App\Handler\Files;

use App\Model\FileModel;
use App\Filter\Files\DisplayFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class DisplayByNameHandler implements RequestHandlerInterface
{
    public function __construct(
        private Translator $translator,
        private FileModel $fileModel,
        private DisplayFilter $filter,
        private Error $error
    )
    {
        $this->filter = $filter;
        $this->fileModel = $fileModel;
        $this->translator = $translator;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/files/display",
     *   tags={"Files"},
     *   summary="Display by name",
     *   operationId="files_display",
     *
     *   @OA\Parameter(
     *       in="query",
     *       name="fileName",
     *       required=true,
     *       description="File name",
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
        $queryParams = $request->getQueryParams();
        $get['fileName'] = $queryParams['fileName'];
        $this->filter->setInputData($get);
        if ($this->filter->isValid()) {
            $row = $this->fileModel->findOneByName($get['fileName']);
            if (empty($row)) {
                return new TextResponse(
                    $this->translator->translate('No file found'),
                    404
                );
            }
            $response = new Response('php://temp', 200);
            $response->getBody()->write($row['fileData']);
            $response = $response->withHeader('Pragma', 'public');
            $response = $response->withHeader('Expires', 0);
            $response = $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            // $response = $response->withHeader('Cache-Control', 'max-age=86400');
            // $response = $response->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
            $response = $response->withHeader('Content-Type', $row['fileType']); //  image/png
            return $response;
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
    }
}


