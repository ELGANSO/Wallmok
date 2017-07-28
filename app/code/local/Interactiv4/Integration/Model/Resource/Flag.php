<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Resource_Flag extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {    
        $this->_init('i4integration/flag', 'integration_flag_id');
    }
    
}