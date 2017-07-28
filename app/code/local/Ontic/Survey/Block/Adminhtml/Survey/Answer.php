<?php

class Ontic_Survey_Block_Adminhtml_Survey_Answer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'survey';
        $this->_controller = 'adminhtml_survey_answer';
        $this->_headerText = Mage::helper('survey')->__('Respuestas');

        $this->addButton('btnAdd', [
            'label' => $this->__('Añadir respuesta'),
            'onclick' => "setLocation('" . $this->getUrl('*/*/editAnswer', ['page_key' => 'collection']) . "')",
            'class' => 'add'
        ]);

        parent::__construct();

        $this->_removeButton('add');
    }
}
