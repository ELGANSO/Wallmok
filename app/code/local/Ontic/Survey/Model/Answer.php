<?php

class Ontic_Survey_Model_Answer extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/answer');
    }

    public function getQuestion()
    {
        return Mage::getModel('survey/question')->load($this['question_id']);
    }

    public function getLowestPriority()
    {
        if(strlen($this['question_id']) === 0)
        {
            return 101;
        }

        $collection = $this->getCollection()
            ->addFieldToFilter('question_id', [ 'eq' => $this['question_id'] ])
            ->setOrder('`order`', 'DESC');

        if($collection->count() === 0)
        {
            return 101;
        }

        return $collection->getFirstItem()['order'];
    }
}
