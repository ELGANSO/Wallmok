<?php
/**
 * Description of Urls
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Urls {
    
    const URL_DEFAULT = 'default';
    const URL_SERMEPA = 'sermepa';
    const URL_REDSYS = 'redsys';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => self::URL_DEFAULT,
                'label' => $this->_getHelper()->__('Use default')
            ),
            array(
                'value' => self::URL_REDSYS,
                'label' => $this->_getHelper()->__('Redsys')
            ),     
            array(
                'value' => self::URL_SERMEPA,
                'label' => $this->_getHelper()->__('Sermepa')
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
