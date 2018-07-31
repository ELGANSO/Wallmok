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
 class Interactiv4_RedsysPro_Block_Adminhtml_Sales_Order_Payment extends Mage_Adminhtml_Block_Sales_Order_Payment
 {
     /**
      * @param $payment
      * @return $this
      */
    public function setPayment($payment)
    {
    	parent::setPayment($payment);
        $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($payment);

        if ($payment->getMethod() == 'i4redsyspro') {

            $paymentInfoBlock->setTemplate('payment/info/i4redsyspro.phtml');

        }
        $this->setChild('info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }

     /**
      * @return string
      */
    protected function _toHtml()
    {
        return $this->getChildHtml('info');
    }
 }
