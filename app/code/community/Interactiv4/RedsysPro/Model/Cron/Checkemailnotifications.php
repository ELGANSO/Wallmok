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
class Interactiv4_RedsysPro_Model_Cron_Checkemailnotifications {
    

    public function checkEmailNotifications() {
        $this->_getEmailNotificationHelper()->processEmailConfirmations();
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getRedsysHelper() {
        return Mage::helper('i4redsyspro');
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Emailnotification
     */
    protected function _getEmailNotificationHelper(){
        return Mage::helper('i4redsyspro/emailnotification');
    }
    
    

    
    
}
