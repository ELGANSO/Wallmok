<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of Testconnection
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Data_Testconnection extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
         if ($this->_isCheckingEmailNotification()) {
             $this->_testConnnection();
         }
    }
    
    protected function _isCheckingEmailNotification() {
        return $this->_getEmailConfigValue('checknotificationemail') ? true : false;
    }
    
    protected function _getEmailConfigValue($field) {
        $result = $this->getData("groups/serviredproemailnotification/fields/$field");
        if (is_array($result) && array_key_exists('value', $result)) {
            return $result['value'];
        } 
        return $result;
    }
    /**
     *
     * @return \Interactiv4_ServiRedPro_Model_Config_Data_Testconnection
     * @throws Exception 
     */
    protected function _testConnnection() {
        $mailBox = new Interactiv4_EmailDownloader_Model_Emaildownloader(); /* @var $mailBox Interactiv4_EmailDownloader_Model_Emaildownloader */
        $success = $mailBox->initMailBox($this->_getEmailConfigValue('protocol'), $this->_getEmailConfigValue('mailboxhost'), $this->_getEmailConfigValue('mailboxusername'), $this->_getEmailConfigValue('mailboxpassword'), $this->_getEmailConfigValue('port'), $this->_getEmailConfigValue('security'), $errorMessage);
        if (!$success) {
            throw new Exception($this->_getHelper()->__("Unable to connect to mail server. ").($errorMessage ? $errorMessage : ''));
        }
        return $this;
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }     
    
    
}
?>
