<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Block_Info extends Mage_Payment_Block_Info_Cc {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('i4serviredpro/payment/info/serviredpro.phtml');
    }
    public function getNotification($orderId) {
        $notification_collection = Mage::getResourceModel('serviredpro/serviredpro_notification_collection');
        $notification_collection->setOrderFilter($orderId);
        $notification = null;
        foreach ($notification_collection as $not) {
            $notification = $not;
            break;
        }
        return $notification;
    }
    public function getRefundsCollection() {
        try {
            $refunds_collection = Mage::getResourceModel('serviredpro/serviredpro_refund_collection');
            $refunds_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
//	    				->addAttributeToSelect('*')
                    ->addOrder('refunded_on')
                    ->load();
        } catch (exception $e) {
            $this->_getHelper()->log($e->getMessage());
        }
        return $refunds_collection;
    }
    public function getServiRedInfo() {
        try {
            $info_collection = Mage::getResourceModel('serviredpro/serviredpro_transaction_collection');
            $info_collection->setOrderFilter($this->getInfo()->getOrder()->getId())
//                ->addAttributeToSelect('*')
                    ->load();
        } catch (exception $e) {
            $this->_getHelper()->log($e->getMessage());
        }
        foreach ($info_collection as $info) {
            return $info;
            break;
        }
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
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
}
