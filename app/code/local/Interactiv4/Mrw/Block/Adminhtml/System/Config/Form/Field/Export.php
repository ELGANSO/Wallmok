<?php
class Interactiv4_Mrw_Block_Adminhtml_System_Config_Form_Field_Export extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $params = array(
            'website' => $this->getRequest()->getParam('website')
        );
        $url = $this->getUrl('i4mrwes/adminhtml_index/export', $params);
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Exportar')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();
        return $html;
    }
}
