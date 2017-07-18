<?php

class Ontic_Sync_Block_Adminhtml_Updates_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('update_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('onticsync/product_update')
            ->getCollection()
            ->join(
                [ 'request' => 'onticsync/product_update_request' ],
                'main_table.request_id = request.request_id',
                [ 'created_at' => 'request.created_at' ]
            )
            ;

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('survey');

        $this->addColumn('update_id', [
            'header' => $helper->__('Id'),
            'index' => 'update_id'
        ]);

        $this->addColumn('sku', [
            'header' => $helper->__('Producto'),
            'index' => 'sku',
        ]);

        $this->addColumn('created_at', [
            'header' => $helper->__('Fecha'),
            'index' => 'created_at',
        ]);

        $this->addColumn('status', [
            'header' => $helper->__('Estado'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'renderer' => 'Ontic_Sync_Block_Adminhtml_Updates_Renderer_Status',
            'options' => Ontic_Sync_Model_Product_Update::getStatusLabels()
        ]);

        return parent::_prepareColumns();
    }
}