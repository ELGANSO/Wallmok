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
class Interactiv4_RedsysPro_Model_Redsyspro_Refund extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('i4redsyspro/redsyspro_refund');
    }


    public function addRefundLog($result, $parentId, $refunded)
    {
            $this->_getHelper()->log(__METHOD__);
            $this->setEntityId($parentId)
            ->setStatus("OK")
            ->setTxAuthNo($result->OPERACION->Ds_AuthorisationCode)
            ->setAmountRefunded($refunded)
            ->setRefundedOn(date('Y-m-d H:i:s'))
            ->save();
            $this->_getHelper()->log("Refund Log succesfully added to i4redsyspro_refunds table");
            $this->_getHelper()->log("");
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }

}