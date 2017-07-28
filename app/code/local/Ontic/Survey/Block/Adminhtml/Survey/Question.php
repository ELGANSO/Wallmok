<?php

class Ontic_Survey_Block_Adminhtml_Survey_Question extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'survey';
        $this->_controller = 'adminhtml_survey_question';
        $this->_headerText = Mage::helper('survey')->__('Preguntas');

        $this->addButton('btnAdd', [
            'label' => $this->__('Añadir pregunta'),
            'onclick' => "setLocation('" . $this->getUrl('*/*/editQuestion', ['page_key' => 'collection']) . "')",
            'class' => 'add'
        ]);

        parent::__construct();

        $this->_removeButton('add');
    }
}
