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
class Interactiv4_RedsysPro_Model_Config_Source_Consumerlanguage
{
	public function toOptionArray()
	{
		return array(
            array(
                'value' => '001',
                'label' => Mage::helper('adminhtml')->__('Spanish')
            ),
            array(
                'value' => '002',
                'label' => Mage::helper('adminhtml')->__('English')
            ),
            array(
                'value' => '003',
                'label' => Mage::helper('adminhtml')->__('Catalan')
            ),
            array(
                'value' => '004',
                'label' => Mage::helper('adminhtml')->__('French')
            ),
            array(
                'value' => '005',
                'label' => Mage::helper('adminhtml')->__('German')
            ),
            array(
                'value' => '006',
                'label' => Mage::helper('adminhtml')->__('Dutch')
            ),
            array(
                'value' => '007',
                'label' => Mage::helper('adminhtml')->__('Italian')
            ),
            array(
                'value' => '008',
                'label' => Mage::helper('adminhtml')->__('Swedish')
            ),
            array(
                'value' => '009',
                'label' => Mage::helper('adminhtml')->__('Portuguese')
            ),
            array(
                'value' => '010',
                'label' => Mage::helper('adminhtml')->__('Valencian')
            ),
            array(
                'value' => '011',
                'label' => Mage::helper('adminhtml')->__('Polish')
            ),
            array(
                'value' => '012',
                'label' => Mage::helper('adminhtml')->__('Galician')
            ),
            array(
                'value' => '013',
                'label' => Mage::helper('adminhtml')->__('Basque')
            ),
            array(
                'value' => '643',
                'label' => Mage::helper('adminhtml')->__('Russian')
            ),
		);
	}
}