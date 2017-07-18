<?php

class Ontic_Survey_Adminhtml_SurveyController extends Mage_Adminhtml_Controller_Action
{
    // Grid
    public function questionAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('survey');
        $this->_addContent(
            $this->getLayout()->createBlock('survey/adminhtml_survey_question')
        );
        $this->renderLayout();
    }

    public function answerAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('survey');
        $this->_addContent(
            $this->getLayout()->createBlock('survey/adminhtml_survey_answer')
        );
        $this->renderLayout();
    }

    public function responseAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('survey');
        $this->_addContent(
            $this->getLayout()->createBlock('survey/adminhtml_survey_response')
        );
        $this->renderLayout();
    }

    // Formularios
    public function editQuestionAction()
    {
        $model = Mage::getModel('survey/question');
        $questionId = $this->getRequest()->get('id');

        if(strlen($questionId) === 0)
        {
            $model->setData('order', $model->getLowestPriority() - 1);
            $model->setData('enabled', 1);
        }
        else
        {
            $model->load($questionId);
        }

        $this->loadLayout();
        $this->_setActiveMenu('survey');
        $block = $this->getLayout()->createBlock('survey/adminhtml_survey_question_edit');
        $block->setDataObject($model);
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function editAnswerAction()
    {
        $model = Mage::getModel('survey/answer');
        $answerId = $this->getRequest()->get('id');

        if(strlen($answerId) === 0)
        {
            $model->setData('order', $model->getLowestPriority() - 1);
            $model->setData('enabled', 1);
        }
        else
        {
            $model->load($answerId);
        }

        $this->loadLayout();
        $this->_setActiveMenu('survey');
        $block = $this->getLayout()->createBlock('survey/adminhtml_survey_answer_edit');
        $block->setDataObject($model);
        $this->_addContent($block);
        $this->renderLayout();
    }

    // Funciones de guardado
    public function saveQuestionAction()
    {
        if(!($postData = $this->getRequest()->getPost()))
        {
            return;
        }

        $session = Mage::getSingleton('adminhtml/session');

        try
        {
            $question = Mage::getModel('survey/question');
            $question->setData($postData);
            $question->save();
        }
        catch(Exception $e)
        {
            $session->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/survey/question');
    }

    public function saveAnswerAction()
    {
        if(!($postData = $this->getRequest()->getPost()))
        {
            return;
        }

        $session = Mage::getSingleton('adminhtml/session');

        try
        {
            $answer = Mage::getModel('survey/answer');
            $answer->setData($postData);
            $answer->save();
        }
        catch(Exception $e)
        {
            $session->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/survey/answer');
    }

    // Funciones de eliminación
    public function deleteQuestionAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        try
        {
            $id = $this->getRequest()->get('id');
            $question = Mage::getModel('survey/question')->load($id);
            $question->delete();
        }
        catch(Exception $e)
        {
            $session->addError($e->getMessage());
            $this->_redirectReferer();
            return;
        }

        $session->addSuccess($this->__('La pregunta ha sido eliminada'));
        $this->_redirect('*/*/question');
    }

    public function deleteAnswerAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        try
        {
            $id = $this->getRequest()->get('id');
            $answer = Mage::getModel('survey/answer')->load($id);
            $answer->delete();
        }
        catch(Exception $e)
        {
            $session->addError($e->getMessage());
            $this->_redirectReferer();
            return;
        }

        $session->addSuccess($this->__('La respuesta ha sido eliminada'));
        $this->_redirect('*/*/answer');
    }
}

