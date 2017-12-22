<?php
/**
 * Se manejan llamadas de Ajax del diálogo de packed shipment.
 *
 * @author david.slater@interactiv4.com
 */
class Interactiv4_PackedShipment_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /*
     * Se carga un bloque para el diálogo de validación de direcciones
     * o nada, si la dirección enviada ya es válida o si no hay nada que hacer.
     */
    public function addressvalidationdialoghtmlAction()
    {
        
	$orderId = $this->getRequest()->getParam('order');
	$order = Mage::getModel('sales/order');
	if ($orderId)
	{
	    $order->load($orderId);
	}
	$city =  $this->getRequest()->getParam('city');
	$city = $city ? $city : '';
	$postcode = $this->getRequest()->getParam('postcode');
	$postcode = $postcode ? $postcode : '';
        $countryId = $this->getRequest()->getParam('countryid');
        $countryId = $countryId ? $countryId : '';
        $dontCorrectAddress = $this->getRequest()->getParam('dontcorrectaddress');
        
	
	if (!$dontCorrectAddress)
        { 
            $layout = Mage::getSingleton('core/layout');
            $layout->createBlock('i4packedshipment/addressvalidationdialog', 'root')
                    ->setTemplate('i4packedshipment/sales/order/shipment/create/address_validation_dialog.phtml')
                    ->setOrder($order)
                    ->setCity($city)
                    ->setPostcode($postcode)
                    ->setCountryId($countryId);
            $dialogHtml = $layout->addOutputBlock('root')	
                    ->setDirectOutput(false)
                    ->getOutput();	 
            $data['dialogHtml'] = trim($dialogHtml);
        }
        else // Usuario ha indicado que no quiere realizar más cambios a la dirección.
        {
            $data['dialogHtml'] = '';
        }
	$this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));
    }
    
    /*
     * Se devuelve el coste de la transportación de los bultos del envío.
     * Pasamos en el post un array con los pesos de los bultos, el
     * código postal y población del destinario).
     * @return float
     */
    public function getshippingcostAction()
    {
        $orderId = $this->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order')->load($orderId);        
        $carrier = $order->getShippingCarrier();
        if (!Mage::helper('i4packedshipment')->carrierSupportsCalculationOfShippingCosts($carrier)) {
            $data = array();
            $data['error'] = Mage::helper('i4packedshipment')->__('Se intentó calcular el coste del envío, pero el transportista no soporte esta operación.');
            $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));
            
        }
                   
        $weightsBultos = $this->getRequest()->getParam('weightsBultos');
        $shippingAddress = $order->getShippingAddress();
       
        // La llamada Ajax nos va a pasar la población y el código postal 
        // pero sólo en el caso de que tengamos validación de direcciones.
        // Si no, los tendremes que sacar del pedido.
        $city = $this->getRequest()->getParam('city');
        $city = $city ? $city : $shippingAddress->getCity();
        $postcode = $this->getRequest()->getParam('postcode');
        $postcode = $postcode ? $postcode : $shippingAddress->getPostcode();
        
        
        
        $shippingCost = $carrier->getShippingCost(
            $order, 
            $city,
            $postcode,
            $weightsBultos, 
            $errorStr);   
        
        $data = array();
        $data['shippingcost'] = Mage::helper('core')->currency($shippingCost, true, false);
        
        // Se devuelve el coste del envío para guardar para los informes del 
        // módulo de Shipping Reports. 
        $data['shippingreportsshippingcost'] = $shippingCost;
        
        $profit = $order->getBaseShippingAmount() - $shippingCost;
        $data['profit'] = Mage::helper('core')->currency($profit, true, false);
      
        
        $data['profitcolor'] = $profit >= 0 ? 'Black' : 'Red';
        
       
        
        $data['error'] = $errorStr;
        
        $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Mage::helper('core')->jsonEncode($data));
    }
}
?>
