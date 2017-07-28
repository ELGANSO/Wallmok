<?php
/**
 * ServiRedPro
 *
 * @category ¬† ¬†Interactiv4
 * @package ¬† ¬† Interactiv4_ServiRedPro
 * @copyright ¬† Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_PaymentController extends Mage_Core_Controller_Front_Action {
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
     * @return Interactiv4_ServiRedPro_Model_Standard
     */
    public function getStandard() {
        return Mage::getSingleton('serviredpro/standard');
    }
    public function callbackAction() {
        $this->_log(__METHOD__);
        //$this->_setNotificationContent();
        $params = $this->getRequest()->getParams();
        $this->_getHelper()->log("------------ START PAYMENT RESPONSE (PARAMS) -----------");
        $this->_log($params);
        $this->_getHelper()->log("------------- END PAYMENT RESPONSE (PARAMS) ------------");
        $this->_getHelper()->log("");
        $this->_callbackAction = true;
        $this->successAction();
    }
    public function notificationAction() {
        $this->_log(__METHOD__);
        
        $this->_logRequestRawBody();
        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_setHTTPInternalServerError($errorMessage);
        }
        if ($this->_isOrderAlreadyPaid() || $this->_getSavedResponse()) {
            $this->_setNotificationResponseContent();
            $this->_log('The IPN has already been processed in the success action.');
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            return;
        }
        if (!$this->_processSoapNotification()) {
            $this->_log('Failed to process SOAP response.');
            $this->_setNotificationResponseContent(false);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            return;
        }
        $params = $this->getRequest()->getParams();
        $this->_getHelper()->log("---------- START PAYMENT RESPONSE (IPN PARAMS) ----------");
        $this->_log($params);
        $this->_getHelper()->log("----------- END PAYMENT RESPONSE (IPN PARAMS) -----------");
        $this->_getHelper()->log("");
        $this->_saveResponse($params);
        if (!isset($params['Ds_Response'])) {
            $this->_setNotificationResponseContent(false);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            return;
        }
        $this->_getQuote()->setIsActive(false)->save();
        $orderState = Mage_Sales_Model_Order::STATE_NEW;
        $orderStatus = false;
        $stateObject = new Varien_Object();
        if (!$this->_processResponseError($params)) {
            $this->_setNotificationResponseContent(true);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            return;
        }
        $payment = $order->getPayment();
        $payment->setLastTransId($params['Ds_AuthorisationCode']);
        if (!$order->getEmailSent() && (((int) Mage::getModel('serviredpro/standard')->getConfigData('sendmailorderconfirmation')) == 1)) {
            $order->sendNewOrderEmail();
        }
        if ((bool) Mage::getModel('serviredpro/standard')->getConfigData('autoinvoice')) {
            $invoice = $order->prepareInvoice(); // Evitamos captar un pago con total cero.
            if ($invoice->getGrandTotal() == 0) {
                $this->_log('The response must already have been processed! Aborting on invoice with zero total.');
                $this->_setNotificationResponseContent();
                $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
                $this->_getHelper()->log("");
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
            $orderStatus = Mage::getModel('serviredpro/standard')->getConfigData('order_status');
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
        $this->_setNotificationResponseContent();
        
        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
        $this->_getHelper()->log("");
    }
    /**
     *
     * @return \Interactiv4_ServiRedPro_PaymentController 
     */
    protected function _logRequestRawBody() {
        $rawMessageBody = $this->getRequest()->getRawBody();
        $this->_getHelper()->log("------------- START PAYMENT RESPONSE (IPN) -------------");
        $this->_log('Logging raw message body');
        $this->_log($this->_unescapeRequestMessage($rawMessageBody));
        $this->_getHelper()->log("-------------- END PAYMENT RESPONSE (IPN) --------------");
        $this->_getHelper()->log("");
        return $this;
    }
    /**
     *
     * @param boolean $isOk
     * @param string $rawMessageBody
     * @param Mage_Sales_Model_Order $order
     * @return boolean 
     */
    protected function _setNotificationResponseContent($isOk = true, $rawMessageBody = null, Mage_Sales_Model_Order $order = null) {
        if (!$this->_isSoapNotification($rawMessageBody)) {
            if (!$isOk) {
                return false;
            }
            return true;
        }
        if (!$order) {
            $order = $this->_getOrder($errorMessage);
            if (!$order) {
                return false;
            }
        }
        $responseXML = sprintf("<Response Ds_Version='0.0'><Ds_Response_Merchant>%s</Ds_Response_Merchant></Response>", $isOk ? 'OK' : 'KO');
        $standard = Mage::getModel('serviredpro/standard'); /* @var $standard Interactiv4_ServiRedPro_Model_Standard */
        $responseSignature = sha1($responseXML . $standard->getClave($order->getStore()));
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
        return true;
    }
    /**
     *
     * @param string $message
     * @return  
     */
    protected function _unescapeRequestMessage($message) {
        return str_replace('&apos', "'", html_entity_decode($message));
    }
    /**
     *
     * @param string $rawMessageBody
     * @return boolean 
     */
    protected function _processSoapNotification($rawMessageBody = null, Mage_Sales_Model_Order $order = null) {
        $messageBody = isset($rawMessageBody) ? $rawMessageBody : $this->getRequest()->getRawBody();
        $order = $order ? $order : $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_log('The order could not be found.');
            return false;
        }
        if (!$this->_isSoapNotification($messageBody)) {
            return true;
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
        $XMLNodes = $xmlNotification->xpath('//ns1:procesaNotificacionSIS/XML');
        if (!is_array($XMLNodes) || (count($XMLNodes) == 0)) {
            $this->_log('Could not parse SOAP notification. Failed to extract XML message');
            return false;
        }
        $XMLAsString = (string) $XMLNodes[0];
        $XML = simplexml_load_string($XMLAsString);
        if (!$XML) {
            $this->_log('Could not parse SOAP notification. XML not found');
            return false;
        }
        $XMLRequest = isset($XML->Request) ? $XML->Request : false;
        if (!$XMLRequest) {
            $this->_log('Could not parse SOAP notification. Request part not found.');
            return false;
        }
        //Copiamos los parámetros desde el paquete SOAP/XML a los parámetros del request. 
        // Así en el próximo paso, los podemos procesar como si llegaran por  POST.
        foreach ($XMLRequest->children() as $dsParamNode) { /* @var $dsParamNode SimpleXMLElement */
            $this->getRequest()->setParam($dsParamNode->getName(), (string) $dsParamNode);
        }
        // Validamos la firma
        $receivedSignature = isset($XML->Signature) ? (string) $XML->Signature : false;
        $requestString = null;
        $isFoundRequestString = preg_match('/<Request.*<\/Request>/', $XMLAsString, $requestString);
        if (!$isFoundRequestString || !is_array($requestString) || (count($requestString) == 0)) {
            $this->_log('Could not parse SOAP notification. Request string could not be extracted.');
            return false;
        }
        $whatSignatureShouldBe = sha1($requestString[0] . $this->getStandard()->getClave($order->getStore()));
        if (!is_string($receivedSignature) || ($receivedSignature !== $whatSignatureShouldBe)) {
            $this->_log('SOAP notification error. Received signature incorrect.');
            return false;
        }
        $this->_log('SOAP notification processed with signature validation ok.');
        return true;
    }
    /**
     *
     * @param string $messageBody
     * @return boolean 
     */
    protected function _isSoapNotification($messageBody = null) {
        if (!isset($messageBody)) {
            $messageBody = $this->getRequest()->getRawBody();
        }
        return strpos($messageBody, "SOAP-ENV:Envelope") !== false;
    }
    /**
     * Se pasan los parámetros devueltos por post por servired y si contienen 
     * un error, se guarda como notificación, se cancela el pedido, 
     * y se devuelve false.
     * Al contrario, se devuelve true.
     * @param array $params
     * @return boolean 
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
            Mage::getModel('serviredpro/serviredpro_notification')->addNotification($orderId, $error_code, $response, $authcode);
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
            Mage::getModel('serviredpro/serviredpro_notification')->addNotification($orderId, $error_code, $response, $authcode);
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
        $orderAmount = round($order->getBaseGrandTotal() * 100);
        $serviredParamsAmount = $params['Ds_Amount'];
        return $orderAmount == $serviredParamsAmount ? true : false;
    }
    public function redirectAction() {
        $this->_log(__METHOD__);
        $ordernum = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($ordernum); /* @var $order Mage_Sales_Model_Order */
        if (!$this->_isOrderAlreadyPaid($order) && $this->_orderNewOrPending($order)) {
            $order->setStatus('servired_pending');
            $callingSidParam = md5(uniqid(rand(), true));
            $order->setI4serviredSessionId($callingSidParam); // Guardamos un id único para identificar el pedido en el IP 
            $serviredOrderReference = $this->_getHelper()->generateServiredOrderReference($order);
            $order->addStatusHistoryComment($this->_getHelper()->__("Initiated capture of payment details for Order with Interactiv4 ServiredPro. Servired reference for order is %s.", $serviredOrderReference));
            $order->save();
            $this->loadLayout();
            $this->renderLayout();
            
            $block = $this->getLayout()->createBlock('serviredpro/redirect'); /* @var $block Interactiv4_ServiRedPro_Block_Redirect */
            $postHtml = $block->getPostHtml();
            $this->getResponse()->appendBody($postHtml);
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
        } else {
            if ($this->_isOrderAlreadyPaid($order)) {
                $this->_log(__METHOD__ . " : could not redirect. Order {$order->getIncrementId()} has already been paid.");
            } elseif ($this->_orderNewOrPending($order)) {
                $this->_log(__METHOD__ . " : could not redirect. Order {$order->getIncrementId()} is not in state new or pending.");
            }
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
        }
    }
    public function cancelAction() {
        $this->_log(__METHOD__);
        $order = $this->_getOrder($errorMessage);
        if (!$order) {
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
            $this->_getHelper()->log("");
            $this->_redirect('checkout/cart');
        }
        if ($this->_isOrderAlreadyPaid()) {
            $this->_log('The order is already paid.');
            $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
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
        Mage::getModel('serviredpro/serviredpro_notification')->addNotification($order->getId(), $error_code, $response, $authcode);
        if ($order->getId()) {
            $cartempty = Mage::getStoreConfig('payment/serviredpro/carrito_vacio');
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
                        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
                        $this->_getHelper()->log("");
                        $this->_redirect('checkout/cart');
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
                        $this->_getHelper()->log("");
                        Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                        $this->_redirect('checkout/cart');
                    }
                }
                Mage::getSingleton('checkout/session')->addError('Pedido Cancelado desde el TPV');
                $cart->save();
            }
        }
//        Mage::throwException(Mage::helper('serviredpro')->getErrorDescription($error_code));
        $this->_getHelper()->log("--------------- END " . __METHOD__ . " ---------------");
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
            $result = $this->_getHelper()->getOrderServiredParams($order);
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
        $orderState = Mage_Sales_Model_Order::STATE_NEW;
        $orderStatus = false;
        $stateObject = new Varien_Object();
        $payment = $order->getPayment();
        $payment->setLastTransId($params['Ds_AuthorisationCode']);
        if (!$order->getEmailSent() && (((int) Mage::getModel('serviredpro/standard')->getConfigData('sendmailorderconfirmation')) == 1)) {
            $order->sendNewOrderEmail();
        }
        if ((bool) Mage::getModel('serviredpro/standard')->getConfigData('autoinvoice')) {
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
            $orderStatus = Mage::getModel('serviredpro/standard')->getConfigData('order_status');
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
        $this->_getHelper()->setOrderServiredParams($order, $params);
        $order->save();
        return true;
    }
    /**
     * Sacamos del query string el ID único que nos permite identifcar el pedido que se ha pagado.
     * @return type 
     */
    protected function _getCallingSessionId() {
        return $this->getRequest()->getParam(Interactiv4_ServiRedPro_Model_Standard::getCallingSessionIdQueryParam());
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
                $errorMessage = 'No session Id passed from servired';
                $this->_log($errorMessage);
                return false;
            }
            $this->_order = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addFieldToFilter('i4servired_session_id', $callingSessionId)
                    ->getLastItem();
        }
        if (!$this->_order || !$this->_order->getId()) {
            $errorMessage = 'Order passed from servired not recognised: ' . $callingSessionId;
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
     * @return \Interactiv4_ServiRedPro_PaymentController 
     */
    protected function _log($msg) {
        $this->_getHelper()->log($msg);
        return $this;
    }
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Emailnotification
     */
    protected function _getEmailNotificationHelper() {
        return Mage::helper('serviredpro/emailnotification');
    }
    public function debugemailnotificationsAction() {
        $cron = Mage::getModel('serviredpro/cron_checkemailnotifications');
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
}
