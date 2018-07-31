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
class Interactiv4_RedsysPro_Model_Config_Source_Signaturealgorithm
{

	CONST SHA1_ALGORITHM 		= 1;
	CONST SHA256_ALGORITHM 		= 2;

	public function toOptionArray()
    {
		return array(
			array(
				'value'	=> self::SHA1_ALGORITHM,
				'label' => $this->_getHelper()->__('SHA-1')
			),
			array(
				'value'	=> self::SHA256_ALGORITHM,
				'label' => $this->_getHelper()->__('SHA-256')
			),
		);
	}

	/**
	 *
	 * @return Interactiv4_RedsysPro_Helper_Data
	 */
	protected function _getHelper() {
		return Mage::helper('i4redsyspro');
	}
}