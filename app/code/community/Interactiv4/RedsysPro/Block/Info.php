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
class Interactiv4_RedsysPro_Block_Info extends Mage_Payment_Block_Info_Cc {

    /**
     *
     */
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('i4redsyspro/payment/info/i4redsyspro.phtml');
    }

    /**
     * @param $orderId
     * @return Interactiv4_RedsysPro_Model_Redsyspro_Notification|null
     */
    public function getNotification($orderId) {
        /** @var Interactiv4_RedsysPro_Model_Mysql4_Redsyspro_Notification_Collection $notification_collection */
        $notification_collection = Mage::getResourceModel('i4redsyspro/redsyspro_notification_collection');
        $notification_collection->setOrderFilter($orderId);
        $notification = null;
        /** @var Interactiv4_RedsysPro_Model_Redsyspro_Notification $not */
        foreach ($notification_collection as $not) {
            $notification = $not;
            break;
        }
        return $notification;
    }

    /**
     * @return Interactiv4_RedsysPro_Model_Mysql4_Redsyspro_Refund_Collection|null
     */
    public function getRefundsCollection() {
        $refunds_collection = null;
        try {
            /** @var Interactiv4_RedsysPro_Model_Mysql4_Redsyspro_Refund_Collection $refunds_collection */
            $refunds_collection = Mage::getResourceModel('i4redsyspro/redsyspro_refund_collection');
            $refunds_collection
                ->setOrderFilter($this->getInfo()->getOrder()->getId())
                ->addOrder('refunded_on')
                ->load();
        } catch (exception $e) {
            $this->_getHelper()->log($e->getMessage());
        }
        return $refunds_collection;
    }
    
    /**
     * Retrieve credit card type name
     *
     * @return string
     */
    public function getCcTypeName()
    {
        return $this->getInfo()->getCcType() ? parent::getCcTypeName() : '';
    }    
    
    /**
     * Retrieve redsys helper
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }

}
