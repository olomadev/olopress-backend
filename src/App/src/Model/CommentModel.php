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

class CommentModel
{
    private $conn;
    private $adapter;

    public function __construct(
        private TableGatewayInterface $postComments,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    )
    {
        $this->postComments = $postComments;
        $this->cache = $cache;
        $this->adapter = $postComments->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([

            'id' => 'commentId',
            'name',
            'email',
            'body' =>  new Expression("SUBSTRING_INDEX(body, ' ', 10)"),
            'published',
            'createdAt',
        ]);
        $select->from(['pc' => 'postComments']);
        $select->join(
            ['p' => 'posts'], 'p.postId = pc.postId',
            [
                'postTitle' => 'title'
            ],
            $select::JOIN_LEFT
        );
        $select->order(['createdAt DESC']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'postTitle',
            'name',
            'email',
            'body',
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

    public function update(array $data) : string
    {
        $commentId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->postComments->update($data['comments'], ['commentId' => $commentId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $commentId)
    {
        try {
            $this->conn->beginTransaction();
            $this->postComments->delete(['commentId' => $commentId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function deleteCache()
    {

    }

}
