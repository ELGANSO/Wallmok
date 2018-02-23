<?php

class Ontic_Survey_Block_Adminhtml_Survey_Question_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('survey_adminhtml_question_form');
        $this->setTitle($this->__('Pregunta'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/saveQuestion', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ]);
        
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $this->__('Datos de la pregunta'),
            'class' => 'fieldset-wide'
        ]);

        if(!$this->getDataObject()->isObjectNew())
        {
            $fieldset->addField('question_id', 'hidden', [
                'name' => 'question_id'
            ]);
        }
        
        $fieldset->addField('text', 'text', [
            'name' => 'text',
            'label' => $this->__('Text'),
            'title' => $this->__('Text'),
            'required' => true
        ]);

        $fieldset->addField('order', 'text', [
            'name' => 'order',
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'required' => true,
        ]);

        $field = $fieldset->addField('store_id', 'select', [
            'name' => 'store_id',
            'label' => $this->__('Store'),
            'title' => $this->__('Store'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false)
        ]);
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);


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
