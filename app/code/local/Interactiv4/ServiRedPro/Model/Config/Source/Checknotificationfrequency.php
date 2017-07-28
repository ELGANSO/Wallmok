<?php
/**
 * Description of Checknotificationfrequency
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Checknotificationfrequency {
    
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
     * @return Interactiv4_ServiRedPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }    
}
?>
