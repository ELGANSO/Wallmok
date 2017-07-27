<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */
 
class Interactiv4_Integration_Model_System_Config_Source_Log_Email
{
 
    protected static $_options;
 
    const LOG_NO      = '1';
    const LOG_FULL    = '2';
    const LOG_ERROR   = '3';
    
 
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = array(
                array(
                    'label' => Mage::helper('i4integration')->__('No'),
                    'value' => self::LOG_NO,
                ),
                array(
                    'label' => Mage::helper('i4integration')->__('Same report as Logs'),
                    'value' => self::LOG_FULL,
                ),
                array(
                    'label' => Mage::helper('i4integration')->__('Generic message only'),
                    'value' => self::LOG_ERROR,
                )                
            );
        }
        return self::$_options;
    }
 
}
