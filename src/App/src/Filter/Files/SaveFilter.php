<?php

declare(strict_types=1);

namespace App\Filter\Files;

use App\Filter\InputFilter;
use Laminas\Filter\ToInt;
use Laminas\Validator\Uuid;
use App\Filter\Utils\ToFile;
use App\Validator\BlobFileData;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class SaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'postId',
            'required' => false,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $this->add([
            'name' => 'pageId',
            'required' => false,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $this->add([
            'name' => 'fileId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $this->add([
            'name' => 'fileName',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 160,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'fileSize',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $this->add([
            'name' => 'fileType',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'fileData',
            'required' => true,
            'filters' => [
                ['name' => ToFile::class],
            ],
            'validators' => [
                [
                    'name' => BlobFileData::class,
                    'options' => [
                        'operation' => HTTP_METHOD == 'POST' ? 'create' : 'update',
                        'max_allowed_upload' => 2097152,  // 2 mega bytes
                        'mime_types' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                    ],
                ]
            ]
        ]);  

        $this->setData($data);
    }
}
