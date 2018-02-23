<?php

class Ontic_Survey_Model_Resource_Response extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('survey/response', 'response_id');
    }
}
