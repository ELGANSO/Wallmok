<?php
/**
 * Description of Js
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js extends Mage_Adminhtml_Block_Template {
    
    const ADDRESS_INDEX_PLACEHOLDER = '@A@';
    const STREET_LINE_PLACEHOLDER = '@L@';
    
    
    /**
     *
     * @var string
     */
    protected $_tiposViasValuesJsArray = null;
    
    /**
     *
     * @var string
     */
    protected $_tiposViasLabelsJsArray = null;
    
    /**
     *
     * @var string 
     */
    protected $_existingAddressesBlockSelector = '';
    
    /**
     *
     * @var string
     */
    protected $_newAddressesBlockSelector = '';
    
    /**
     *
     * @var string
     */
    protected $_addressLineNameTemplate = '';
    
    /**
     *
     * @var string
     */
    protected $_newAddressLineNameTemplate = '';
    
    /**
     *
     * @var type 
     */
    protected $_addressLineIdTemplate = '';
    
    /**
     *
     * @var boolean 
     */
    protected $_showIfAddressInvalid = false;
    
    
    
    protected function _getTiposViasJsArrays() {
        $tiposVias = Mage::getModel('i4mrwes/shipping_carrier_mrw_source_tipovia'); /* @var $tiposVias Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Tipovia */
        $values = array("''");
        $labels = array("''");
        foreach ($tiposVias->toOptionArray() as $tipoVia) {
            $values[] = "'" . $tipoVia['value'] . "'";
            $labels[] = "'" . $tipoVia['label'] . "'";
        }
        $this->_tiposViasValuesJsArray = "[" . implode(",", $values) . ']';
        $this->_tiposViasLabelsJsArray = "[" . implode(",", $labels) . ']';
        return $this;       
    }
    
    /**
     *
     * @return string 
     */
    public function getTiposViasValuesJsArray() {
        if (!is_array($this->_tiposViasValuesJsArray)) {
            $this->_getTiposViasJsArrays();
        }
        return $this->_tiposViasValuesJsArray;
    }
    
    /**
     *
     * @return string
     */
    public function getTiposViasLabelsJsArray() {
        if (!is_array($this->_tiposViasLabelsJsArray)) {
            $this->_getTiposViasJsArrays();
        }
        return $this->_tiposViasLabelsJsArray;
    }
    
    public function getIsActive() {
        return $this->_getHelper()->isActive();
    }
    
    /**
     *
     * @return Interactiv4_Mrw_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }
    
    /**
     *
     * @param string $selector
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js 
     */
    public function setExistingAddressesBlock($selector) {
        $this->_existingAddressesBlockSelector = $selector;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getExistingAddressesBlock() {
        return $this->_existingAddressesBlockSelector;
    }
    
    /**
     *
     * @param string $selector
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js 
     */
    public function setNewAddressesBlock($selector) {
        $this->_newAddressesBlockSelector = $selector;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getNewAddressesBlock() {
        return $this->_newAddressesBlockSelector ;
    }
    
    /**
     *
     * @return string 
     */
    public function getAddressLineNameTemplate() {
        return $this->_addressLineNameTemplate;
    }
    
    /**
     *
     * @param string $template
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js 
     */
    public function setAddressLineNameTemplate($template) {
        $this->_addressLineNameTemplate = $template;
        return $this;
    }
    
    /**
     *
     * @return string 
     */
    public function getNewAddressLineNameTemplate() {
        return $this->_newAddressLineNameTemplate;
    }
    
    /**
     *
     * @param string $template
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js 
     */
    public function setNewAddressLineNameTemplate($template) {
        $this->_newAddressLineNameTemplate = $template;
        return $this;
    }    
    
    /**
     *
     * @return string
     */
    public function getAddressLineIdTemplate() {
        return $this->_addressLineIdTemplate;
    }
    
    /**
     *
     * @param string $template
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js 
     */
    public function setAddressLineIdTemplate($template) {
        $this->_addressLineIdTemplate = $template;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getAddressIndexPlaceholder() {
        return self::ADDRESS_INDEX_PLACEHOLDER;
    }
    
    /**
     *
     * @return string
     */
    public function getStreetLinePlaceholder() {
        return self::STREET_LINE_PLACEHOLDER;
    }
    
    /**
     * 
     * @param boolean $show
     * @return \Interactiv4_Mrw_Block_Adminhtml_Customer_Edit_Js
     */
    public function setShowIfAddressInvalid($show) {
        $this->_showIfAddressInvalid = $show;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getShowIfAddressInvalid() {
        return $this->_showIfAddressInvalid;
    }
}
?>
