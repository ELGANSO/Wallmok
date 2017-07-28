<?php

namespace Ontic\Sync\Models\Attributes;

class IntAttribute extends Attribute
{
    protected function areEqual($value1, $value2)
    {
        return ((int) $value1) === ((int) $value2);
    }
}