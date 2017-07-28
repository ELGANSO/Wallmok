<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;
use Ontic\Sync\Models\Transaction;

class CategoryAttribute extends Attribute
{
    protected function getSourceValue($source)
    {
        $categoryNames = explode('->', $source[$this->getCode()]);

        $level = 2;
        $categoryIds = [];
        foreach($categoryNames as $categoryName)
        {
            if($id = $this->getCategoryId($categoryName, $level))
            {
                $categoryIds[] = $id;
            }
            $level++;
        }

        return $categoryIds;
    }

    protected function getDestinationValue(Mage_Catalog_Model_Product $destination)
    {
        return $destination->getCategoryIds();
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        $transaction->addAttributeUpdate('category_ids', $newValue);
        $transaction->requestFullSave();
    }

    protected function areEqual($value1, $value2)
    {
        // Comparación no estricta, porque puede variar el orden
        // de los valores en el array
        return $value1 == $value2;
    }

    protected function getCategoryId($name, $level)
    {
        $categoryIds = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter([
                [ 'attribute' => 'name', 'eq' => $name ]
            ])
            ->addAttributeToFilter([
                [ 'attribute' => 'level', 'eq' => $level ]
            ])
            ->getAllIds();

        if($categoryIds)
        {
            return $categoryIds[0];
        }

        return null;
    }
}