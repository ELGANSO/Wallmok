<?php

class Ontic_Survey_IndexController extends Mage_Core_Controller_Front_Action
{
    public function questionsAction()
    {
        $questions = Mage::getModel('survey/question')
            ->getCollection()
            ->addFieldToFilter('enabled', ['eq' => 1])
            ->addFieldToFilter('store_id', ['eq' => Mage::app()->getStore()->getId()])
            ->setOrder('`order`', 'DESC')
            ;

        $data = [];
        foreach($questions as $question)
        {
            $questionData = [ 
                'id' => $question->getId(),
                'text' => $question['text'],
                'answers' => []
            ];


            $answers = Mage::getModel('survey/answer')
                ->getCollection()
                ->addFieldToFilter('question_id', ['eq' => $question->getId()])
                ->addFieldToFilter('enabled', ['eq' => 1])
                ->setorder('`order`', 'DESC')
                ;

            foreach($question->getAnswers() as $answer)
            {
                $questionData['answers'][] = [
                    'id' => $answer->getId(),
                    'text' => $answer['text']
                ];
            }

            $data[] = $questionData;
        }

        $response = [
            'total_questions' => $questions->count(),
            'questions' => $data
        ];


        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    public function saveAction()
    {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
        {
            $this->getResponse()->setHeader('HTTP/1.1', '403', true);
            $this->getResponse()->setBody('403 Forbidden');
            return;
        }

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $answerIds = $this->getRequest()->get('answers');
        foreach($answerIds as $id)
        {
            Mage::getModel('survey/response')
                ->setData('customer_id', $customerId)
                ->setData('answer_id', $id)
                ->save();

            $this->getResponse()->setHeader('HTTP/1.1', '201', true);
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(json_encode(['success' => true]));
        }
    }
}
