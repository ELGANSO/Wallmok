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
class Interactiv4_RedsysPro_Model_Redsyspro_Notification extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('i4redsyspro/redsyspro_notification');
    }

    public function addNotification($parentId,$error_code,$response,$authcode)
    {
            $this->_getHelper()->log(__METHOD__);
            $this->setEntityId($parentId)
            ->setResponse($response)
            ->setAuthorisationCode($authcode)
            ->setErrorCode($error_code)
            ->save();
            $this->_getHelper()->log("Notification succesfully added to i4redsyspro_notification table");
            $this->_getHelper()->log("");
    }
        
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }

}