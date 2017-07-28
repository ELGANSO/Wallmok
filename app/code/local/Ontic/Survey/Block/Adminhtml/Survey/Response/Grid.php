<?php

class Ontic_Survey_Block_Adminhtml_Survey_Response_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('survey_response_grid');
        $this->setDefaultSort('response_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $firstNameAttributeId = Mage::getModel('eav/config')->getAttribute('customer', 'firstname')->getId();
        $lastNameAttributeId = Mage::getModel('eav/config')->getAttribute('customer', 'lastname')->getId();

        $collection = Mage::getModel('survey/response')
            ->getCollection()
            ->join(
                [ 'answer' => 'survey/answer' ],
                'main_table.answer_id = answer.answer_id',
                [ 'answer_text' => 'answer.text' ]
            )
            ->join(
                [ 'question' => 'survey/question' ],
                'answer.question_id = question.question_id',
                [ 'question_text' => 'question.text' ]
            )
            ->join(
                [ 'customer' => 'customer/entity' ],
                'main_table.customer_id = customer.entity_id',
                [ 'customer_email' => 'customer.email' ]
            )
            ;

        $collection->getSelect()
            ->join(
                [ 'customer_varchar_firstname' => 'customer_entity_varchar' ],
                'main_table.customer_id = customer_varchar_firstname.entity_id AND customer_varchar_firstname.attribute_id = '. $firstNameAttributeId,
                [ 'customer_firstname' => 'customer_varchar_firstname.value' ]
            )
            ->join(
                [ 'customer_varchar_lastname' => 'customer_entity_varchar' ],
                'main_table.customer_id = customer_varchar_lastname.entity_id AND customer_varchar_lastname.attribute_id = '. $lastNameAttributeId,
                [ 'customer_fullname' => 'CONCAT(customer_varchar_firstname.value, " ", customer_varchar_lastname.value)' ]
            )
            ;

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('survey');

        $this->addColumn('response_id', [
            'header' => $helper->__('Id'),
            'index' => 'response_id' 
        ]);

        $this->addColumn('customer_name', [
            'header' => $helper->__('Customer'),
            'index' => 'customer_fullname',
            'filter_index' => 'CONCAT(customer_varchar_firstname.value, " ", customer_varchar_lastname.value)'
        ]);

        $this->addColumn('customer_email', [
            'header' => $helper->__('e-mail'),
            'index' => 'customer_email' ,
            'filter_index' => 'customer.email'
        ]);

        $this->addColumn('question_text', [
            'header' => $helper->__('Pregunta'),
            'index' => 'question_text' ,
            'filter_index' => 'question.question_id',
            'type' => 'options',
            'options' => Mage::getModel('survey/question')->getOptions()
        ]);

        $this->addColumn('answer_text', [
            'header' => $helper->__('Respuesta'),
            'index' => 'answer_text' ,
            'filter_index' => 'answer.text'
        ]);

        return parent::_prepareColumns();
    }
}
