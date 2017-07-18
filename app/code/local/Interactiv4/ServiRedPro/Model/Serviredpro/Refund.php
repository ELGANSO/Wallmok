<?php
class Interactiv4_ServiRedPro_Model_Serviredpro_Refund extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('serviredpro/serviredpro_refund');
    }
    public function addRefundLog($result, $parentId, $refunded)
    {
            $this->_getHelper()->log(__METHOD__);
            $this->setEntityId($parentId)
            ->setStatus("OK")
            ->setStatusDetail("")
            ->setVpsTxId("")
            ->setTxAuthNo($result->OPERACION->Ds_AuthorisationCode)
            ->setAmountRefunded($refunded)
            ->setRefundedOn(date('Y-m-d H:i:s'))
            ->save();
            $this->_getHelper()->log("Refund Log succesfully added to serviredpro_refunds table");
            $this->_getHelper()->log("");
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }
}
