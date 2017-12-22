<?php
/**
 * Description of Tax
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Tax {
    
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $result = parent::collect($invoice);
        // Añadimos los impuestos del sobrecargo contrareembolso, si hay. 
        $invoice->setData("i4mrwes_cashondelivery_surcharge_tax", 0);
        $invoice->setData("base_i4mrwes_cashondelivery_surcharge_tax", 0);        
        if ($invoice->getData("i4mrwes_cashondelivery_surcharge")) {
            $baseTax = $invoice->getOrder()->getData("base_i4mrwes_cashondelivery_surcharge_tax");
            $invoice->setData("base_i4mrwes_cashondelivery_surcharge_tax", $baseTax);
            
            $tax = $invoice->getOrder()->getData("i4mrwes_cashondelivery_surcharge_tax");
            $invoice->setData("i4mrwes_cashondelivery_surcharge_tax", $tax);           
            
            // Según el cálculo que se realiza en la clase padre, si se deveuelve
            // true de "isLast", ya habremos añadido todos los impuestos 
            // (incluyendo los del contrarreembolso al total de impuestos y el gran total
            // del pedido). Entonces, sólo en el caso de que se devuelva false de 
            // la llamada a "isLast" añadimos los impuestos del contrarrembolso a los totales.            
            if (!$invoice->isLast()) {
                $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTax);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTax);
                $invoice->setTaxAmount($invoice->getTaxAmount() + $tax);
                $invoice->setGrandTotal($invoice->getGrandTotal() + $tax);
            }
        }
        
        return $result;         
        
    }
}
?>
