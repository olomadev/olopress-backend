<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use function generateRandomNumber, createGuid, isUid;

class FileModel
{
    private $conn;
    private $adapter;

    public function __construct(   
        private TableGatewayInterface $files,
        private TableGatewayInterface $postFiles,
        private TableGatewayInterface $posts
    )
    {
        $this->files = $files;
        $this->postFiles = $postFiles;
        $this->posts = $posts;
        $this->adapter = $files->getAdapter();
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllPostImages(array $get)
    {
        if (empty($get['postId']) && ! isUid($get['postId'])) {
            return array();
        }
        $postId = $get['postId'];
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            ['id' => 'fileId']
        );
        $select->from(['pf' => 'postFiles']);
        $select->join(['f' => 'files'], 'f.fileId = pf.fileId', ['name' => 'fileName'], $select::JOIN_LEFT);
        $select->where(['pf.postId' => $postId, 'f.fileDimension' => '80x55']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findOneByName(string $fileName)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'fileId',
                'fileName',
                'fileSize',
                'fileType',
                'fileData',
            ]
        );
        $select->from(['f' => 'files']);
        $select->where(['f.fileName' => $fileName]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return  $row;
    }

    public function create(array $data) : array
    {
        $postId = null;
        if (! empty($data['files']['postId'])) {
            $postId = $data['files']['postId'];
            unset($data['files']['postId']);
        }
        $data['files']['fileTag'] = 'original';
        $data['files']['fileType'] = 'image/webp';
        $fileMeta = pathinfo($data['files']['fileName']);
        $fileMeta['filename'] = $fileMeta['filename'].'-'.generateRandomNumber(10);
        $fileName = $data['files']['fileName'] = $fileMeta['filename'].'.webp';
        $fileData = $data['files']['fileData'] = $this->convertImageToWebp($data['files']['fileData']);
        $response = array();
        //
        // read original file dimensions
        //
        list($width, $height) = $this->getDimension($fileData);
        //
        // add current file dimension to db
        //
        $data['files']['fileDimension'] = $width."x".$height;
        try {
            $this->conn->beginTransaction();
            $this->files->insert($data['files']);
            if ($postId) {
                $val = array();
                $val['postId'] = $postId;
                $val['fileId'] = $data['files']['fileId'];
                $this->postFiles->insert($val);
            }
            unset($data['files']['fileData']);
            $response['original'] = $data['files']; // original
            $response['thumbs'] = array();
            //
            // create thumbnails only for images with image width larger than 499px
            //
            if ($width > 499) { // thumbs
                $thumb = ['fileId' => $data['files']['fileId'], 'fileData' => $fileData, 'fileMeta' => $fileMeta];
                $thumbDetails = $this->createThumb($thumb, "80x55");
                $response['thumbs'][] = $thumbDetails;
                $thumbDetails = $this->createThumb($thumb, "160x110");
                $response['thumbs'][] = $thumbDetails;
                $thumbDetails = $this->createThumb($thumb, "320x160");
                $response['thumbs'][] = $thumbDetails;
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        return $response;
    }

    public function createThumb(array $data, $fileDimension) : array
    {        
        extract($data);
        list($width, $height) = explode("x", $fileDimension);
        $manager = new ImageManager(new Driver);
        $image = $manager->read($fileData) // load the image and resize it
            ->resize((int)$width, (int)$height, function ($constraint) {
                $constraint->aspectRatio(); // maintains the aspect ratio of the image
                $constraint->upsize();      // prevents upscaling smaller images
            });
        $fileData = (string)$image->toWebp(75); // 90 represents the quality percentage (1-100)
        $values = [
            'fileId' => $fileId,
            'fileDimension' => $fileDimension,
            'fileTag' => 'thumb',
            'fileName' => $fileMeta['filename'].'-thumb-'.$fileDimension.'.webp',
            'fileType' => 'image/webp',
            'fileSize' => strlen($fileData),
            'fileData' => $fileData,
        ];
        $this->files->insert($values);
        unset($values['fileData']);
        return $values;
    }

    public function delete(string $fileName)
    {
        $row = $this->findOneByName($fileName);
        if ($row) {
            $fileId = $row['id'];
            $foundPostId = $this->findFeaturedImagePostId($row['id']);
            try {
                $this->conn->beginTransaction();
                $this->files->delete(['fileId' => $fileId]);
                //
                // remove current post featured image if we it's equal with file id
                // 
                if ($foundPostId) {
                    $this->posts->update(['featuredImageId' => null], ['postId' => $foundPostId]);    
                }
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }
    }

    protected function convertImageToWebp(string $fileData)
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($fileData);
        return (string)$image->toWebp(90); // convert to webp format
    }

    protected function getDimension($fileData) : array
    {
        $image = imagecreatefromstring($fileData);
        $width = imagesx($image);
        $height = imagesy($image);
        return [$width, $height];
    }

    protected function findFeaturedImagePostId($fileId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'postId',
            ]
        );
        $select->from(['p' => 'posts']);
        $select->where(['p.featuredImageId' => $fileId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row ? $row['postId'] : false;
    }


}