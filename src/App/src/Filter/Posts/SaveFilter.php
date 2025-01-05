<?php

declare(strict_types=1);

namespace App\Filter\Posts;

use Laminas\Filter\ToInt;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Filter\CollectionInputFilter;
use App\Filter\Utils\ToJson;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

use createGuid;

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
        $data['tags'] = $this->filterTags($data['tags']);

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
        $this->add([
            'name' => 'publishedAt',
            'required' => false,
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d H:i:s',
                        'strict' => true,
                    ],
                ],
            ]
        ]);
        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => false,
        ]);
        $this->add($objectFilter, 'featuredImageId');

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
        $this->add($collection, 'categories');

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
        $inputFilter->add([
            'name' => 'name',
            'required' => false,
        ]);
        $collection->setInputFilter($inputFilter);
        $this->add($collection, 'tags');

        $this->setData($data);
    }

    /**
     * Problem: New tags do not have a guid id, but old tags in the database do. 
     * So when the user sends a new tag, he/she sends a tag without an id.
     *
     * Solution: We recreate the raw input and if the tag id exists we create a new one.
     * 
     * @param  array $tags tags with/without ids
     * @return array new tags with id
     */
    private function filterTags($tags)
    {
        $i = 0;
        $newTagValues = array(); // if tag has not got id then add
        foreach ((array)$tags as $key => $val) {
            if (empty($val['id'])) {
                $newTagValues[$i] = ['id' => createGuid(), 'name' => $val];
            } else {
                $newTagValues[$i] = $val;
            }
            ++$i;
        }
        return $newTagValues;
    }

}
