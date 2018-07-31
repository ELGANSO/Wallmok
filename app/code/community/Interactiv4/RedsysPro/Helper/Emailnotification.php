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
class Interactiv4_RedsysPro_Helper_Emailnotification extends Mage_Core_Helper_Abstract {

    /**
     * Default max attemps
     */
    const MAX_ATTEMPTS_DEFAULT              = 5;

    /**
     * Max attemps superior limit
     */
    const MAX_MAX_ATTEMPTS                  = 30;

    const REDSYS_EMAILNOTIFICATION_LOG_FILE = 'i4emailnotification.log';

    /**
     * @var array
     */
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
        return Mage::getStoreConfig("payment/i4redsysproemailnotification/$field", $store);
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
     * @param bool|null $isRequired
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    public function setOrderEmailNotficationIsRequired(Mage_Sales_Model_Order $order, $isRequired = null) {
        if (!isset($isRequired)) {
            $isRequired = $this->isCheckingEmailNotificationsActivated($order->getStore());
        }
        $order->setData('i4redsyspro_email_notification_required', $isRequired ? 1 : 0);
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
        $mailbox = Mage::getModel('i4emaildownloader/emaildownloader');
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
     * @return bool|array
     */
    protected function _parseOrderConfirmationEmail($emailText) {
        $redsysParams = array();
        if ($emailText) {
            $unparsedParams = explode(';', $emailText);
            foreach ($unparsedParams as $unparsedParam) {
                if ($unparsedParam) {
                    $parts = explode(':', $unparsedParam);
                    $partKey = trim($parts[0]);
                    if (count($parts) == 2) {
                        $redsysParams[$partKey] = trim($parts[1]);
                    } else {
                        $redsysParams[$partKey] = '';
                    }
                }
            }
        }
        return $redsysParams;
    }

    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getRedsysProHelper() {
        return Mage::helper('i4redsyspro');
    }

    /**
     *
     * @return boolean 
     */
    public function processEmailConfirmations() {
        $this->_updateOrdersNoLongerRequiringNotification();

        if ($this->_getUnnotifiedRedsysOrders()->count() == 0) {
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
    protected function _getUnnotifiedRedsysOrders() {
        $collection = Mage::getResourceModel('sales/order_collection'); /* @var $collection Mage_Sales_Model_Mysql4_Order_Collection */
        $collection->getSelect()->join(array('payment' => $collection->getTable('sales/order_payment')), 'main_table.entity_id=payment.parent_id');
        $collection->addFieldToFilter('main_table.i4redsyspro_email_notification_required', 1)
                ->addFieldToFilter('payment.method', array('eq' => Interactiv4_RedsysPro_Model_Standard::CODE));
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
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _processEmailNotification(Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox, $emailIdx) {
        
        $order = $this->_getOrderNotifiedByEmail($mailBox, $emailIdx);
        if ($order) {
            $emailText = $mailBox->getMessageText($emailIdx);   
            $order->addStatusHistoryComment($this->__("Redsys email notification received: <br/>%s", str_replace('\n', '<br/>', $emailText)));
            $emailParams = $this->_parseOrderConfirmationEmail($emailText);
            $savedParams = $this->_getRedsysProHelper()->getOrderRedsysParams($order);
            if (!$this->_getRedsysProHelper()->compareRedsysParamSets($savedParams, $emailParams)) {
                $commentReason = $this->__("Redsys email notification does not match IPN notification. Suspected fraud. <br/>IPN: <br/>n%s<br/><br/>Email:<br/><br/>%s", $this->_getRedsysProHelper()->redsysParamsToString($savedParams), $this->_getRedsysProHelper()->redsysParamsToString($emailParams));
                $this->_getRedsysProHelper()->setSuspectedFraud($order, $commentReason);
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
        foreach ($this->_getUnnotifiedRedsysOrders() as $order) { /* @var $order Mage_Sales_Model_Order */
            $redsysOrderRef = $this->_getRedsysProHelper()->getRedsysOrderReference($order);
            if ((strpos($emailText, $redsysOrderRef) !== false) && ($this->_isAllowedSenderEmailAddress($order, $mailBox, $emailIdx))) {
                return $order;
            }
        }
        
        return false;
    }

    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _updateOrdersNoLongerRequiringNotification() {
        foreach ($this->_getUnnotifiedRedsysOrders() as $order) {/* @var $order Mage_Sales_Model_Order */
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
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _incrementOrdersAttemptsCount() {
        foreach ($this->_getUnnotifiedRedsysOrders() as $order) { /* @var $order Mage_Sales_Model_Order */
            $attemptsSoFar = $this->_getOrderNumEmailReadAttempts($order) + 1;
            $this->_setOrderNumEmailReadAttempts($order, $attemptsSoFar);
            if ($attemptsSoFar >= $this->_getMaxAttempts($order->getStore())) {
                $newStatus = $this->_getEmailNotFoundOrderStatus($order->getStore());
                
                $orderHistoryComment = $this->__("Gave up looking for Redsys email notification for order %s after %s attempt(s).", $order->getIncrementId(), $attemptsSoFar);
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
        $attemptsSoFar = $order->getData('i4redsyspro_email_read_attempts');
        return $attemptsSoFar ? $attemptsSoFar : 0;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param int $numEmailReadAttempts
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _setOrderNumEmailReadAttempts(Mage_Sales_Model_Order $order, $numEmailReadAttempts) {
        $order->setData('i4redsyspro_email_read_attempts', $numEmailReadAttempts);
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
     * @param Mage_Core_Model_Store|int $store
     * @return array|false
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
     * @return \Interactiv4_RedsysPro_Helper_Emailnotification
     */
    public function log($msg) {
        Mage::log($msg, null, self::REDSYS_EMAILNOTIFICATION_LOG_FILE);
        return $this;
    }

}
