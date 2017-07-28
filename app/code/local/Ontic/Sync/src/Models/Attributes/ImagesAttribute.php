<?php

namespace Ontic\Sync\Models\Attributes;

use Mage;
use Mage_Catalog_Model_Product;
use Mage_Catalog_Model_Product_Attribute_Backend_Media;
use Mage_Catalog_Model_Resource_Eav_Attribute;
use Ontic\Sync\Models\Transaction;
use Ontic\Sync\Models\TransactionInfo;

class ImagesAttribute extends Attribute
{
    protected function getSourceValue($source)
    {
        $value = [];

        foreach($source[$this->getCode()] as $imageName)
        {
            $imagePath = $this->getSourceImagePath($imageName);
            $value[$imagePath] = md5_file($imagePath);
        }

        return $value;
    }

    protected function getDestinationValue(Mage_Catalog_Model_Product $destination)
    {
        $value = [];

        foreach($destination->getMediaGalleryImages() as $image)
        {
            $imagePath = $image['path'];
            $value[$imagePath] = md5_file($imagePath);
        }

        return $value;
    }

    protected function areEqual($value1, $value2)
    {
        return array_values($value1) == array_values($value2);
    }

    protected function onValueChanged(Transaction $transaction, $newValue)
    {
        $transaction->addBeforeUpdateAction(
            function(TransactionInfo $info) use($newValue)
            {
                $product = $info->getProduct();
                $transaction = $info->getTransaction();

                // Borramos las imágenes que pueda tener el producto
                $this->deleteProductImages($product);

                // Y Añadimos las nuevas
                $mediaAttributes = [ 'image', 'small_image', 'thumbnail' ];
                foreach(array_keys($newValue) as $imagePath)
                {
                    if (file_exists($imagePath))
                    {
                        $product->addImageToMediaGallery($imagePath, $mediaAttributes, false, false);
                        $mediaAttributes = [];
                    }
                }

                // Obligamos a que se realice un guardado completo del producto
                $transaction->requestFullSave();

                return true;
            }
        );
    }

    protected function deleteProductImages(Mage_Catalog_Model_Product $product)
    {
        if(!$product->getId())
        {
            // Es un producto que todavía no se ha guardado, así que no
            // puede tener imágenes todavía
            return;
        }

        // Obtenemos una lista de las imágenes del producto
        $mediaApi = Mage::getModel('catalog/product_attribute_media_api');
        $existingImages = $mediaApi->items($product->getId());

        // Y las vamos eliminando
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $gallery */
        $gallery = $product->getTypeInstance()->getSetAttributes()['media_gallery'];
        /** @var Mage_Catalog_Model_Product_Attribute_Backend_Media $galleryBackend */
        $galleryBackend = $gallery->getBackend();
        foreach ($existingImages as $image)
        {
            if ($galleryBackend->getImage($product, $image['file']))
            {
                $galleryBackend->removeImage($product, $image['file']);
            }
        }
    }

    protected function getSourceImagePath($imageName)
    {
        return Mage::getBaseDir('media') . '/import/' . $imageName;
    }
}