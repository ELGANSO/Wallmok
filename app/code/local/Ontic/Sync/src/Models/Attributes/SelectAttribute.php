<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;

class SelectAttribute extends Attribute
{
    protected function getSourceValue($source)
    {
        $magentoAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $this->getCode());

        foreach($magentoAttribute->getSource()->getAllOptions() as $option)
        {
            if($option['label'] === $source[$this->getCode()])
            {
                return $option['value'];
            }
        }

        return null;
    }
}