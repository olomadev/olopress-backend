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

class PostModel
{
    private $conn;
    private $posts;
    private $cache;
    private $adapter;
    private $columnFilters;

    /**
     * Constructor
     * 
     * @param TableGatewayInterface $posts object
     * @param TableGatewayInterface $rolePermissions object
     * @param StorageInterface $cache object
     * @param ColumnFilters object
     */
    public function __construct(
        TableGatewayInterface $posts,
        StorageInterface $cache,
        ColumnFiltersInterface $columnFilters
    )
    {
        $this->posts = $posts;
        $this->cache = $cache;
        $this->adapter = $posts->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    /**
     * Find all posts (Do cache)
     *
     * @return array
     */
    public function findAll() : array
    {
        $select = $this->posts->getSql()->select();
        $resultSet = $this->posts->selectWith($select);
        $result = array();
        foreach ($resultSet as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'authorId',
            'title',
            'permalink',
            'published',
            'createdAt',
        ]);
        $select->from(['p' => 'posts']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'authorId',
            'title',
            'permalink',
            'published',
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

    public function findOneById(string $postId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'postId',
            'authorId',
            'title',
            'permalink',
            'contentJson',
            'published',
            'createdAt',
        ]);
        $select->from(['p' => 'posts']);
        $select->where(['p.postId' => $postId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        // $statement->getResource()->closeCursor();
        return $row;
    }

    public function create(array $data)
    {
        $postId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['posts']['postId'] = $postId;
            $this->posts->insert($data['posts']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $postId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->posts->update($data['posts'], ['postId' => $postId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $postId)
    {
        try {
            $this->conn->beginTransaction();
            $this->posts->delete(['postId' => $postId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
