<?php

declare(strict_types=1);

namespace App\Filter\Pages;

use Laminas\Filter\ToInt;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use App\Filter\InputFilter;
use App\Filter\Utils\ToJson;
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
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);

        $this->add([
            'name' => 'route',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 60,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'title',
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
            'name' => 'keywords',
            'required' => false,
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
            'name' => 'description',
            'required' => false,
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
            'name' => 'contentJson',
            'required' => true,
            'filters' => [
                ['name' => ToJson::class],
            ],
        ]);

        $this->add([
            'name' => 'contentHtml',
            'required' => true,
        ]);

        $this->add([
            'name' => 'publishStatus',
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

        $this->setData($data);
    }

}
