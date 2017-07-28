<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of Tax
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Model_Sales_Order_Creditmemo_Total_Tax extends Mage_Sales_Model_Order_Creditmemo_Total_Tax {
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        
        $result = parent::collect($creditmemo);
        
        // Añadimos los impuestos del sobrecargo contrareembolso, si hay.
        $creditmemo->setData("i4mrwes_cashondelivery_surcharge_tax", 0);
        $creditmemo->setData("base_i4mrwes_cashondelivery_surcharge_tax", 0);        
        if ($creditmemo->getData("i4mrwes_cashondelivery_surcharge")) {
            $baseTax = $creditmemo->getOrder()->getData("base_i4mrwes_cashondelivery_surcharge_tax");
            $creditmemo->setData("base_i4mrwes_cashondelivery_surcharge_tax", $baseTax);
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTax);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTax);
            
            $tax = $creditmemo->getOrder()->getData("i4mrwes_cashondelivery_surcharge_tax");
            $creditmemo->setData("i4mrwes_cashondelivery_surcharge_tax", $tax);
            $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $tax);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $tax);
        }
        
        return $result; 
    }
}
?>
