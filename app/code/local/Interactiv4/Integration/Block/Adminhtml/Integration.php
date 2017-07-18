<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */
 
class Interactiv4_Integration_Block_Adminhtml_Integration extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct()
    {
        $this->_controller = 'adminhtml_integration';
        $this->_blockGroup = 'i4integration';
        $this->_headerText = Mage::helper('i4integration')->__('Integration Logs');
        $this->_addButtonLabel = Mage::helper('i4integration')->__('Add Item');
        parent::__construct();
        $this->_removeButton('add');
        $this->_addStockButton();
        $this->_addPriceButton();
        $this->_addCustomerButton();
        $this->_addOrderButton();
        $this->_addCatalogButton();
    }
    
    private function _addStockButton() {
        if (Mage::getStoreConfig('i4sync_stock/sync/enable')) {
            $this->_addButton('i4sync_stock', array(
                    'label'     => Mage::helper('i4integration')->__('Sync Stock'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_integration/syncStock') .'\')',
                    'class'     => '',
                ), -100);
        }
    }
    
    private function _addPriceButton() {
        if (Mage::getStoreConfig('i4sync_price/sync/enable')) {
            $this->_addButton('i4sync_price', array(
                'label'     => Mage::helper('i4integration')->__('Sync Price'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_integration/syncPrice') .'\')',
                'class'     => '',
            ), -90);
        }
    }
    
    private function _addCustomerButton() {
        if (Mage::getStoreConfig('i4sync_customer/sync/enable')) {
            $this->_addButton('i4sync_customer', array(
                'label'     => Mage::helper('i4integration')->__('Sync Customer'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_integration/syncCustomer') .'\')',
                'class'     => '',
            ), -80);
        }
    }
    
    private function _addOrderButton() {
        if (Mage::getStoreConfig('i4sync_order/sync/enable')) {
            $this->_addButton('i4sync_order', array(
                'label'     => Mage::helper('i4integration')->__('Sync Order'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_integration/syncOrder') .'\')',
                'class'     => '',
            ), -70);
        }
    }
    
    private function _addCatalogButton() {
        if (Mage::getStoreConfig('i4sync_catalog/sync/enable')) {
            $this->_addButton('i4sync_catalog', array(
                'label'     => Mage::helper('i4integration')->__('Sync Catalog'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_integration/syncCatalog') .'\')',
                'class'     => '',
            ), -60);
        }
    }

}