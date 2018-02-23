<?php

class Ontic_Sync_Block_Adminhtml_Updates extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'onticsync';
        $this->_controller = 'adminhtml_updates';
        $this->_headerText = 'Actualizaciones de productos';
        parent::__construct();
        $this->_removeButton('add');
    }
}