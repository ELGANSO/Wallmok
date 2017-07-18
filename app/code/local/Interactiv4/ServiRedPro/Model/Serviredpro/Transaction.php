<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
 class Interactiv4_ServiRedPro_Model_Serviredpro_Transaction extends Mage_Core_Model_Abstract
{
	 /** Order instance
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('serviredpro/serviredpro_transaction');
    }
	public function addTransaction($parentId,$authorized,$payment)
	{
		$this->setParentId($parentId)
		->setEntityId($parentId)
		->setAuthorised($authorized)
		->setSecurityKey($payment->getSecurityKey())
		->save();
	}
}
