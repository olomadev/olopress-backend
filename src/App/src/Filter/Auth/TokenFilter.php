<?php

declare(strict_types=1);

namespace App\Filter\Auth;

use App\Filter\InputFilter;
use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;

class TokenFilter extends InputFilter
{
    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);
        $this->setData($data);
    }
}
