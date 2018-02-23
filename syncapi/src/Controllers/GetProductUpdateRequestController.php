<?php

namespace Ontic\SyncApi\Controllers;

use Mage;
use Ontic\SyncApi\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GetProductUpdateRequestController extends BaseController
{
    function defaultAction()
    {
        if(!($requestId = $this->getParameter('requestId')))
        {
            return new Response('400 Bad Request', 400);
        }

        /** @var \Ontic_Sync_Model_Product_Update_Request $request */
        $request = Mage::getModel('onticsync/product_update_request')
            ->load($requestId);

        if($request->isObjectNew())
        {
            return new Response('404 Not Found', 404);
        }

        $data['status'] = $request->getStatusCode();
        if($request['status'] == $request::Status_Finished)
        {
            foreach ($request->getAllUpdates() as $update)
            {
                $updateData = json_decode($update['data'], true);
                $updateData['success'] = ($update['status'] == $update::Status_Success);
                $data['updates'][] = $updateData;
            }
        }

        return new JsonResponse($data, 200);
    }
}