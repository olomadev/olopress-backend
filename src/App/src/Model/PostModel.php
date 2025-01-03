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

use generateRandomAlphaLowerCase;

class PostModel
{
    private $conn;
    private $adapter;

    public function __construct(
        private TableGatewayInterface $posts,
        private TableGatewayInterface $postTags,
        private TableGatewayInterface $postCategories,
        private TableGatewayInterface $tags,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    )
    {
        $this->posts = $posts;
        $this->postTags = $postTags;
        $this->postCategories = $postCategories;
        $this->tags = $tags;
        $this->cache = $cache;
        $this->adapter = $posts->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'postId',
            'authorId' => new Expression("JSON_OBJECT('id', u.userId, 'name', CONCAT(u.firstname, ' ', u.lastname))"),
            'title',
            'permalink',
            'contentHtml',
            'publishStatus',
            'createdAt',
        ]);
        $select->from(['p' => 'posts']);
        $select->join(['u' => 'users'], 'u.userId = p.authorId', ['firstname', 'lastname'], $select::JOIN_LEFT);
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

    public function findOneById(string $postId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'postId',
            'authorId' => new Expression("JSON_OBJECT('id', u.userId, 'name', CONCAT(u.firstname, ' ', u.lastname))"),
            'title',
            'permalink',
            'contentJson',
            'publishStatus',
            'publishedAt',
            'createdAt',
        ]);
        $select->from(['p' => 'posts']);
        $select->join(['u' => 'users'], 'u.userId = p.authorId', ['firstname', 'lastname'], $select::JOIN_LEFT);
        $select->where(['p.postId' => $postId]);
        //
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        if (! empty($row['contentJson'])) {
            $row['contentJson'] = json_decode($row['contentJson'], true);
        }
        $statement->getResource()->closeCursor();
        //
        // tags
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'tagId',
            ]
        );
        $select->from(['pt' => 'postTags']);
        $select->join(['t' => 'tags'], 'pt.tagId = t.tagId', ['name' => 'tagName'], $select::JOIN_LEFT);
        $select->where(['postId' => $postId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $postTags = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        $row['tags'] = $postTags;
        //
        // categories
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'categoryId',
            ]
        );
        $select->from(['pc' => 'postCategories']);
        $select->join(['c' => 'categories'], 'pc.categoryId = c.categoryId', ['name'], $select::JOIN_LEFT);
        $select->where(['postId' => $postId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $postCategories = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        $row['categories'] = $postCategories;
        return $row;
    }

    public function create(array $data) : string
    {
        $postId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['posts']['postId'] = $postId;
            $data['posts']['permalink'] = $this->updatePermalink($postId, $data['posts']['permalink']);
            $data['posts']['createdAt'] = date("Y-m-d H:i:s");
            $this->posts->insert($data['posts']);
            $this->saveItems($data);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        return $data['posts']['permalink'];
    }

    public function update(array $data) : string
    {
        $postId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['posts']['permalink'] = $this->updatePermalink($postId, $data['posts']['permalink']);
            $this->posts->update($data['posts'], ['postId' => $postId]);
            $this->saveItems($data);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        return $data['posts']['permalink'];
    }

    public function publish(array $data)
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
            $this->postTags->delete(['postId' => $postId]);
            $this->postCategories->delete(['postId' => $postId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    protected function saveItems(array $data)
    {
        $postId = $data['id'];
        $this->postTags->delete(['postId' => $postId]);
        $this->postCategories->delete(['postId' => $postId]);
        if (! empty($data['tags'])) {
            $findExistingTagIds = $this->findExistingTagsInDb($data['tags']);
            foreach ($data['tags'] as $val) {
                $tags = array();
                $tags['postId'] = $postId;
                $tags['tagId'] = $val['id'];
                if (! in_array($val['id'], $findExistingTagIds)) { // if it's a new tag we need insert to db
                    $this->tags->insert(['tagId' => $val['id'], 'tagName' => trim($val['name'])]);
                }
                $this->postTags->insert($tags);
            }
        }
        if (! empty($data['categories'])) {
            foreach ($data['categories'] as $val) {
                $cats = array();
                $cats['postId'] = $postId;
                $cats['categoryId'] = $val['id'];
                $this->postCategories->insert($cats);
            }
        }
    }

    private function updatePermalink(string $postId, string $permalink)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'permalink',
        ]);
        $select->from(['p' => 'posts']);
        $select->where->equalTo('permalink', $permalink);
        $select->where->notEqualTo('postId', $postId);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        if ($row) {
            return $row['permalink'].'-'.generateRandomAlphaLowerCase(6);
        }
        return $permalink;
    }

    private function findExistingTagsInDb($tags) : array
    {
        $tagIds = array_column($tags, 'id');
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'tagId',
            ]
        );
        $select->from(['t' => 'tags']);
        $select->where->in('tagId', $tagIds);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        if (! empty($results[0]) && ! empty($results[0]['id'])) {
            return array_column($results, 'id');    
        }
        return array();
    }

    private function deleteCache()
    {
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':findTags');
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':findCategories');
    }

}
