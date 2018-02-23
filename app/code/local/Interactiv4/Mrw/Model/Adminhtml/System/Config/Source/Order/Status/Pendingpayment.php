<?php
/**
 * Description of Pendingpayment
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Model_Adminhtml_System_Config_Source_Order_Status_Pendingpayment extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
   // protected $_stateStatuses = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
        Mage_Sales_Model_Order::STATE_PROCESSING,
    );
}
?>
