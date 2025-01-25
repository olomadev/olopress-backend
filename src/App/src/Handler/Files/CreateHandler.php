<?php

declare(strict_types=1);

namespace App\Handler\Files;

use App\Model\FileModel;
use App\Schema\Files\FileSave;
use App\Filter\Files\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private FileModel $fileModel,        
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->fileModel = $fileModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Post(
     *   path="/files/create",
     *   tags={"Files"},
     *   summary="Create a new file",
     *   operationId="files_create",
     *
     *   @OA\Parameter(
     *       in="query",
     *       name="thumb",
     *       required=false,
     *       description="Thumbnail creation switch",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Create a new file",
     *     @OA\JsonContent(ref="#/components/schemas/FileSave"),
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
        $post = $request->getParsedBody();    
        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        $response['data']['fileName'] = null;
        $thumb = (array_key_exists('thumb', $post) && $post['thumb'] == false) ? false : true;
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(FileSave::class, 'files');
            $response['data'] = $this->fileModel->create($data, $thumb);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
