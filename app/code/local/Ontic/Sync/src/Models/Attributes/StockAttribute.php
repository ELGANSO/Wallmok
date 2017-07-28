<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;
use Ontic\Sync\Models\Transaction;
use Ontic\Sync\Models\TransactionInfo;

class StockAttribute extends IntAttribute
{
    protected function getDestinationValue(Mage_Catalog_Model_Product $destination)
    {
        return $this->getStockItem($destination)->getQty();
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        $transaction->addBeforeUpdateAction(
            function(TransactionInfo $info) use($newValue)
            {
                $product = $info->getProduct();
                $transaction = $info->getTransaction();

                if($info->getIsProductNew())
                {
                    // Se trata de un producto nuevo, así que le añadimos los datos
                    // de stock desde cero
                    $product->setData('stock_data', [
                        'manage_stock' => true,
                        'qty' => $newValue,
                        'is_in_stock' => $newValue > 0
                    ]);

                    // Solicitamos el guardado completo del producto
                    $transaction->requestFullSave();
                }
                else
                {
                    // Actualización. Cargamos el stockItem del producto
                    $stockItem = $this->getStockItem($product);

                    // Lo actualizamos con la cantidad recibida
                    $stockItem->setQty($newValue);
                    $stockItem->setIsInStock($newValue > 0);

                    // Y lo guardamos
                    $stockItem->save();
                }

                return true;
            }
        );
    }

    /**
     * Obtiene el stock item de un producto
     * @param Mage_Catalog_Model_Product $product
     * @return \Mage_CatalogInventory_Model_Stock_Item
     */
    protected function getStockItem(Mage_Catalog_Model_Product $product)
    {
        return Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
    }
}