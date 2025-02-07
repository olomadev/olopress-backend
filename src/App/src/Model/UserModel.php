<?php
declare(strict_types=1);

namespace App\Model;

use function generateRandomNumber, createGuid;

use Exception;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Cache\Storage\StorageInterface;

class UserModel
{
    private $conn;
    private $adapter;

    public function __construct(
        private TableGatewayInterface $users,
        private TableGatewayInterface $userProfile,
        private TableGatewayInterface $userRoles,
        private TableGatewayInterface $userAvatars,
        private StorageInterface $cache,
        private ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $users->getAdapter();
        $this->users = $users;
        $this->userProfile = $userProfile;
        $this->userRoles = $userRoles;
        $this->userAvatars = $userAvatars;
        $this->columnFilters = $columnFilters;
        $this->cache = $cache;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'userId',
            'email',
            'active',
            'createdAt',
        ]);
        $select->from(['u' => 'users']);
        $select->join(
            ['up' => 'userProfile'], 'up.userId = u.userId',
            [
                'firstname',
                'lastname',
            ],
            $select::JOIN_LEFT
        );
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('firstname', 'up.firstname');
        $this->columnFilters->setAlias('lastname', 'up.lastname');
        $this->columnFilters->setColumns([
            'firstname',
            'lastname',
            'email',
            'active',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'firstname',
                'lastname',
                'email',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'active',
            ]
        );
        $this->columnFilters->setSelect($select);
        $this->columnFilters->setData($get);

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
        if ($this->columnFilters->likeDataIsNotEmpty()) {
            foreach ($this->columnFilters->getLikeData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->like(new Expression($column), '%'.$val.'%');
                    }
                    $nest->unnest();
                } else {
                    $select->where->like(new Expression($column), '%'.$value.'%');
                }
            }   
        }
        if ($this->columnFilters->whereDataIsNotEmpty()) {
            foreach ($this->columnFilters->getWhereData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->equalTo(new Expression($column), $val);
                    }
                    $nest->unnest();
                } else {
                    $select->where->equalTo(new Expression($column), $value);
                }
            }
        }
        // date filters
        // 
        $this->columnFilters->setDateFilter('createdAt');
        // orders
        // 
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order($order);
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

    public function findOneById(string $userId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'userId',
                'userId',
                'email',
                'active',
                'emailActivation',
                'lastLogin',
                'createdAt',
            ]
        );
        $select->from(['u' => 'users']);
        $select->join(
            ['up' => 'userProfile'], 'up.userId = u.userId',
            [
                'firstname',
                'lastname',
                'locale' => new Expression("JSON_OBJECT('id', l.langId, 'name', l.langName)"),
                'jobTitle',
                'themeColor',
            ],
            $select::JOIN_LEFT
        );
        $select->join(['l' => 'languages'], 'up.locale = l.langId', [], $select::JOIN_LEFT);
        $select->join(['ua' => 'userAvatars'], 'ua.userId = u.userId',
            [
                'avatar' => new Expression("JSON_OBJECT('image', CONCAT('data:image/png;base64,', TO_BASE64(avatarImage)))"),
            ],
        $select::JOIN_LEFT);
        $select->where(['u.userId' => $userId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $userProfile = array();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        if ($row) {
            //
            // user profile
            // 
            $userProfile['firstname'] = $row['firstname'];
            $userProfile['lastname'] = $row['lastname'];
            $userProfile['jobTitle'] = $row['jobTitle'];
            $userProfile['locale'] = $row['locale'];
            $userProfile['themeColor'] = $row['themeColor'];
            //
            // user roles
            // 
            $sql    = new Sql($this->adapter);
            $select = $sql->select();
            $select->columns(
                [
                    'id' => 'roleId',
                ]
            );
            $select->from('userRoles');
            $select->join(['r' => 'roles'], 'r.roleId = userRoles.roleId',
                [
                    'name' => 'roleName'
                ],
            $select::JOIN_LEFT);
            $select->where(['userId' => $userId]);
            // echo $select->getSqlString($this->adapter->getPlatform());
            // die;
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $userRoles = iterator_to_array($resultSet);
            $statement->getResource()->closeCursor();
            $newUserRoles = array();
            foreach ($userRoles as $key => $val) {
                $newUserRoles[$key] = ["id" => $val['id'], "name" => $val['name']];
            }
            $row['userRoles'] = $newUserRoles;
            $row['userProfile'] = json_encode($userProfile);
        }
        return $row;
    }

    public function findOneByUsername(string $username)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'userId',
                'userId',
                'email',
                'active',
            ]
        );
        $select->from('users');
        $select->join(
            ['up' => 'userProfile'], 'up.userId = u.userId',
            [
                'firstname',
                'lastname',
                'jobTitle',
                'locale' => new Expression("JSON_OBJECT('id', l.langId, 'name', l.langName)"),
                'themeColor',
            ],
            $select::JOIN_LEFT
        );
        $select->where(['u.email' => $username]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row;
    }

    public function findAvatarById(string $userId)
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.$userId.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'mimeType',
                'avatarImage',             
            ]
        );
        $select->from('userAvatars');
        $select->where(['userId' => $userId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        if ($row) {
            $this->cache->setItem($key, $row);
        }
        return $row;
    }

    public function create(array $data)
    {
        $userId = $data['id'];
        $data['users']['userId'] = $userId;
        try {
            $this->conn->beginTransaction();
            if (! empty($data['users']['password'])) {
                $data['users']['password'] = password_hash($data['users']['password'], PASSWORD_DEFAULT, ['cost' => 10]);
            }
            $this->users->insert($data['users']);
            $data['userProfile']['userProfileId'] = createGuid();
            $data['userProfile']['userId'] = $userId;
            $this->userProfile->insert($data['userProfile']);
            if (! empty($data['userRoles'])) {
                foreach ($data['userRoles'] as $val) {
                    $this->userRoles->insert(['userId' => $userId, 'roleId' => $val['id']]);
                }
            }
            if (! empty($data['avatar']['image'])) {
                $this->userAvatars->insert(['userId' => $userId, 'avatarImage' => $data['avatar']['image']]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $userId = $data['id'];
        try {
            $this->conn->beginTransaction();
            if (! empty($data['users']['password'])) {
                $data['users']['password'] = password_hash($data['users']['password'], PASSWORD_DEFAULT, ['cost' => 10]);
            } else {
                unset($data['users']['password']);
            }
            $data['users']['updatedAt'] = date('Y-m-d H:i:s');
            $this->users->update($data['users'], ['userId' => $userId]);
            if (! empty($data['userRoles'])) {
                $this->userRoles->delete(['userId' => $userId]);
                foreach ($data['userRoles'] as $val) {
                    $this->userRoles->insert(['userId' => $userId, 'roleId' => $val['id']]);
                }
            }
            $this->userProfile->update($data['userProfile'], ['userId' => $userId]);
            $this->userAvatars->delete(['userId' => $userId]);
            if (! empty($data['avatar']['image'])) { // let's read mime type safely
                $mimeType = finfo_buffer(
                    finfo_open(),
                    $data['avatar']['image'],
                    FILEINFO_MIME_TYPE
                );
                $this->userAvatars->insert(
                    [
                        'userId' => $userId, 
                        'mimeType' => $mimeType,
                        'avatarImage' => $data['avatar']['image']
                    ]
                );
                $this->deleteCache($userId);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $userId)
    {
        try {
            $this->conn->beginTransaction();
            $this->users->delete(['userId' => $userId]);
            $this->userRoles->delete(['userId' => $userId]);
            $this->userAvatars->delete(['userId' => $userId]);
            $this->deleteCache($userId);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function generateResetPassword(string $username) : string
    {
        $resetCode = generateRandomNumber(6);
        $this->cache->setItem((string)$resetCode, $username, 600); // wait confirmation for 10 minutes
        return $resetCode;
    }

    public function checkResetCode(string $resetCode)
    {
        return $this->cache->getItem($resetCode);
    }

    public function updatePasswordByResetCode(string $resetCode, string $newPassword)
    {
        $username = $this->cache->getItem($resetCode);
        if ($username) {
            $password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 10]);
            try {
                $this->conn->beginTransaction();
                $this->users->update(['password' => $password, 'emailActivation' => 1], ['email' => $username]);
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }
        return $username;
    }

    public function updatePasswordById(string $userId, string $newPassword)
    {
        $password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 10]);
        try {
            $this->conn->beginTransaction();
            $this->users->update(['password' => $password], ['userId' => $userId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

    private function deleteCache(string $userId)
    {
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':'.$userId.':'.__FUNCTION__);
    }
}
