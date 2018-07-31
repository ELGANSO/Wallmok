<?php

/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 * @author      David Slater
 */
class Interactiv4_RedsysPro_PaymentController extends Mage_Core_Controller_Front_Action {

    /**
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     *
     * @var Mage_Sales_Model_Quote 
     */
    protected $_quote = null;

    protected function _expireAjax() {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    /**
     *
     * @return Interactiv4_RedsysPro_Model_Standard
     */
    public function getStandard() {
        return Mage::getSingleton('i4redsyspro/standard');
    }

    /**
     *
     */
    public function callbackAction() {
        $this->_log(__METHOD__);
        $params = $this->getRequest()->getParams();
        $this->_log("------------ START PAYMENT RESPONSE (PARAMS) -----------");
        $this->_log($params);
        $this->_log("------------- END PAYMENT RESPONSE (PARAMS) ------------");
        $this->_log("");
        $this->successAction();
        return;
    }

    /**
     * @throws Exception
     * @throws bool
     */
    public function notificationAction() {
        $this->_log(__METHOD__);

        $this->_logRequestRawBody();

        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_setHTTPInternalServerError($errorMessage);
        }

        $isSoapNotification = $this->_isSoapNotification();
        $checkSignature = $isSoapNotification ? $this->_checkSoapSignature() : $this->_checkSignature();

        if (!$checkSignature) {
            $this->_log('Notification error. Something was wrong with the signature.');
            $this->_setNotificationResponseContent(false);
            $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_log("");
            $this->_setHTTPInternalServerError($this->_getHelper()->__("Notification error: There was a problem with the received signature"));
            return;
        }

        if ($this->_isOrderAlreadyPaid() || $this->_getSavedResponse()) {
            $this->_setNotificationResponseContent();
            $this->_log('The IPN has already been processed in the success action.');
            $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_log("");
            return;
        }

        $params = $this->getRequest()->getParams();

        $signatureAlgorithm = $this->getStandard()->getConfigData('signaturealgorithm', $order->getStore());

        if (!$isSoapNotification && $signatureAlgorithm == Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM) {
            $dsSignature = $params['Ds_Signature'];
            $aux = json_decode($this->_decodeBase64Params($params['Ds_MerchantParameters']), true);
            unset($params['Ds_MerchantParameters']);
            unset($params['Ds_Signature']);
            $params = array_merge($params, $aux);
            $params['Ds_Signature'] = $dsSignature;
        }

        $this->_log("---------- START PAYMENT RESPONSE (IPN PARAMS) ----------");
        $this->_log($params);
        $this->_log("----------- END PAYMENT RESPONSE (IPN PARAMS) -----------");
        $this->_log("");

        $this->_saveResponse($params);

        if (!isset($params['Ds_Response'])) {
            $this->_setNotificationResponseContent(false);
            $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_log("");
            return;
        }

        $this->_getQuote()->setIsActive(false)->save();

        $stateObject = new Varien_Object();

        if (!$this->_processResponseError($params)) {
            $this->_setNotificationResponseContent(false);
            $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_log("");
            return;
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        $payment->setLastTransId($params['Ds_AuthorisationCode']);
        if (!$order->getEmailSent() && (((int) $this->getStandard()->getConfigData('sendmailorderconfirmation')) == 1)) {
            $order->sendNewOrderEmail();
        }
        if ((bool) $this->getStandard()->getConfigData('autoinvoice')) {
            $invoice = $order->prepareInvoice(); // Evitamos captar un pago con total cero.
            if ($invoice->getGrandTotal() == 0) {
                $this->_log('The response must already have been processed! Aborting on invoice with zero total.');
                $this->_setNotificationResponseContent();
                $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
                $this->_log("");
                return;
            }
            $invoice->register()->capture();
            $order->addRelatedObject($invoice);
            $payment->setCreatedInvoice($invoice);
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
        }
        $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($orderState);

        $orderIsNotified = null;
        if ($stateObject->getState() && $stateObject->getStatus()) {
            $orderState = $stateObject->getState();
            $orderStatus = $stateObject->getStatus();
            $orderIsNotified = $stateObject->getIsNotified();
        } else {
            $orderStatus = $this->getStandard()->getConfigData('order_status');
            if (!$orderStatus || $order->getIsVirtual()) {
                $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
            }
        }
        $isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
        $message = $order->getCustomerNote();

        // add message if order was put into review during authorization or capture
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            if ($message) {
                $order->addStatusHistoryComment($message, $order->getStatus());
                //$order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
            }
        }
        // add message to history if order state already declared
        elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }
        // set order state
        elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }

        if ($isSoapNotification) {
            $this->_getEmailNotificationHelper()->setOrderEmailNotficationIsRequired($order,false);
        }
        else {
            $this->_getEmailNotificationHelper()->setOrderEmailNotficationIsRequired($order);
        }
        $order->save();

        $this->_setNotificationResponseContent();
        
        $this->_log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
        $this->_log("");
    }

    /**
     * @return Interactiv4_RedsysPro_PaymentController
     */
    protected function _logRequestRawBody() {
        $rawMessageBody = $this->getRequest()->getRawBody();
        $this->_log("------------- START PAYMENT RESPONSE (IPN) -------------");
        $this->_log('Logging raw message body');
        $this->_log($this->_unescapeRequestMessage($rawMessageBody));
        $this->_log("-------------- END PAYMENT RESPONSE (IPN) --------------");
        $this->_log("");
        return $this;
    }

    /**
     * @param bool|true $isOk
     * @param null $rawMessageBody
     * @param Mage_Sales_Model_Order|null $order
     * @return void
     */
    protected function _setNotificationResponseContent($isOk = true, $rawMessageBody = null, Mage_Sales_Model_Order $order = null) {
        if (!$this->_isSoapNotification($rawMessageBody)) {
            return;
        }

        if (!$order) {
            $order = $this->_getOrder($errorMessage);
            if (!$order) {
                $isOk = false;
            }
        }

        $store = $order ? $order->getStore() : null ;

        $responseXML = sprintf("<Response Ds_Version='0.0'><Ds_Response_Merchant>%s</Ds_Response_Merchant></Response>", $isOk ? 'OK' : 'KO');
        $responseSignature = $this->_getSignatureForSoapResponse($responseXML, $store);
        $responseXML = "<Message>$responseXML</Message><Signature>$responseSignature</Signature>";
        $responseXML = str_replace("'", "&apos;", htmlentities($responseXML));
        $responseBody = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
       <ns1:procesaNotificacionSIS xmlns:ns1="InotificacionSIS" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
            <XML xsi:type="xsd:string">%s</XML>
       </ns1:procesaNotificacionSIS>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;
        
        $responseBody = sprintf($responseBody, $responseXML);
        $this->getResponse()->setHeader('Content-type', 'text/xml');
        $this->getResponse()->setBody($responseBody);

        return;
    }


    /**
     * @param $message
     * @return mixed
     */
    protected function _unescapeRequestMessage($message) {
        return str_replace('&apos;', "'", html_entity_decode($message));
    }

    /**
     * @param string|null $messageBody
     * @return bool
     */
    protected function _isSoapNotification($messageBody = null) {
        if (!isset($messageBody)) {
            $messageBody = $this->getRequest()->getRawBody();
        }
        return strpos($messageBody, "SOAP-ENV:Envelope") !== false;
    }

    /**
     * Se pasan los parámetros devueltos por post por Redsys y si contienen
     * un error, se guarda como notificación, se cancela el pedido, 
     * y se devuelve false.
     * Al contrario, se devuelve true.
     *
     * @param array $params
     * @return bool
     */
    protected function _processResponseError($params) {
        if (!is_array($params)) {
            $params = array();
        }

        $order = $this->_getOrder($errorMessage); /* @var $order Mage_Sales_Model_Order */
        if (!$order) {
            return false;
        }
        $orderId = $order ? $order->getId() : 0;

        if (!array_key_exists('Ds_Response', $params) || $params['Ds_Response'] > 99) {
            if (array_key_exists('Ds_ErrorCode', $params)) {
                $error_code = $params['Ds_ErrorCode'];
            } else {
                $error_code = null;
            }

            if (array_key_exists('Ds_Response', $params)) {
                $response = $params['Ds_Response'];
            } else {
                $response = null;
            }

            if (array_key_exists('Ds_AuthorisationCode', $params)) {
                $authcode = $params['Ds_AuthorisationCode'];
            } else {
                $authcode = null;
            }
            Mage::getModel('i4redsyspro/redsyspro_notification')->addNotification($orderId, $error_code, $response, $authcode);
            $order->cancel()->save();
            return false;
        }

        if (!$this->_checkAmountMatchesOrder($params)) {
            if ($order) {
                $order->getPayment()
                        ->setTransactionId(array_key_exists('Ds_AuthorisationCode', $params) ? $params['Ds_AuthorisationCode'] : 'Not received')
                        ->setNotificationResult(true)
                        ->setIsTransactionClosed(true)
                        ->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_DENY, false);
                $order->save();
            }

            if (array_key_exists('Ds_ErrorCode', $params)) {
                $error_code = $params['Ds_ErrorCode'];
            } else {
                $error_code = null;
            }

            if (array_key_exists('Ds_Response', $params)) {
                $response = $params['Ds_Response'];
            } else {
                $response = null;
            }

            if (array_key_exists('Ds_AuthorisationCode', $params)) {
                $authcode = $params['Ds_AuthorisationCode'];
            } else {
                $authcode = null;
            }

            Mage::getModel('i4redsyspro/redsyspro_notification')->addNotification($orderId, $error_code, $response, $authcode);
            return false;
        }
        return true;
    }

    /**
     *
     * @param array $params
     * @return boolean 
     */
    protected function _checkAmountMatchesOrder($params) {
        $order = $this->_getOrder($errorMessage);
        if (!$order || !is_array($params) || !array_key_exists('Ds_Amount', $params)) {
            return false;
        }

        $storeId = Mage::app()->getStore()->getStoreId();
        $orderAmount = ($storeId == 7)
            ? round($order->getGrandTotal() * 100)
            : round($order->getBaseGrandTotal() * 100);
        //$orderAmount = round($order->getBaseGrandTotal() * 100);
        
        $redsysParamsAmount = $params['Ds_Amount'];
        return $orderAmount == $redsysParamsAmount ? true : false;
    }

    public function redirectAction() {
        $this->_log(__METHOD__);

        Mage::app()->setUseSessionVar(false);
        Mage::app()->setUseSessionInUrl(false);

        $ordernum = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($ordernum); /* @var $order Mage_Sales_Model_Order */
        if (!$this->_isOrderAlreadyPaid($order) && $this->_orderNewOrPending($order)) {
            $order->setStatus('redsyspro_pending');
            if(strlen($order->getI4redsysproSessionId()) === 0)
            {
                $callingSidParam = md5(uniqid(rand(), true));
                $order->setI4redsysproSessionId($callingSidParam); // Guardamos un id único para identificar el pedido en el IP
            }
            $redsysOrderReference = $this->_getHelper()->generateRedsysOrderReference($order);
            $order->addStatusHistoryComment($this->_getHelper()->__("Initiated capture of payment details for Order with Interactiv4 RedsysPro. Redsys reference for order is %s.", $redsysOrderReference));
            $order->save();
            $this->loadLayout();
            $this->renderLayout();

            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
        } else {
            if ($this->_isOrderAlreadyPaid($order)) {
                $this->_log(__METHOD__ . " : could not redirect. Order {$order->getIncrementId()} has already been paid.");
            } elseif (!$this->_orderNewOrPending($order)) {
                $this->_log(__METHOD__ . " : could not redirect. Order {$order->getIncrementId()} is not in state new or pending.");

                $cartempty = Mage::getStoreConfig('payment/i4redsyspro/carrito_vacio');

                if (!$cartempty) {
                    $items = $order->getItemsCollection();

                    //reorder
                    $cart = Mage::getSingleton('checkout/cart');

                    //cancelamos la order por que generara una nueva
                    //$order->cancel()->save();

                    foreach ($items as $item) {
                        try {
                            $cart->addOrderItem($item);
                        } catch (Mage_Core_Exception $e) {
                            if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                                Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                            } else {
                                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                            }
                            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
                            $this->_getHelper()->log("");
                            $this->_redirect('checkout/cart');
                        } catch (Exception $e) {
                            Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
                            $this->_getHelper()->log("");
                            Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                            $this->_redirect('checkout/cart');
                        }
                    }
                    Mage::getSingleton('checkout/session')->addError('Pedido Cancelado para no repetir referencias en el TPV');
                    $cart->save();
                }
            }
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
        }
    }

    public function cancelRedirectAction() {
        $this->_log(__METHOD__);

        $ordernum = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($ordernum); /* @var $order Mage_Sales_Model_Order */

        $cartempty = Mage::getStoreConfig('payment/i4redsyspro/carrito_vacio');

        if (!$cartempty) {
            // Volvemos a llevar los items
            $items = $order->getItemsCollection();

            //reorder
            $cart = Mage::getSingleton('checkout/cart');

            //cancelamos la order por que generara una nueva
            $order->cancel()->save();

            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                    $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------"  . __LINE__);
                    $this->_getHelper()->log("");
                    $this->_redirect('checkout/cart');
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------"  . __LINE__);
                    $this->_getHelper()->log("");
                    Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                    $this->_redirect('checkout/cart');
                }
            }
            Mage::getSingleton('checkout/session')->addError('Pedido Cancelado para no repetir referencias en el TPV');
            $cart->save();
        }

            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
    }

    public function cancelAction() {
        $this->_log(__METHOD__);
        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
        }

        if (!$this->_checkSignature()) {
            $this->_log('Cancel error. Something was wrong with the signature.');
            $this->_setNotificationResponseContent(false);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            //$this->_redirect('checkout/cart');
            //return;
        }

        if ($this->_isOrderAlreadyPaid()) {
            $this->_log('The order is already paid.');
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
            return;
        }

        //reorder
        $cart = Mage::getSingleton('checkout/cart');

        if ($this->_getSavedResponse()) {
            $params = $this->_getSavedResponse();
        } else {
            $params = $this->getRequest()->getParams();
        }


        if (isset($params['Ds_ErrorCode'])) {
            $error_code = $params['Ds_ErrorCode'];
        } else {
            $error_code = null;
        }
        if (isset($params['Ds_Response'])) {
            $response = $params['Ds_Response'];
        } else {
            $response = null;
        }
        if (isset($params['Ds_AuthorisationCode'])) {
            $authcode = $params['Ds_AuthorisationCode'];
        } else {
            $authcode = null;
        }
        $notifModel = Mage::getModel('i4redsyspro/redsyspro_notification');
        $notifModel->load($order->getId(),'entity_id');

        if (!isset($notifModel) || !$notifModel->getId()) {
            Mage::getModel('i4redsyspro/redsyspro_notification')->addNotification($order->getId(), $error_code, $response, $authcode);
        }

        if ($order->getId()) {

            $cartempty = Mage::getStoreConfig('payment/i4redsyspro/carrito_vacio');

            if ($cartempty) {
                $order->cancel()->save();
            } else {

                $items = $order->getItemsCollection();

                //cancelamos la order por que generara una nueva
                $order->cancel()->save();

                foreach ($items as $item) {
                    try {
                        $cart->addOrderItem($item);
                    } catch (Mage_Core_Exception $e) {
                        if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                            Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                        } else {
                            Mage::getSingleton('checkout/session')->addError($e->getMessage());
                        }
                        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
                        $this->_getHelper()->log("");
                        $this->_redirect('checkout/cart');
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
                        $this->_getHelper()->log("");
                        Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                        $this->_redirect('checkout/cart');
                    }
                }
                Mage::getSingleton('checkout/session')->addError('Pedido Cancelado desde el TPV');
                $cart->save();
            }
        }

//        Mage::throwException(Mage::helper('i4redsyspro')->getErrorDescription($error_code));
        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
        $this->_getHelper()->log("");
        $this->_redirect('checkout/cart');
    }

    /**
     *
     * @return array|false
     */
    protected function _getSavedResponse() {
        $order = $this->_getOrder($errorMessage);
        if ($order) {
            $result = $this->_getHelper()->getOrderRedsysParams($order);
            return is_array($result) ? $result : false;
        } else {
            return false;
        }
    }

    /**
     * Se devuelve true si el pedido ya está pagado.
     * @return boolean 
     */
    protected function _isOrderAlreadyPaid(Mage_Sales_Model_Order $order = null) {
        $order = !isset($order) ? $this->_getOrder($errorMessage) : $order;

        if ($order) {
            $payment = $order->getPayment();
            return $payment->getLastTransId() ? true : false;
        }
        return false;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return type 
     */
    protected function _orderNewOrPending(Mage_Sales_Model_Order $order) {
        return array_search($order->getState(), array(Mage_Sales_Model_Order::STATE_NEW, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)) !== false;
    }

    public function successAction() {
        $this->_log(__METHOD__);

        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_redirect('checkout/cart');
        }

        if (!$this->_checkSignature()) {
            $this->_log('Success error. Something was wrong with the signature.');
            //$this->_setNotificationResponseContent(false);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------" . __LINE__);
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
        }

        if ($this->_isOrderAlreadyPaid() || $this->_getSavedResponse()) {
            $this->_log("The order '#" . $order->getIncrementId() . "' has already been paid or a response was already received.");

            $params = $this->_getSavedResponse();

            if (is_array($params) && isset($params['Ds_Response']) && $params['Ds_Response'] > 99) {
                $this->_redirect('checkout/cart');
            } else {
                Mage::getSingleton('checkout/session')->setLastOrderId($order->getId());
                $this->_redirect('checkout/onepage/success', array('_secure' => true));
            }
            return;
        } elseif ($this->_isStillAwaitingNotification()) {
            Mage::getSingleton('checkout/session')->setLastOrderId($order->getId());
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
            return;
        } else {
            $params = $this->getRequest()->getParams();
            $this->_saveResponse($params);
        }

        if (!$this->_processResponseError($params)) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_getQuote()->setIsActive(false)->save();

        $stateObject = new Varien_Object();

        $payment = $order->getPayment();
        $payment->setLastTransId($params['Ds_AuthorisationCode']);
        if (!$order->getEmailSent() && (((int) $this->getStandard()->getConfigData('sendmailorderconfirmation')) == 1)) {
            $order->sendNewOrderEmail();
        }
        if ((bool) $this->getStandard()->getConfigData('autoinvoice')) {

            $invoice = $order->prepareInvoice();
            if ($invoice->getGrandTotal() == 0) { // Evitamos captar un pago con total cero.
                $this->_log('The response must already have been processed! Aborting on invoice with zero total.');
                $this->_redirect('checkout/cart');
                return;
            }
            $invoice->register()->capture();
            $order->addRelatedObject($invoice);
            $payment->setCreatedInvoice($invoice);
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
        }
        $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($orderState);

        $orderIsNotified = null;
        if ($stateObject->getState() && $stateObject->getStatus()) {
            $orderState = $stateObject->getState();
            $orderStatus = $stateObject->getStatus();
            $orderIsNotified = $stateObject->getIsNotified();
        } else {
            $orderStatus = $this->getStandard()->getConfigData('order_status');
            if (!$orderStatus || $order->getIsVirtual()) {
                $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
            }
        }
        $isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
        $message = $order->getCustomerNote();

        // add message if order was put into review during authorization or capture
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            if ($message) {
                $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
            }
        }
        // add message to history if order state already declared
        elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }
        // set order state
        elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
        }
        $this->_getEmailNotificationHelper()->setOrderEmailNotficationIsRequired($order);
        $order->save();

        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    /**
     *
     * @param array $params
     * @return boolean 
     */
    protected function _saveResponse($params) {
        if (!is_array($params)) {
            return false;
        }
        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            return false;
        }

        $this->_getHelper()->setOrderRedsysParams($order, $params);
        $order->save();
        return true;
    }

    /**
     * Sacamos del query string el ID único que nos permite identifcar el pedido que se ha pagado.
     * @return type 
     */
    protected function _getCallingSessionId() {
        return $this->getRequest()->getParam(Interactiv4_RedsysPro_Model_Standard::getCallingSessionIdQueryParam());
    }

    /**
     * Se devuelve true si no tenemos una respuesta guardada todavía del banco 
     * y los parámetros recibidos tampoco contienen la respuesta.
     * @return boolean 
     */
    protected function _isStillAwaitingNotification() {
        $params = $this->_getSavedResponse();
        if (is_array($params) && array_key_exists('Ds_Response', $params)) {
            return false;
        }

        $params = $this->getRequest()->getParams();
        return (!is_array($params) || !array_key_exists('Ds_Response', $params)) ? true : false;
    }

    /**
     * Se recupera el pedido que se está pagando.
     * @param string &$error - Se devuelve un mensaje de error si no es posible recuperar el pedido. 
     * @return Mage_Sales_Model_Order|boolean
     */
    protected function _getOrder(&$errorMessage) {
        $errorMessage = '';
        $callingSessionId = $this->_getCallingSessionId();
        if (!$this->_order) {
            if (!$callingSessionId) {
                $errorMessage = 'No session Id passed from redsys';
                $this->_log($errorMessage);
                return false;
            }
            $this->_order = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addFieldToFilter('i4redsyspro_session_id', $callingSessionId)
                    ->getLastItem();
        }
        if (!$this->_order || !$this->_order->getId()) {
            $errorMessage = 'Order passed from redsys not recognised: ' . $callingSessionId;
            $this->_log($errorMessage);
            return false;
        }
        return $this->_order;
    }

    /**
     * 
     * @return boolean 
     */
    protected function _getQuote() {
        if (!$this->_quote) {
            $order = $this->_getOrder($errorMessage);
            if (!$order) {
                return false;
            }
            $this->_quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        }
        return $this->_quote;
    }

    /**
     *
     * @param string $message 
     */
    protected function _setHTTPInternalServerError($message = '') {
        header('HTTP/1.1 500 Internal Server Error: ' . $message);
        die("Server error");
    }

    /**
     *
     * @param string $msg
     * @return Interactiv4_RedsysPro_PaymentController
     */
    protected function _log($msg) {
        $this->_getHelper()->log($msg);
        return $this;
    }

    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }

    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _getEmailNotificationHelper() {
        return Mage::helper('i4redsyspro/emailnotification');
    }

    public function debugemailnotificationsAction() {
        /** @var Interactiv4_RedsysPro_Model_Cron_Checkemailnotifications $cron */
        $cron = Mage::getModel('i4redsyspro/cron_checkemailnotifications');
        $cron->checkEmailNotifications();
        echo 'OK';
    }

    /* public function debugsoapAction() {

      $messageBody = <<<EOD
      <?xml version='1.0' encoding='UTF-8'?>
      <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
      <SOAP-ENV:Body>
      <ns1:procesaNotificacionSIS xmlns:ns1="InotificacionSIS" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
      <XML xsi:type="xsd:string">&lt;Message&gt;&lt;Request Ds_Version=&apos;0.0&apos;&gt;&lt;Fecha&gt;19/04/2013&lt;/Fecha&gt;&lt;Hora&gt;11:35&lt;/Hora&gt;&lt;Ds_SecurePayment&gt;1&lt;/Ds_SecurePayment&gt;&lt;Ds_Card_Country&gt;724&lt;/Ds_Card_Country&gt;&lt;Ds_Amount&gt;60199&lt;/Ds_Amount&gt;&lt;Ds_Currency&gt;978&lt;/Ds_Currency&gt;&lt;Ds_Order&gt;009100000143&lt;/Ds_Order&gt;&lt;Ds_MerchantCode&gt;329095830&lt;/Ds_MerchantCode&gt;&lt;Ds_Terminal&gt;001&lt;/Ds_Terminal&gt;&lt;Ds_Response&gt;0000&lt;/Ds_Response&gt;&lt;Ds_MerchantData&gt;&lt;/Ds_MerchantData&gt;&lt;Ds_TransactionType&gt;0&lt;/Ds_TransactionType&gt;&lt;Ds_ConsumerLanguage&gt;1&lt;/Ds_ConsumerLanguage&gt;&lt;Ds_AuthorisationCode&gt;867145&lt;/Ds_AuthorisationCode&gt;&lt;/Request&gt;&lt;Signature&gt;840d8a60031be6ab08650dca355e4c5d560ef1ad&lt;/Signature&gt;&lt;/Message&gt;</XML>
      </ns1:procesaNotificacionSIS>
      </SOAP-ENV:Body>
      </SOAP-ENV:Envelope>
      EOD;
      $this->_processSoapNotification($messageBody, Mage::getModel('sales/order'));
      $this->_setNotificationResponseContent(true, $messageBody, Mage::getModel('sales/order'));
      } */

    protected function _getSignatureForSoapResponse($string, $store = null)
    {
        $this->_log("Start ". __METHOD__);

        $signatureAlgorithm = $this->getStandard()->getConfigData('signaturealgorithm', $store);
        $data = $this->getRequest()->getParams();

        $result ="";

        switch ($signatureAlgorithm) {
            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA1_ALGORITHM:
                $result = sha1($string . $this->getStandard()->getClave($store));
                break;

            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM:
                if (isset($data['Ds_Order'])) {
                    $keyConfig = $this->getStandard()->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['Ds_Order'], $base64decodedKey);

                    $result = base64_encode(hash_hmac('sha256', $string, $keyByOrderReference, true));
                }
                break;

            default:
                if (isset($data['Ds_Order'])) {
                    $keyConfig = $this->getStandard()->getClave($store);
                    $base64decodedKey = base64_decode($keyConfig);
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($data['Ds_Order'], $base64decodedKey);

                    $result = base64_encode(hash_hmac('sha256', $string, $keyByOrderReference, true));
                }
        }

        return $result;
    }

    protected function _getWhatSignatureShouldBe($string, $data = null, $store = null, $isSoap = false) {
        $this->_log("Start ". __METHOD__);
        $signatureAlgorithm = $this->getStandard()->getConfigData('signaturealgorithm', $store);

        $result ="";

        switch ($signatureAlgorithm) {
            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA1_ALGORITHM:
                $result = sha1($string . $this->getStandard()->getClave($store));
                break;

            case Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm::SHA256_ALGORITHM:
                $keyConfig = $this->getStandard()->getClave($store);
                $base64decodedKey = base64_decode($keyConfig);

                $dataBase64Decoded = $this->_decodeBase64Params($data);

                $params = json_decode($dataBase64Decoded,true);
                if (isset($params['Ds_Order'])) {
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($params['Ds_Order'], $base64decodedKey);
                    $result =  $this->_encodeBase64Params(hash_hmac('sha256', $data, $keyByOrderReference, true));
                }
                break;

            default:
                $keyConfig = $this->getStandard()->getClave($store);
                $base64decodedKey = base64_decode($keyConfig);

                $dataBase64Decoded = $this->_decodeBase64Params($data);

                $params = json_decode($dataBase64Decoded,true);
                if (isset($params['Ds_Order'])) {
                    $keyByOrderReference = $this->_getHelper()->encrypt_3DES($params['Ds_Order'], $base64decodedKey);
                    $result =  $this->_encodeBase64Params(hash_hmac('sha256', $data, $keyByOrderReference, true));
                }
                break;
        }

        return $result;
    }

    protected function _decodeBase64Params($data) {
        $aux = strtr($data, '-_', '+/');

        $result =  base64_decode($aux);

        return $result;
    }

    protected function _encodeBase64Params($data) {
        $aux = base64_encode($data);

        $result =  strtr($aux, '+/', '-_');

        return $result;
    }

    protected function _checkSignature() {

        $this->_log("Start ". __METHOD__);
        if (!$this->_getHelper()->isSHA256Configured()) {
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return true;
        }

        $params = $this->getRequest()->getParams();
        if ($this->_getSavedResponse()) {
            $this->_log('Notification response already received. Skip check Signature');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return true;
        }

        $receivedSignature = array_key_exists('Ds_Signature', $params) ? $params['Ds_Signature'] : null;

        if (!$receivedSignature) {
            $this->_log('Notification error. Received signature missing.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        $dataAsReceived = array_key_exists('Ds_MerchantParameters', $params) ? $params['Ds_MerchantParameters'] : null;

        if (!$dataAsReceived) {
            $this->_log('Notification error. No params received.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        //Mage::log("receivedSignature => " . $receivedSignature ,null, "sha256.log",true);
        $whatSignatureShouldBe = $this->_getWhatSignatureShouldBe("", $dataAsReceived, null);
        //Mage::log("whatSignatureShouldBe => " . $whatSignatureShouldBe ,null, "sha256.log",true);

        if ($receivedSignature !== $whatSignatureShouldBe) {
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }
        $this->_log("End ". __METHOD__ . "::" . __LINE__);
        return true;
    }

    protected function _checkSoapSignature() {
        $this->_log("Start ". __METHOD__);
        if (!$this->_getHelper()->isSHA256Configured()) {
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return true;
        }

        $messageBody = $this->getRequest()->getRawBody();

        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_log('The order could not be found.');
            return false;
        }

        $this->_log('SOAP notification detected. Extracting parameters.');
        $xmlNotification = simplexml_load_string($messageBody);
        if (!($xmlNotification instanceof SimpleXMLElement)) {
            $this->_log('Could not parse SOAP notification');
            return false;
        }

        if (!$xmlNotification->registerXPathNamespace('ns1', 'InotificacionSIS')) {
            $this->_log('Could not parse SOAP notification. Namespace registration failed');
            return false;
        }

        /* TEST */
        $XMLNodes = $xmlNotification->xpath('//ns1:procesaNotificacionSIS/XML');
        if (!is_array($XMLNodes) || (count($XMLNodes) == 0)) {
            $this->_log('Could not parse SOAP notification. Failed to extract XML message');
            return false;
        }

        $XMLAsString = $XMLNodes[0]->asXML();

        $XMLAsString = str_replace('xsi:type="xsd:string"',"",$XMLAsString);

        $XML = simplexml_load_string($XMLAsString);
        if (!$XML) {
            $this->_log('Could not parse SOAP notification. XML not found' . __LINE__);
            return false;
        }

        $XML2AsString = (isset($XML[0])) ? $XML[0]->__toString() : false;
        if (!$XML2AsString) {
            $this->_log('Could not parse SOAP notification. Message Request Xml part not found.' . __LINE__);
            return false;
        }
        $receivedRequest = null;
        $isFoundRequestString = preg_match('/<Request.*<\/Request>/', $XML2AsString, $receivedRequest);
        if (!$isFoundRequestString || !is_array($receivedRequest) || (count($receivedRequest) == 0)) {
            $this->_log('Could not parse SOAP notification. Request string could not be extracted.');
            return false;
        }
        $receivedRequest = $receivedRequest[0];
        if (!$receivedRequest) {
            $this->_log('Notification error. Received Request missing.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        //Mage::log("SOAP Request => " . $receivedRequest ,null, "sha256.log",true);

        $xml2 = simplexml_load_string($XML2AsString);
        if (!($xml2 instanceof SimpleXMLElement)) {
            $this->_log('Could not parse SOAP notification. Message Request Xml part not found.' . __LINE__);
            return false;
        }

        /** @var SimpleXMLElement $receivedRequest2 */
        $receivedRequest2 = (isset($xml2->Request) && $xml2->Request instanceof SimpleXMLElement ) ? $xml2->Request->asXML() : false;
        if (!$receivedRequest2) {
            $this->_log('Notification error. Received Request missing.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        //Mage::log("SOAP Request 2 => " . $receivedRequest2 ,null, "sha256.log",true);

        //Copiamos los parámetros desde el paquete SOAP/XML a los parámetros del request.
        // Así en el próximo paso, los podemos procesar como si llegaran por  POST.
        foreach ($xml2->Request->children() as $dsParamNode) { /* @var $dsParamNode SimpleXMLElement */
            $this->getRequest()->setParam($dsParamNode->getName(), (string) $dsParamNode);
        }

        $receivedSignature = isset($xml2->Signature) ? $xml2->Signature->__toString() : false;
        if (!$receivedSignature) {
            $this->_log('Notification error. Received signature missing.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        //Mage::log("SOAP Signature received => " . $receivedSignature ,null, "sha256.log",true);

        $receivedOrderRef = isset($xml2->Request->Ds_Order) ? $xml2->Request->Ds_Order->__toString() : false;
        if (!$receivedOrderRef) {
            $this->_log('Notification error. Received Ds_Order missing.');
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }

        //Mage::log("SOAP Order Ref received => " . $receivedOrderRef ,null, "sha256.log",true);

        $store = $order->getStore();
        $key = $this->getStandard()->getClave($store);
        $keyBase64Decoded = base64_decode($key);

        $keyByOrderReference = $this->_getHelper()->encrypt_3DES($receivedOrderRef, $keyBase64Decoded);

        $whatSignatureShouldBe = base64_encode(hash_hmac('sha256', $receivedRequest, $keyByOrderReference, true)); //$this->_encodeBase64Params(hash_hmac('sha256', $receivedRequest, $keyByOrderReference, true));
        //Mage::log("SOAP whatSignatureShouldBe => " . $whatSignatureShouldBe ,null, "sha256.log",true);

        if ($receivedSignature !== $whatSignatureShouldBe) {
            $this->_log("End ". __METHOD__ . "::" . __LINE__);
            return false;
        }
        $this->_log("End ". __METHOD__ . "::" . __LINE__);
        return true;
    }


}
