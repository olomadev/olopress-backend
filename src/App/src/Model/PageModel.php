<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class PageModel
{
    private $conn;
    private $adapter;

    public function __construct(
        private TableGatewayInterface $pages,
        private TableGatewayInterface $files,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    )
    {
        $this->pages = $pages;
        $this->files = $files;
        $this->cache = $cache;
        $this->adapter = $pages->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'pageId',
            'route',
            'title',
            'keywords',
            'description',
            'publishStatus',
            'createdAt',
        ]);
        $select->from(['p' => 'pages']);
        $select->join(['s' => 'screenshots'], 'p.pageId = s.pageId', ['screenId', 'imageType'], $select::JOIN_LEFT);
        $select->order(['createdAt DESC']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'route',
            'title',
            'keywords',
            'description',
            'publishStatus',
        ]);
        $this->columnFilters->setData($get);
        $this->columnFilters->setSelect($select);

        if ($this->columnFilters->searchDataIsNotEmpty()) {
            $nest = $select->where->nest();
            foreach ($this->columnFilters->getSearchData() as $col => $words) {
                $nest = $nest->or->nest();
                foreach ($words as $str) {
                    $nest->or->like(new Expression($col), '%'.$str.'%');
                }
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect(
            $select,
            $this->adapter
        );
        $paginator = new Paginator($paginatorAdapter);
        return $paginator;
    }

    public function findOneById(string $pageId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'pageId',
            'route',
            'title',
            'keywords',
            'description',
            'contentJson',
            'contentHtml',
            'publishStatus',
            'createdAt',
        ]);
        $select->from(['p' => 'pages']);
        $select->where(['p.pageId' => $pageId]);
        //
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        if (false === $row) {
            return;
        }
        if (! empty($row['contentJson'])) {
            $row['contentJson'] = json_decode($row['contentJson'], true);
        }
        $statement->getResource()->closeCursor();
        //
        // page files
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([]);
        $select->from(['pf' => 'pageFiles']);
        $select->join(['f' => 'files'], 'pf.fileId = f.fileId', 
            [
                'fileName',
            ],
        $select::JOIN_LEFT);
        $select->where(['pf.pageId' => $pageId, 'f.fileTag' => 'original']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $pageFiles = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        $row['pageFiles'] = is_array($pageFiles) ? array_column($pageFiles, 'fileName') : array();
        return $row;
    }

    public function create(array $data)
    {
        $pageId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['pages']['pageId'] = $pageId;
            $data['pages']['createdAt'] = date("Y-m-d H:i:s");
            $this->pages->insert($data['pages']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $pageId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->pages->update($data['pages'], ['pageId' => $pageId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function publish(array $data)
    {
        $pageId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->pages->update($data['pages'], ['pageId' => $pageId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $pageId)
    {
        $pageFiles = $this->findPageFiles($pageId);
        try {
            $this->conn->beginTransaction();
            $this->pages->delete(['pageId' => $pageId]);
            foreach ($pageFiles as $fileId) {
                $this->files->delete(['fileId' => $fileId]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function findPageFiles(string $pageId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(['fileId']);
        $select->from(['pf' => 'pageFiles']);
        $select->where(['pf.pageId' => $pageId]);
        $select->group('pf.fileId');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        return $results ? array_column($results, 'fileId') : array();
    }

}
