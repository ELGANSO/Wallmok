<?php

class Ontic_Survey_Block_Adminhtml_Survey_Response extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'survey';
        $this->_controller = 'adminhtml_survey_response';
        $this->_headerText = Mage::helper('survey')->__('Contestaciones');

        parent::__construct();

        $this->_removeButton('add');
    }
}
