<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Sync_In
{

    const IN    = '1';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::IN,
                'label' => Mage::helper('i4integration')->__('To Magento')
            )
        );
    }

}