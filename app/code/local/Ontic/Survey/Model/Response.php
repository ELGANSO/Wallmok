<?php

class Ontic_Survey_Model_Response extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/response');
    }

    protected function _beforeSave()
    {
        // Borramos las respuestas previas de las mismas preguntas
        // que puedan existir
        if(($question = $this->getQuestion()) && $this['customer_id'])
        {
            $responses = Mage::getModel('survey/response')
                ->getCollection()
                ->addFieldToFilter('answer_id', [ 'in' => $question->getAnswerIds() ])
                ->addFieldToFilter('customer_id', [ 'eq' => $this['customer_id'] ])
                ;

            foreach($responses as $response)
            {
                $response->delete();
            }
        }

        return parent::_beforeSave();
    }

    protected function getAnswer()
    {
        return Mage::getModel('survey/answer')->load($this['answer_id']);
    }

    protected function getQuestion()
    {
        if($answer = $this->getAnswer())
        {
            return $answer->getQuestion();
        }

        return null;
    }
}
