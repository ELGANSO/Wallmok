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
class Interactiv4_RedsysPro_Model_Config_Data_Testconnection extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
         if ($this->_isCheckingEmailNotification()) {
             $this->_testConnnection();
         }
    }
    
    protected function _isCheckingEmailNotification() {
        return $this->_getEmailConfigValue('checknotificationemail') ? true : false;
    }
    
    protected function _getEmailConfigValue($field) {
        $result = $this->getData("groups/i4redsysproemailnotification/fields/$field");
        if (is_array($result) && array_key_exists('value', $result)) {
            return $result['value'];
        } 
        return $result;
    }
    /**
     *
     * @return Interactiv4_RedsysPro_Model_Config_Data_Testconnection
     * @throws Exception 
     */
    protected function _testConnnection() {
        /** @var Interactiv4_EmailDownloader_Model_Emaildownloader $mailBox */
        $mailBox = Mage::getModel('i4emaildownloader/emaildownloader'); //new Interactiv4_EmailDownloader_Model_Emaildownloader();
        $success = $mailBox->initMailBox($this->_getEmailConfigValue('protocol'), $this->_getEmailConfigValue('mailboxhost'), $this->_getEmailConfigValue('mailboxusername'), $this->_getEmailConfigValue('mailboxpassword'), $this->_getEmailConfigValue('port'), $this->_getEmailConfigValue('security'), $errorMessage);
        if (!$success) {
            throw new Exception($this->_getHelper()->__("Unable to connect to mail server. ").($errorMessage ? $errorMessage : ''));
        }
        return $this;
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }     
    
    
}
