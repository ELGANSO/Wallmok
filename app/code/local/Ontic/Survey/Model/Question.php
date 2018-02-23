<?php

class Ontic_Survey_Model_Question extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/question');
    }

    public function getOptions()
    {
        $options = [];

        $collection = $this->getCollection()->setOrder('text', 'ASC');

        foreach($collection as $question)
        {
            $options[$question->getId()] = $question['text'];
        }

        return $options;
    }

    public function getLowestPriority()
    {
        $collection = $this->getCollection()->setOrder('`order`', 'DESC');

        if($collection->count() === 0)
        {
            return 101;
        }

        return $collection->getFirstItem()['order'];
    }

    public function getAnswers()
    {
        return Mage::getModel('survey/answer')
            ->getCollection()
            ->addFieldToFilter('question_id', ['eq' => $this->getId()])
            ->addFieldToFilter('enabled', ['eq' => 1])
            ->setOrder('`order`', 'DESC')
            ;
    }

    public function getAnswerIds()
    {
        $ids = [];

        $query = $this->getAnswers()->getSelect()->query();
        while($row = $query->fetch())
        {
            $ids[] = $row['answer_id'];
        }

        return $ids;
    }
}
