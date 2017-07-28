<?php

class Ontic_Survey_Block_Adminhtml_Survey_Question_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'survey';
        $this->_controller = 'adminhtml_survey_question';

        $this->addButton('backBtn', [
            'label' => $this->__('Volver'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/question') . '\')',
            'class' => 'back'
        ]);

        parent::__construct();
        
        $this->updateButton('save', 'label', $this->__('Save'));
        $this->removeButton('reset');
        $this->removeButton('back');

        if($id = $this->getRequest()->get('id'))
        {
            $this->updateButton('delete', 'onclick', $this->getDeleteButtonOnClick($id));
        }
        else
        {
            $this->removeButton('delete');
        }
    }
    
    public function getHeaderText()
    {
        if($this->getDataObject()->isObjectNew())
        {
            return $this->__('Añadir pregunta');
        }
        
        return $this->__('Modificar pregunta');
    }

    protected function getDeleteButtonOnClick($id)
    {
        $deleteUrl = $this->getUrl('*/*/deleteQuestion', [ 'id' => $id ]);
        $message = $this->__('Are you sure you want to do this?');
        return sprintf('deleteConfirm(\'%s\', \'%s\')', $message, $deleteUrl);
    }
}
