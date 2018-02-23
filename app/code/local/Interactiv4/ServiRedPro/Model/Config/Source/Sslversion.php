<?php
/**
 * Description of Urls
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Sslversion {
    
    const SSL_VERSION_2 = 2;
    const SSL_VERSION_3 = 3;
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::SSL_VERSION_2,
                'label' => $this->_getHelper()->__('SSL version 2')
            ),
            array(
                'value' => self::SSL_VERSION_3,
                'label' => $this->_getHelper()->__('SSL version 3')
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
