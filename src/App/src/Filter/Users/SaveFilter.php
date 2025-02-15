<?php

declare(strict_types=1);

namespace App\Filter\Users;

use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Filter\CollectionInputFilter;
use App\Filter\Utils\MbUcFirstFilter;
use App\Filter\Utils\EmailNormalizeFilter;
use App\Filter\Utils\ToFile;
use App\Validator\BlobFileData;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
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
        $this->adapter  = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'userId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        
        $this->add([
            'name' => 'email',
            'required' => false,
            'filters' => [
                ['name' => EmailNormalizeFilter::class],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => NoRecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'email',
                        'exclude'   => [
                            'field' => 'email',
                            'value' => $data['email'],
                        ],
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => HTTP_METHOD == 'POST' ? true : false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 8,
                        'max' => 16,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'active',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $this->add([
            'name' => 'emailActivation',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'firstname',
            'required' => false,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $objectFilter->add([
            'name' => 'lastname',
            'required' => false,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $objectFilter->add([
            'name' => 'jobTitle',
            'required' => false,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $objectFilter->add([
            'name' => 'themeColor',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 7,
                        'max' => 7,
                    ],
                ],
            ],
        ]);
        $this->add($objectFilter, 'userProfile');

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'image',
            'required' => false,
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
                        ],
                    ],
                ]
            ]
        ]);
        $this->add($objectFilter, 'avatar');
        
        // User Roles Input filter
        //
        $userRolesCollection = $this->filter->get(CollectionInputFilter::class);
        $userRolesInputFilter = $this->filter->get(InputFilter::class);
        $userRolesInputFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $userRolesCollection->setInputFilter($userRolesInputFilter);
        $this->add($userRolesCollection, 'userRoles');
        
        $this->setData($data);
    }
}
