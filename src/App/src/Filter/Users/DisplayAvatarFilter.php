<?php

declare(strict_types=1);

namespace App\Filter\Users;

use App\Filter\InputFilter;
use Laminas\Validator\Uuid;

class DisplayAvatarFilter extends InputFilter
{
    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'userId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        
        $this->setData($data);
    }
}
