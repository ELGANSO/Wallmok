<?php

class Ontic_Survey_Model_Resource_Question extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/question', 'question_id');
    }
}
