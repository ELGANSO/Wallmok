<?php

class Ontic_Sync_Adminhtml_OnticsyncController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/onticsync');
        $this->_addContent(
            $this->getLayout()->createBlock('onticsync/adminhtml_updates')
        );
        $this->renderLayout();
    }
}