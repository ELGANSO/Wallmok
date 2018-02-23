<?php
/**
 * Description of Checkemailnotifications
 *
 * @author davidslater
 */
class Interactiv4_ServiredPro_Model_Cron_Checkemailnotifications {
    
    public function checkEmailNotifications() {
        $this->_getEmailNotificationHelper()->processEmailConfirmations();
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data
     */
    protected function _getServiredHelper() {
        return Mage::helper('serviredpro');
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Emailnotification
     */
    protected function _getEmailNotificationHelper(){
        return Mage::helper('serviredpro/emailnotification');
    }
    
    
    
    
}
?>
