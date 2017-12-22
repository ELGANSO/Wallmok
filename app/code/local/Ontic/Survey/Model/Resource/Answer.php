<?php

class Ontic_Survey_Model_Resource_Answer extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/answer', 'answer_id');
    }
}
