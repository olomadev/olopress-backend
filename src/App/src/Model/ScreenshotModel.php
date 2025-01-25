<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ScreenshotModel
{
    private $adapter;

    public function __construct(
        private TableGatewayInterface $screenshots,
        private StorageInterface $cache
    )
    {
        $this->cache = $cache;
        $this->screenshots = $screenshots;
        $this->adapter = $screenshots->getAdapter();
    }

    public function findOneById(string $id)
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__.':'.$id;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        } 
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'imageType',
                'imageData',
            ]
        );
        $select->from(['s' => 'screenshots']);
        $select->where->equalTo('s.postId', $id);
        $select->where->or->equalTo('s.pageId', $id);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        if ($row) {
            $this->cache->setItem($key, $row);    
        }
        return  $row;
    }


}
