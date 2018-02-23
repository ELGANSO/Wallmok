<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Process extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('i4integration/process');
    }
    
    public function getOptionArray()
    {
        $collection = Mage::getSingleton('i4integration/process')->getCollection();
        $options = array();
        foreach ($collection as $process) {
            $options[$process->getCode()] = $process->getLabel();
        }
        return $options;
    }
    
}