<?php

declare(strict_types=1);

namespace App\Filter\Files;

use App\Filter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class DeleteFilter extends InputFilter
{
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'postId',
            'required' => false,
        ]);
        $this->add([
            'name' => 'fileName',
            'required' => true,
        ]);
       
        $this->setData($data);
    }
}
