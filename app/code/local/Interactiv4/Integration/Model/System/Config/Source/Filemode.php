<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Source_Filemode
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => FTP_ASCII,
                'label' => Mage::helper('i4integration')->__('ASCII')
            ),
            array(
                'value' => FTP_BINARY,
                'label' => Mage::helper('i4integration')->__('Binary')
            )
        );
    }

}