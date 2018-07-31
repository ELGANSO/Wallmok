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
class Interactiv4_RedsysPro_Model_Config_Source_Emailprotocols {

    const EMAIL_PROTOCOL_POP3 = 'POP3';
    const EMAIL_PROTOCOL_IMAP = 'IMAP';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::EMAIL_PROTOCOL_POP3,
                'label' => $this->_getHelper()->__('POP3')
            ),
            array(
                'value' => self::EMAIL_PROTOCOL_IMAP,
                'label' => $this->_getHelper()->__('IMAP')
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
