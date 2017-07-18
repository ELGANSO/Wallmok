<?php

class Ontic_Survey_Block_Adminhtml_Survey_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('survey_question_grid');
        $this->setDefaultSort('question_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('survey/question')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('survey');

        $this->addColumn('question_id', [
            'header' => $helper->__('Id'),
            'index' => 'question_id' 
        ]);

        $this->addColumn('text', [
            'header' => $helper->__('Question'),
            'index' => 'text' 
        ]);

        $this->addColumn('order', [
            'header' => $helper->__('Priority'),
            'index' => 'order',
            'filter_index' => '`order`',
        ]);

        $this->addColumn('store_id', [
            'header' => $helper->__('Store'),
            'index' => 'store_id' ,
            'type' => 'store',
            'store_view' => true
        ]);

        $this->addColumn('enabled', [
            'header' => $helper->__('Enabled'),
            'index' => 'enabled',
            'type' => 'options',
            'options' =>  [
                '1' => $helper->__('Yes'),
                '0' => $helper->__('No')
            ]
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) 
    {
        return $this->getUrl('*/*/editQuestion', array('id' => $row->getId()));
    }
}
