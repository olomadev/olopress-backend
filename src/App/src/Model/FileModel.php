<?php
declare(strict_types=1);

namespace App\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

use function generateRandomAlphaLowerCase, createGuid;

class FileModel
{
    private $conn;
    private $adapter;

    public function __construct(   
        private TableGatewayInterface $files
    )
    {
        $this->files = $files;
        $this->adapter = $files->getAdapter();
        $this->conn = $this->adapter->getDriver()->getConnection();
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
        return  $row;
    }

    public function create(array $data) : string
    {
        $fileId = createGuid();
        $fileMeta = pathinfo($data['files']['fileName']);
        $data['files']['fileName'] = $fileMeta['filename'].'-'.generateRandomNumber(10).'.'.$fileMeta['extension'];
        try {
            $this->conn->beginTransaction();
            $data['files']['fileId'] = $fileId;
            $this->files->insert($data['files']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        return $data['files']['fileName'];
    }

    public function delete(string $fileName)
    {
        try {
            $this->conn->beginTransaction();
            $this->files->delete(['fileName' => $fileName]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

}