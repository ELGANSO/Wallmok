<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;
use Ontic\Sync\Models\Transaction;
use Ontic\Sync\Models\TransactionInfo;

class TypeIdAttribute extends Attribute
{
    /** @var string[] */
    private $configurableAttributes;

    protected function getSourceValue($source)
    {
        // Nos quedamos con el valor del atributo "configurable_attributes"
        $this->configurableAttributes = @$source['configurable_attributes'];

        return parent::getSourceValue($source);
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        parent::onValueChanged($transaction, $newValue);

        $transaction->addAfterProductFullSaveAction(function(TransactionInfo $info)
        {
            $this->afterProductSave($info);
        });
    }

    protected function afterProductSave(TransactionInfo $info)
    {
        if(!$info->getIsProductNew())
        {
            // El producto no es nuevo, ya no se puede cambiar el tipo
            return false;
        }

        if ($info->getProduct()['type_id'] !== 'configurable')
        {
            // Si el producto no es configurable no hacemos nada
            return false;
        }

        if(!$this->configurableAttributes)
        {
            // No se nos ha pasado ningún valor para poder
            // saber los atributos que son configurables
            return false;
        }

        // Obtenemos los IDs de los atributos configurables
        $configurableAttributeIds = array_map([$this, 'getAttributeId'], $this->configurableAttributes);

        // Recargamos el producto
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($info->getProduct()->getId());

        // Obtenemos la instancia configurable del producto
        /** @var \Mage_Catalog_Model_Product_Type_Configurable $configurableProduct */
        $configurableProduct = $product->getTypeInstance();

        // Le asignamos los IDs de los atributos que van a ser configurables
        $configurableProduct->setUsedProductAttributeIds($configurableAttributeIds);

        // Obtenemos el array de atributos configurables y se lo asignamos al producto
        $configurableAttributesData = $configurableProduct->getConfigurableAttributesAsArray();
        $product->setData('configurable_attributes_data', $configurableAttributesData);
        $product->setData('can_save_configurable_attributes', true);

        // Guardamos el producto
        $product->save();

        return true;
    }

    protected function getAttributeId($attributeCode)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_product', $attributeCode);
    }
}