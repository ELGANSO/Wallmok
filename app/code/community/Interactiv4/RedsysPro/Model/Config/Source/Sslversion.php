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
class Interactiv4_RedsysPro_Model_Config_Source_Sslversion {
    
    const SSL_VERSION_2 = 2;
    const SSL_VERSION_3 = 3;

    // Only work for PHP versions using curl 7.34 or newer
    const SSL_VERSION_TLSv1_0 = 4;
    const SSL_VERSION_TLSv1_1 = 5;
    const SSL_VERSION_TLSv1_2 = 6;
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::SSL_VERSION_2,
                'label' => $this->_getHelper()->__('SSL version 2')
            ),
            array(
                'value' => self::SSL_VERSION_3,
                'label' => $this->_getHelper()->__('SSL version 3')
            ),
            // Only work for PHP versions using curl 7.34 or newer
            array(
                'value' => self::SSL_VERSION_TLSv1_0,
                'label' => $this->_getHelper()->__('TLS version 1.0')
            ),
            array(
                'value' => self::SSL_VERSION_TLSv1_1,
                'label' => $this->_getHelper()->__('TLS version 1.1')
            ),
            array(
                'value' => self::SSL_VERSION_TLSv1_2,
                'label' => $this->_getHelper()->__('TLS version 1.2')
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
