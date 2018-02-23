<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Model_Config_Source_Signaturemethod
{
	public function toOptionArray()
    {
		return array(
			array('value'=>1, 'label' => Mage::helper('serviredpro')->__('Complete')),
			array('value'=>2, 'label' => Mage::helper('serviredpro')->__('Complete extended')),
		);
	}
}
