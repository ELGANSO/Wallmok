<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Block_Adminhtml_Integration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('integrationGrid');
        $this->setDefaultSort('integration_logs_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('i4integration/logs')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('integration_logs_id', array(
            'header'    => Mage::helper('i4integration')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'integration_logs_id',
        ));

        $this->addColumn('log_date', array(
            'header'    => Mage::helper('i4integration')->__('Date'),
            'align'     =>'left',
            'type'	  => 'datetime',
            'index'     => 'log_date',
        ));
      
        $this->addColumn('process_name', array(
            'header'    => Mage::helper('i4integration')->__('Process'),
            'index'     => 'process_name',
            'type'      => 'options',
            'options'   => Mage::getModel('i4integration/process')->getOptionArray()
        ));
        
        $this->addColumn('message', array(
            'header'    => Mage::helper('i4integration')->__('Message'),
            'index'     => 'message',
        ));
        
		$this->addExportType('*/*/exportCsv', Mage::helper('i4integration')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('i4integration')->__('XML'));
	  
      return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('integration_id');
        $this->getMassactionBlock()->setFormFieldName('integration');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('i4integration')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('i4integration')->__('Are you sure?')
        ));

        return $this;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}