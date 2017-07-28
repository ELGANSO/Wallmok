<?php
/**
 * Description of Tipovia
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Tipovia {
    
    /**
     *
     * @return array 
     */
    public function getTiposVias() {
        return array(
            'C' => $this->_getHelper()->__('Calle'),
            'Avda.' => $this->_getHelper()->__('Avenida'),
            'Plz.' => $this->_getHelper()->__('Plaza')
            );
    }
    
    /**
     *
     * @return string 
     */
    public function getDefaultTipoViaCode() {
        return 'C';
    }
    
    /**
     *
     * @return array 
     */
    public function toOptionArray() {
        $tiposVias = $this->getTiposVias();
        $options = array();
        foreach ($tiposVias as $value => $label) {
            $options[] = array('value' => $value, 'label' => $label);
        }
        array_unshift($options, array('value' => 'Otro', 'label' => $this->_getHelper()->__('Otro')));
        return $options;
    }
    
    /**
     *
     * @param string $code
     * @return boolean 
     */
    public function isValidTipoViaCode($code) {       
        return array_key_exists($code, $this->getTiposVias()) ? true : false;
    }
    
    /**
     *
     * @return Interactiv4_Mrw_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }
}
?>
