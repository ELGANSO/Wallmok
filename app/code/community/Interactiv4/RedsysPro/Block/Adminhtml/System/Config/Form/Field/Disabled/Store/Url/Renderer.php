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
 class Interactiv4_RedsysPro_Block_Adminhtml_System_Config_Form_Field_Disabled_Store_Url_Renderer extends Mage_Adminhtml_Block_System_Config_Form_Field
 {
     protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

         $element->setDisabled('disabled');
         $element->setValue(Mage::getStoreConfig("web/unsecure/base_url"), $element->getScopeId());

         return parent::_getElementHtml($element);
    }
 }
