<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Flag extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('i4integration/flag');
    }
        
    public function getFlagByCode($process_code)
    {
        $collection = Mage::getModel('i4integration/flag')->getCollection()
                        ->addFieldToFilter('code', array('eq' => $process_code));
        $processes = $collection->getItems();
        $flag = null;
        foreach ($processes as $process) {
            return $process;
        }
        return false;
    }
    
    public function updateFlag($process_id, $flag)
    {
        $model = Mage::getModel('i4integration/flag')->load($process_id);
        $model->setFlag($flag);
        $model->save();
    }
    
}