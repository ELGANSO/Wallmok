<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;
use Ontic\Sync\Models\Transaction;
use Ontic\Sync\Models\TransactionInfo;

class ParentSkuAttribute extends Attribute
{
    /** @var string */
    private $parentSku;

    protected function getSourceValue($source)
    {
        // Nos guardamos el valor del atributo
        $this->parentSku = parent::getSourceValue($source);

        return $this->parentSku;
    }

    protected function getDestinationValue(Mage_Catalog_Model_Product $destination)
    {
        $parentIds = Mage::getModel('catalog/product_type_configurable')
            ->getParentIdsByChild($destination->getId());

        if(count($parentIds) === 0)
        {
            return null;
        }

        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter([
                [ 'attribute' => 'entity_id', 'eq' => $parentIds[0] ]
            ])
            ->getFirstItem()['sku'];
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        $transaction->addBeforeUpdateAction(function (TransactionInfo $info)
        {
            if($info->getIsProductNew())
            {
                // El producto todavía no tiene ID, tendremos que esperar
                // a que se guarde para poder realizar operaciones con
                // su producto padre
                $info->getTransaction()->addAfterProductFullSaveAction(function(TransactionInfo $info)
                {
                    return $this->updateRelationship($info, $this->parentSku);
                });

                return false;
            }

            return $this->updateRelationship($info, $this->parentSku);
        });
    }

    protected function updateRelationship(TransactionInfo $info, $parentSku)
    {
        // Si el nuevo SKU del padre es null, desvinculamos el producto hijo
        // de su producto padre configurable
        if($parentSku === null)
        {
            $this->unlinkFromParent($info->getProduct()->getId());
            return true;
        }

        // Vinculamos el producto hijo con su nuevo padre
        $this->linkToParentSku($info->getProduct()->getId(), $parentSku);
        return true;
    }

    protected function linkToParentSku($childId, $parentSku)
    {
        // Obtenemos el ID del padre
        $parentId = Mage::getModel('catalog/product')->getIdBySku($parentSku);

        // Obtenemos los IDs de los productos hijo asociados
        $childrenIds = $this->getUsedProductIds($parentId);

        // Le añadimos el nuevo ID de producto
        $childrenIds[] = $childId;

        // Y vinculamos el producto padre con todos los productos hijo
        $this->setUsedProductIds($parentId, $childrenIds);
    }

    protected function unlinkFromParent($childId)
    {
        $parentIds = Mage::getModel('catalog/product_type_configurable')
            ->getParentIdsByChild($childId);

        if(count($parentIds) === 0)
        {
            // No está asociado a ningún configurable, salimos directamente
            return null;
        }

        $parentId = $parentIds[0];

        // Obtenemos todos los hijos del producto padre
        $childrenIds = $this->getUsedProductIds($parentId);

        // Quitamos el producto actual de la lista de hijos
        $childrenIds = array_diff($childrenIds, [ $childId ]);

        // Y volvemos a vincular el resto con el padre
        $this->setUsedProductIds($parentId, $childrenIds);
    }

    /**
     * Obtiene los hijos de un producto configurable
     * @param int $parentId
     * @return int[]
     */
    protected function getUsedProductIds($parentId)
    {
        // Cargamos el producto padre
        /** @var Mage_Catalog_Model_Product $parent */
        $parent = Mage::getModel('catalog/product')->load($parentId);
        /** @var \Mage_Catalog_Model_Product_Type_Configurable $configurableParent */
        $configurableParent = $parent->getTypeInstance();

        // Devolvemos los IDs de los productos hijo asociados
        return $configurableParent->getUsedProductIds();
    }

    /**
     * Establece los hijos de un producto configurable
     * @param int $parentId
     * @param int[] $childrenIds
     */
    protected function setUsedProductIds($parentId, $childrenIds)
    {
        /** @var Mage_Catalog_Model_Product $parent */
        $parent = Mage::getModel('catalog/product')->load($parentId);

        /** @var \Mage_Catalog_Model_Resource_Product_Type_Configurable $parentResource */
        $parentResource = Mage::getResourceModel('catalog/product_type_configurable')->load($parent, $parent->getId());
        $parentResource->saveProducts($parent, $childrenIds);
    }
}