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
class Interactiv4_RedsysPro_Model_Config_Source_Checknotificationfrequency {

    
    public function toOptionArray() {
        return array(
            array(
                'value' => '*/5 * * * *',
                'label' => $this->_getHelper()->__('Every 5 minutes')
            ),
            array(
                'value' => '*/10 * * * *',
                'label' => $this->_getHelper()->__('Every 10 minutes')
            ),
            array(
                'value' => '*/20 * * * *',
                'label' => $this->_getHelper()->__('Every 20 minutes')
            ),
            array(
                'value' => '*/30 * * * *',
                'label' => $this->_getHelper()->__('Every 30 minutes')
            ),  
            array(
                'value' => '0 * * * *',
                'label' => $this->_getHelper()->__('Every hour')
            ),                        
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
