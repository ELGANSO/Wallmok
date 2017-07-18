<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
class Interactiv4_ServiRedPro_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
    {
		parent::_construct();
		$this->setTemplate('payment/form/serviredpro.phtml');
	}
}
