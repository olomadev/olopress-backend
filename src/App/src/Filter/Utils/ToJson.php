<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

class ToJson extends AbstractFilter
{
    public function filter($value)
    {
        if (empty($value) || ! is_array($value)) {
            return "";
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

