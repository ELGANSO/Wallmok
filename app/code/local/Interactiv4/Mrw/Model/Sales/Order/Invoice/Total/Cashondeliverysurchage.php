<?php
class Interactiv4_Mrw_Model_Sales_Order_Invoice_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $invoice->setData('i4mrwes_cashondelivery_surcharge', 0);
        $invoice->setData('base_i4mrwes_cashondelivery_surcharge', 0);
        $invoice->setData('i4mrwes_cashondelivery_surcharge_tax', 0);
        $invoice->setData('base_i4mrwes_cashondelivery_surcharge_tax', 0);
        $order = $invoice->getOrder(); 
        $amount =$order->getData('i4mrwes_cashondelivery_surcharge');
        if ($amount) {
            // Miramos las facturas para ver si ya se ha cobrado el sobrecargo contrareembolso.
            foreach ($order->getInvoiceCollection() as $previousInvoice) { /* @var $previousInvoice Mage_Sales_Model_Order_Invoice */
                if (!$previousInvoice->isCanceled() && $previousInvoice->getData('base_i4mrwes_cashondelivery_surcharge')) {
                    return $this;
                } 
            }
            $invoice->setData('i4mrwes_cashondelivery_surcharge', $amount);
            
            $baseAmount = $order->getData('base_i4mrwes_cashondelivery_surcharge');
            $invoice->setData('base_i4mrwes_cashondelivery_surcharge', $baseAmount);
            
            
            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);
            
            // NB. No añadimos los impuestos al grand total aquí. 
            // Se añaden en Interactiv4_Mrw_Model_Sales_Order_Invoice_Total_Tax. 
            
        }
        return $this;
    }
}
