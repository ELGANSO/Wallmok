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
class Interactiv4_RedsysPro_Model_Config_Source_Emailsecurity {
    
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
                'label' => $this->_getHelper()->__('SSL')
            ),
            array(
                'value' => self::EMAIL_SECURITY_TLS,
                'label' => $this->_getHelper()->__('TLS')
            )            
        );
    }    
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }    
    
}
