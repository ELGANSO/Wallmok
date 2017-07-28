<?php

class Ontic_Survey_Block_Adminhtml_Survey_Answer_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('survey_adminhtml_answer_form');
        $this->setTitle($this->__('Respuesta'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/saveAnswer', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ]);

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $this->__('Datos de la respuesta'),
            'class' => 'fieldset-wide'
        ]);

        if(!$this->getDataObject()->isObjectNew())
        {
            $fieldset->addField('answer_id', 'hidden', [
                'name' => 'answer_id'
            ]);
        }

        $fieldset->addField('question_id', 'select', [
            'name' => 'question_id',
            'label' => $this->__('Pregunta'),
            'title' => $this->__('Pregunta'),
            'required' => true,
            'values' => Mage::getModel('survey/question')->getOptions()
        ]);
        
        $fieldset->addField('text', 'text', [
            'name' => 'text',
            'label' => $this->__('Respuesta'),
            'title' => $this->__('Respuesta'),
            'required' => true
        ]);

        $fieldset->addField('order', 'text', [
            'name' => 'order',
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'required' => true,
        ]);

        $fieldset->addField('enabled', 'select', [
            'name' => 'enabled',
            'label' => $this->__('Enabled'),
            'title' => $this->__('Enabled'),
            'required' => true,
            'value' => 1,
            'values' => [
                1 => $this->__('Yes'),
                0 => $this->__('No'),
            ]
        ]);

        $values = $this->getDataObject()->getData();
        
        $form->setValues($values);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
