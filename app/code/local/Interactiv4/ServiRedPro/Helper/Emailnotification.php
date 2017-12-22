<?php
/**
 * Description of Emailnotification
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Helper_Emailnotification extends Mage_Core_Helper_Abstract {
    const MAX_ATTEMPTS_DEFAULT = 5;
    const MAX_MAX_ATTEMPTS = 30;
    protected $_distinctMailboxes = array();
    
    /**
     *
     * @var array
     */
    protected $_orderSet = null;
    /**
     *
     * @param string $field
     * @param mixed $store
     * @return mixed 
     */
    public function getEmailNotificationConfig($field, $store = null) {
        return Mage::getStoreConfig("payment/serviredproemailnotification/$field", $store);
    }
    /**
     *
     * @param mixed $store
     * @return boolean 
     */
    public function isCheckingEmailNotificationsActivated($store) {
        return $this->getEmailNotificationConfig('checknotificationemail', $store) ? true : false;
    }
    /**
     *
     * @param mixed $store
     * @return int 
     */
    public function _getMaxAttempts($store) {
        $maxAttempts = $this->getEmailNotificationConfig('attempts', $store);
        if ($maxAttempts) {
            $maxAttempts = min(array(self::MAX_MAX_ATTEMPTS, $maxAttempts));
        } else {
            $maxAttempts = self::MAX_ATTEMPTS_DEFAULT;
        }
        return $maxAttempts;
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param boolean|null $isRequired
     * @return \Interactiv4_ServiRedPro_Helper_Emailnotification 
     */
    public function setOrderEmailNotficationIsRequired(Mage_Sales_Model_Order $order, $isRequired = null) {
        if (!isset($isRequired)) {
            $isRequired = $this->isCheckingEmailNotificationsActivated($order->getStore());
        }
        $order->setData('i4servired_email_notification_required', $isRequired ? 1 : 0);
        return $this;
    }
    /**
     *
     * @param mixed $store
     * @return Interactiv4_EmailDownloader_Model_Emaildownloader 
     */
    protected function _getStoreMailbox($store = null) {
        $host = $this->getEmailNotificationConfig('mailboxhost', $store);
        $type = $this->getEmailNotificationConfig('protocol', $store);
        $username = $this->getEmailNotificationConfig('mailboxusername', $store);
        $password = $this->getEmailNotificationConfig('mailboxpassword', $store);
        $port = $this->getEmailNotificationConfig('port', $store);
        $ssl = $this->getEmailNotificationConfig('security', $store);
        $distinctMailboxKey = "$username:$password@$host";
        if (array_key_exists($distinctMailboxKey, $this->_distinctMailboxes)) {
            return $this->_distinctMailboxes[$distinctMailboxKey];
        }
        $mailbox = new Interactiv4_EmailDownloader_Model_Emaildownloader();
        if (!$mailbox->initMailBox($type, $host, $username, $password, $port, $ssl, $errorMessage)) {
            $this->log("Unable to create mailbox $distinctMailboxKey: $errorMessage");
            $mailbox = false;
        }
        $this->_distinctMailboxes[$distinctMailboxKey] = $mailbox;
        return $mailbox;
    }
    /**
     *
     * @param string $emailText
     * @return boolean|array
     */
    protected function _parseOrderConfirmationEmail($emailText) {
        $serviredParams = array();
        if ($emailText) {
            $unparsedParams = explode(';', $emailText);
            foreach ($unparsedParams as $unparsedParam) {
                if ($unparsedParam) {
                    $parts = explode(':', $unparsedParam);
                    $partKey = trim($parts[0]);
                    if (count($parts) == 2) {
                        $serviredParams[$partKey] = trim($parts[1]);
                    } else {
                        $serviredParams[$partKey] = '';
                    }
                }
            }
        }
        return $serviredParams;
    }
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getServiredProHelper() {
        return Mage::helper('serviredpro');
    }
    /**
     *
     * @return boolean 
     */
    public function processEmailConfirmations() {
        $this->_updateOrdersNoLongerRequiringNotification();
        if ($this->_getUnnotifiedServiredOrders()->count() == 0) {
            $this->log(__FUNCTION__ . ': No orders requiring email notification check.');
            return true;
        }
        $mailBox = $this->_getStoreMailbox();
        if (!$mailBox) {
            return false;
        }
        while (true) {
            $emailIdx = $mailBox->getNextMessage();
            if (!$emailIdx) {
                break;
            }
            
            $this->_processEmailNotification($mailBox, $emailIdx);
            
            $mailBox->deleteMessage($emailIdx);
        }
        $this->_incrementOrdersAttemptsCount();
        $this->log(__FUNCTION__ . ': Finished processing email notifications.');
        return true;
    }
    /**
     *
     * @return Mage_Sales_Model_Mysql4_Order_Collection
     */
    protected function _getUnnotifiedServiredOrders() {
        $collection = Mage::getResourceModel('sales/order_collection'); /* @var $collection Mage_Sales_Model_Mysql4_Order_Collection */
        $collection->getSelect()->join(array('payment' => $collection->getTable('sales/order_payment')), 'main_table.entity_id=payment.parent_id');
        $collection->addFieldToFilter('main_table.i4servired_email_notification_required', 1)
                ->addFieldToFilter('payment.method', array('eq' => Interactiv4_ServiRedPro_Model_Standard::CODE));
        if (is_array($this->_orderSet)) { // Tenemos que recargar la collección de vez en cuando. Nos aseguramos no añadir más pedidos.
            $collection->addFieldToFilter('main_table.entity_id', array('in' => $this->_orderSet));
        } else {
            $this->_orderSet = array();
            foreach ($collection as $order) { /* @var $order Mage_Sales_Model_order */
                $this->_orderSet[] = $order->getId();
            }
            
        }
        return $collection;
    }
    /**
     *
     * @param Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox
     * @param int $emailIdx
     * @return \Interactiv4_ServiRedPro_Helper_Emailnotification 
     */
    protected function _processEmailNotification(Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox, $emailIdx) {
        
        $order = $this->_getOrderNotifiedByEmail($mailBox, $emailIdx);
        if ($order) {
            $emailText = $mailBox->getMessageText($emailIdx);   
            $order->addStatusHistoryComment($this->__("Servired email notification received: <br/>%s", str_replace('\n', '<br/>', $emailText)));
            $emailParams = $this->_parseOrderConfirmationEmail($emailText);
            $savedParams = $this->_getServiredProHelper()->getOrderServiredParams($order);
            if (!$this->_getServiredProHelper()->compareServiredParamSets($savedParams, $emailParams)) {
                $commentReason = $this->__("Servired email notification does not match IPN notification. Suspected fraud. <br/>IPN: <br/>n%s<br/><br/>Email:<br/><br/>%s", $this->_getServiredProHelper()->serviredParamsToString($savedParams), $this->_getServiredProHelper()->serviredParamsToString($emailParams));
                $this->_getServiredProHelper()->setSuspectedFraud($order, $commentReason);
            }
            $this->setOrderEmailNotficationIsRequired($order, false);
            $order->save();
        }
        return $this;
    }
    /**
     *
     * @param $mailBox Interactiv4_EmailDownloader_Model_Emaildownloader
     * @param int $emailIdx
     * @return Mage_Sales_Model_Order boolean 
     */
    protected function _getOrderNotifiedByEmail(Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox, $emailIdx) {
        $emailText = $mailBox->getMessageText($emailIdx);
        $this->log($emailText);
        if (!$emailText) {
            return false;
        }
        foreach ($this->_getUnnotifiedServiredOrders() as $order) { /* @var $order Mage_Sales_Model_Order */
            $serviredOrderRef = $this->_getServiredProHelper()->getServiredOrderReference($order);
            if ((strpos($emailText, $serviredOrderRef) !== false) && ($this->_isAllowedSenderEmailAddress($order, $mailBox, $emailIdx))) {
                return $order;
            }
        }
        
        return false;
    }
    /**
     *
     * @return \Interactiv4_ServiRedPro_Helper_Emailnotification 
     */
    protected function _updateOrdersNoLongerRequiringNotification() {
        foreach ($this->_getUnnotifiedServiredOrders() as $order) {/* @var $order Mage_Sales_Model_Order */
            if (!$this->isCheckingEmailNotificationsActivated($order->getStore())) {
                $this->setOrderEmailNotficationIsRequired($order, false);
                $order->addStatusHistoryComment($this->__('Email notification is no longer required for this order.'));
                $order->save();
            }
        }
        return $this;
    }
    /**
     *
     * @return \Interactiv4_ServiRedPro_Helper_Emailnotification 
     */
    protected function _incrementOrdersAttemptsCount() {
        foreach ($this->_getUnnotifiedServiredOrders() as $order) { /* @var $order Mage_Sales_Model_Order */
            $attemptsSoFar = $this->_getOrderNumEmailReadAttempts($order) + 1;
            $this->_setOrderNumEmailReadAttempts($order, $attemptsSoFar);
            if ($attemptsSoFar >= $this->_getMaxAttempts($order->getStore())) {
                $newStatus = $this->_getEmailNotFoundOrderStatus($order->getStore());
                
                $orderHistoryComment = $this->__("Gave up looking for Servired email notification for order %s after %s attempt(s).", $order->getIncrementId(), $attemptsSoFar);
                $this->setOrderEmailNotficationIsRequired($order, false);
                $this->log(__FUNCTION__ . ": $orderHistoryComment");
                $order->addStatusHistoryComment($orderHistoryComment, $newStatus);
            }
            $order->save();
        }
        return $this;
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return int
     */
    protected function _getOrderNumEmailReadAttempts(Mage_Sales_Model_Order $order) {
        $attemptsSoFar = $order->getData('i4servired_email_read_attempts');
        return $attemptsSoFar ? $attemptsSoFar : 0;
    }
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param int $numEmailReadAttempts
     * @return \Interactiv4_ServiRedPro_Helper_Emailnotification 
     */
    protected function _setOrderNumEmailReadAttempts(Mage_Sales_Model_Order $order, $numEmailReadAttempts) {
        $order->setData('i4servired_email_read_attempts', $numEmailReadAttempts);
        return $this;
    }
    /**
     *
     * @param mixed $store
     * @return string 
     */
    protected function _getEmailNotFoundOrderStatus($store) {
        return $this->getEmailNotificationConfig('orderstatus', $store);
    }
    /**
     *
     * @param type $store
     * @return type 
     */
    protected function _getAllowedSenderEmailAddresses($store) {
        $allowedEmailAddresses = $this->getEmailNotificationConfig('expectedsenderemail', $store);
        $allowedEmailAddresses = is_string($allowedEmailAddresses) && trim($allowedEmailAddresses) ? $allowedEmailAddresses : false;
        if (!$allowedEmailAddresses) {
            return false;
        }
        $allowedEmailAddresses = explode(',', $allowedEmailAddresses);
        array_walk($allowedEmailAddresses, create_function('&$val', '$val = trim($val); $val = strtolower($val);')); 
        return $allowedEmailAddresses;
    }
    
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param Interactiv4_EmailDownloader_Model_EmailDownloader $mailBox
     * @param int $emailId
     * @return boolean 
     */
    protected function _isAllowedSenderEmailAddress(Mage_Sales_Model_Order $order, Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox, $emailId) {
        $allowedEmailAddresses = $this->_getAllowedSenderEmailAddresses($order->getStore());
        if (!$allowedEmailAddresses) {
            return true;
        }
        $senderEmailAddress = $mailBox->getSenderEmailAddress($emailId);
        $senderEmailDomain = $mailBox->getSenderEmailAddressDomain($emailId);
        if ($senderEmailAddress && (array_search($senderEmailAddress, $allowedEmailAddresses) !== false)) {
            return true;
        } elseif ($senderEmailDomain && (array_search($senderEmailDomain, $allowedEmailAddresses) !== false)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     * @param string $msg
     * @return \Interactiv4_ServiRedPro_Helper_Data 
     */
    public function log($msg) {
        Mage::log($msg, null, 'i4emailnotification.log');
        return $this;
    }
}
?>
