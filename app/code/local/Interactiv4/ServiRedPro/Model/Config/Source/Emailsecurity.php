<?php
/**
 * Description of Mailsecurity
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Emailsecurity {
    
    const EMAIL_SECURITY_NO = '';
    const EMAIL_SECURITY_SSL = 'SSL';
    const EMAIL_SECURITY_TLS = 'TLS';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::EMAIL_SECURITY_NO,
                'label' => $this->_getHelper()->__('Unencrypted')
            ),
            array(
                'value' => self::EMAIL_SECURITY_SSL,
                'label' => Mage::helper('adminhtml')->__('SSL')
            ),
            array(
                'value' => self::EMAIL_SECURITY_TLS,
                'label' => Mage::helper('adminhtml')->__('TLS')
            )            
        );
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
