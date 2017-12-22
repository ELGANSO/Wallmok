<?php

use Ontic\Sync\Models\Attributes\Attribute;
use Ontic\Sync\Models\Attributes\CategoryAttribute;
use Ontic\Sync\Models\Attributes\FloatAttribute;
use Ontic\Sync\Models\Attributes\ImagesAttribute;
use Ontic\Sync\Models\Attributes\IntAttribute;
use Ontic\Sync\Models\Attributes\ParentSkuAttribute;
use Ontic\Sync\Models\Attributes\SelectAttribute;
use Ontic\Sync\Models\Attributes\StockAttribute;
use Ontic\Sync\Models\Attributes\StoreIdAttribute;
use Ontic\Sync\Models\Attributes\TypeIdAttribute;
use Ontic\Sync\Models\Transaction;

/** @noinspection PhpIncludeInspection */
require_once Mage::getModuleDir('', 'Ontic_Sync') . '/vendor/autoload.php';

class Ontic_Sync_Model_ProductUpdater extends Mage_Core_Model_Abstract
{
    /** @var Attribute[] */
    private $attributes;

    public function processAllUpdates()
    {
        // Procesamos todas las peticiones que no estén completas
        foreach($this->getIncompleteRequests() as $request)
        {
            // Inicialmente la ponemos en estado "procesando"
            $request->setStatus($request::Status_Processing)->save();

            // Procesamos todas las actualizaciones de la petición
            foreach($request->getAllPendingUpdates() as $update)
            {
                $this->processProductUpdate($update->getSku(), json_decode($update['data'], true));
                $update->setStatus($update::Status_Success);
                $update->save();
            }

            // Verificamos que la petición ya no tiene más actualizaciones
            // pendientes y la marcamos como completada
            if($request->getAllPendingUpdates()->count() === 0)
            {
                $request->setStatus($request::Status_Finished)->save();
            }
        }
    }

    public function processProductUpdate($sku, $data)
    {
        // Buscamos el SKU que se nos ha pasado en la base de datos de Magento
        // para determinar si se trata de una actualización o de un producto nuevo
        if($productId = Mage::getModel('catalog/product')->getIdBySku($sku))
        {
            // El producto ya existe, realizamos la actualización
            $this->updateProduct($productId, $data);
        }
        else
        {
            // Es un producto nuevo, lo damos de alta
            $this->createProduct($sku, $data);
        }
    }

    /**
     * @return Ontic_Sync_Model_Product_Update_Request[]
     */
    protected function getIncompleteRequests()
    {
        // Devolvemos primero las peticiones que se estén procesando
        $processingRequests = Mage::getModel('onticsync/product_update_request')
            ->getCollection()
            ->addFieldToFilter('status', [ 'eq' => Ontic_Sync_Model_Product_Update_Request::Status_Processing ]);

        foreach($processingRequests as $request)
        {
            yield $request;
        }

        // Y a continuación las pendientes de procesar
        $pendingRequests = Mage::getModel('onticsync/product_update_request')
            ->getCollection()
            ->addFieldToFilter('status', [ 'eq' => Ontic_Sync_Model_Product_Update_Request::Status_Pending ]);

        foreach($pendingRequests as $request)
        {
            yield $request;
        }
    }

    protected function createProduct($sku, $data)
    {
        $product = Mage::getModel('catalog/product');

        // Añadimos algunos datos básicos de producto
        $product->setData('store_id', $this->getStoreId(@$data['store_id']));
        $product->setData('sku', $sku);
        $product->setData('attribute_set_id', 4); // Default
        $product->setData('website_ids', $this->getAllWebsiteIds()); // Por defecto le asignamos todos los sitios

        // Añadimos el resto de datos recibidos
        $transaction = new Transaction($product);
        $transaction->requestFullSave();
        foreach ($this->getAttributes() as $attribute)
        {
            $attribute->update($transaction, $data, $product);
        }


        $transaction->commit();
    }

    protected function updateProduct($id, $data)
    {
        /** @var \Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')
            ->setData('store_id', $this->getStoreId(@$data['store_id']))
            ->load($id);

        $transaction = new Transaction($product);
        foreach($this->getAttributes() as $attribute)
        {
            $attribute->update($transaction, $data, $product);
        }

        $transaction->commit();
    }

    protected function getAttributes()
    {
        if($this->attributes === null)
        {
            $this->attributes = [
                new TypeIdAttribute('type_id'),
                new Attribute('name'),
                new Attribute('description'),
                new Attribute('short_description'),
                new SelectAttribute('size'),
                new SelectAttribute('guia_tallas'),
                new SelectAttribute('season'),
                new IntAttribute('status'),
                new IntAttribute('visibility'),
                new FloatAttribute('weight'),
                new Attribute('composicion_textil'),
                new Attribute('lavado'),
                new FloatAttribute('price'),
                new FloatAttribute('special_price'),
                new Attribute('special_from_date'),
                new Attribute('special_to_date'),
                new SelectAttribute('tax_class_id'),
                new Attribute('news_from_date'),
                new Attribute('news_to_date'),
                new StockAttribute('qty'),
                new CategoryAttribute('category'),
                new ImagesAttribute('images'),
                new ParentSkuAttribute('parent_sku'),
            ];
        }

        return $this->attributes;
    }

    protected function getAllWebsiteIds()
    {
        $websiteIds = [];

        /** @var \Mage_Core_Model_Website $website */
        foreach(Mage::app()->getWebsites() as $website)
        {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    protected function getStoreId($storeCode)
    {
        if(!$storeCode)
        {
            return 0;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        return (int) Mage::getModel('core/store')->load($storeCode, 'code')->getId();
    }
}