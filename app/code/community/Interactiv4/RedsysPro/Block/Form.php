<?php
/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 * @author      David Slater
 */
class Interactiv4_RedsysPro_Block_Form extends Mage_Payment_Block_Form
{
	/**
	 * Class Constructor
	 */
	protected function _construct()
    {
		parent::_construct();
		$this->setTemplate('payment/form/i4redsyspro.phtml');
	}

	/**
	 * Retrieve redsys helper
	 *
	 * @return Interactiv4_RedsysPro_Helper_Data
	 */
	protected function _getHelper() {
		return Mage::helper('i4redsyspro');
	}
}