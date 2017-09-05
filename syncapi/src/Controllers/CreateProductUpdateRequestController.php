<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Symfony\Component\HttpFoundation\Response;

class CreateProductUpdateRequestController extends BaseController
{
    /**
     * @return Response
     */
    function defaultAction()
    {
        // Decodificamos el cuerpo de la petición
        if(!($data = json_decode($this->getRequest()->getContent(), true)))
        {
            return new Response('400 Bad Request<br>Invalid JSON', 400);
        }

        $index = 0;
        /** @var \Zend_Db_Adapter_Pdo_Abstract $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        $request = Mage::getModel('onticsync/product_update_request');
        $request->setData('created_at', now());
        $request->save();
        $updater = Mage::getModel('onticsync/productUpdater');
        foreach($data as $productData)
        {
            if(!isset($productData['sku']))
            {
                return new Response('400 Bad Request<br>Missing SKU at index ' . $index, 400);
            }

            $sku = $productData['sku'];

            $update = Mage::getModel('onticsync/product_update');
            $update->setData('request_id', (int) $request->getId());
            $update->setData('sku', $sku);
            $update->setData('data', json_encode($productData));
            $auto = $productData['auto'];
            //Si es una sincro de Automatica, lo actualizo directamente
            //En otro caso, se guarda en la cola
            if(isset($auto) &&  $auto == true)
            {
            	$updater->processProductUpdate($sku, $productData);
            	$update->setStatus($update::Status_Success);
            }
            $update->save();

            $index++;
        }
        $connection->commit();

        // Devolvemos la respuesta
        return new Response(json_encode(['request_id' => (int) $request->getId()]), 202);
    }
}