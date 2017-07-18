<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Model_Serviredpro_Notification extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('serviredpro/serviredpro_notification');
    }
    public function addNotification($parentId,$error_code,$response,$authcode)
    {
            $this->_getHelper()->log(__METHOD__);
            $this->setEntityId($parentId)
            ->setResponse($response)
            ->setAuthorisationCode($authcode)
            ->setErrorCode($error_code)
            ->save();
            $this->_getHelper()->log("Notification succesfully added to serviredpro_notification table");
            $this->_getHelper()->log("");
    }
        
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
}
