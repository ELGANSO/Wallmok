<?php
/**
 * ServiRedPro
 *
 * @category    Interactiv4
 * @package     Interactiv4_ServiRedPro
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
 class Interactiv4_ServiRedPro_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
 {
    public function setPayment($payment)
    {
    	parent::setPayment($payment);
        $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($payment);
        if ($payment->getMethod() == 'serviredpro') {
            $paymentInfoBlock->setTemplate('payment/info/serviredpro.phtml');
        }
        $this->setChild('info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }
    protected function _toHtml()
    {
        return $this->getChildHtml('info');
    }
 }
