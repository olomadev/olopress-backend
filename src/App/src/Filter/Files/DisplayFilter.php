<?php

declare(strict_types=1);

namespace App\Filter\Files;

use App\Filter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class DisplayFilter extends InputFilter
{
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'fileName',
            'required' => true,
            'validators' => [
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'files',
                        'field'   => 'fileName',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        
        $this->setData($data);
    }
}
