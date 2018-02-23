<?php

class Ontic_Survey_Model_Resource_Question_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('survey/question');
    }
}
