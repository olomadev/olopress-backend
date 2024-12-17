<?php

declare(strict_types=1);

namespace App\Filter\Posts;

use Laminas\Filter\ToInt;
use Laminas\Validator\Uuid;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Filter\CollectionInputFilter;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
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
                        'table'   => 'posts',
                        'field'   => 'postId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => true,
        ]);
        $this->add($objectFilter, 'authorId');
        $this->add([
            'name' => 'title',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 220,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'permalink',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'content',
            'required' => true,
        ]);
        $this->add([
            'name' => 'published',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['published', 'pending', 'draft'],
                    ],
                ],
            ],
        ]);

        // Categories Input filter
        //
        $collection = $this->filter->get(CollectionInputFilter::class);
        $inputFilter = $this->filter->get(InputFilter::class);
        $inputFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $collection->setInputFilter($inputFilter);
        $this->add($collection, 'postCategories');

        // Tags Input filter
        //
        $collection = $this->filter->get(CollectionInputFilter::class);
        $inputFilter = $this->filter->get(InputFilter::class);
        $inputFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $collection->setInputFilter($inputFilter);
        $this->add($collection, 'tagCategories');

        $this->setData($data);
    }
}
