<?php
class Interactiv4_Mrw_Model_Sales_Order_Creditmemo_Total_Cashondeliverysurchage extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $creditmemo->setData('i4mrwes_cashondelivery_surcharge', 0);
        $creditmemo->setData('base_i4mrwes_cashondelivery_surcharge', 0);
        $order = $creditmemo->getOrder();
        // Comprueba los creditmemo de antes para ver si ya hemos devuelto 
        $amount = $order->getData('i4mrwes_cashondelivery_surcharge');
        if ($amount) {
            foreach ($order->getCreditmemosCollection() as $previousCreditMemo) { /* @var $previousCreditMemo Mage_Sales_Model_Order_Creditmemo */
                if ($previousCreditMemo->getData("base_i4mrwes_cashondelivery_surcharge")) {
                    return $this;
                }
            }
            // NB. Aquí solo añadimos la base imponible al total. Los impuestos 
            // se añaden en Interactiv4_Mrw_Model_Sales_Order_Creditmemo_Total_Tax
            $creditmemo->setData('i4mrwes_cashondelivery_surcharge', $amount);
            $baseAmount = $order->getData('base_i4mrwes_cashondelivery_surcharge');
            $creditmemo->setData('base_i4mrwes_cashondelivery_surcharge', $baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount );
            
        }
        return $this;
    }
}
