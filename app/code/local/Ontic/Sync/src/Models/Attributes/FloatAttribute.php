<?php

namespace Ontic\Sync\Models\Attributes;

class FloatAttribute extends Attribute
{
    protected function areEqual($value1, $value2)
    {
        return ((float) $value1) === ((float) $value2);
    }
}