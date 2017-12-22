<?php

class Ontic_Survey_Block_Adminhtml_Survey_Answer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('survey_answer_grid');
        $this->setDefaultSort('answer_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('survey/answer')
            ->getCollection()
            ->join(
                [ 'question' => 'survey/question' ],
                'main_table.question_id = question.question_id',
                [ 'question_text' => 'question.text' ]
            )
            ;

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('survey');

        $this->addColumn('answer_id', [
            'header' => $helper->__('Id'),
            'index' => 'answer_id' 
        ]);

        $this->addColumn('question_text', [
            'header' => $helper->__('Pregunta'),
            'index' => 'question_text',
            'filter_index' => 'main_table.question_id',
            'type' => 'options',
            'options' => Mage::getModel('survey/question')->getOptions()
        ]);

        $this->addColumn('text', [
            'header' => $helper->__('Respuesta'),
            'index' => 'text',
            'filter_index' => 'main_table.text',
        ]);

        $this->addColumn('order', [
            'header' => $helper->__('Priority'),
            'index' => 'order',
            'filter_index' => 'main_table.order',
        ]);

        $this->addColumn('enabled', [
            'header' => $helper->__('Enabled'),
            'index' => 'enabled',
            'filter_index' => 'main_table.enabled',
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
        return $this->getUrl('*/*/editAnswer', array('id' => $row->getId()));
    }
}
