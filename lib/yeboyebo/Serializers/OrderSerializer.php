<?php
require_once ('AddressSerializer.php');
class OrderSerializer
{
    public function serialize(\Mage_Sales_Model_Order $order)
    {
        $addressSerializer = new AddressSerializer();

        $shipping = floatval($order->getBaseShippingInclTax());
		$surcharge = floatval($order->getBaseFoomanSurchargeAmount());
		$gastos = $shipping + $surcharge;
	    $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        $data = [
            'entity_id' => (int) $order['entity_id'],
            'increment_id' => (int) $order['increment_id'],
            'created_at' => $order['created_at'],
            'payment_method' => $this->_getPaymentMethodTitle($order->getPayment()->getMethod()),
            'email' => $order['customer_email'],
            'cif' => $order->getBillingAddress()['vat_id'],
            'customer_id' => $order->getCustomerId(),
            'billing_address' => $addressSerializer->serializeAddress($order->getBillingAddress()),
            'shipping_address' => $addressSerializer->serializeAddress($order->getShippingAddress()),
            'shipping_method' => $order->getShippingMethod(),
            'shipping_price' =>  $gastos,
            'tracking_number' => $this->_getShipmentTrackingNumber($order),
            'weight' => (float) $order['weight'],
            'units' => $order->getTotalItemCount(),
            'subtotal' => (float) $order['subtotal'],
            'tax_amount' => (float) $order['tax_amount'],
            'discount_amount' => (float) $order['discount_amount'],
            'grand_total' => (float) $order['grand_total'],
            'franja' => $quote->getData('franja'),
            'fechaRecogida' => $this->_formatedDate($quote->getData('fecharecogida'))
        ];


        foreach($order->getAllItems() as $item)
        {
            $data['items'][] = $this->serializeOrderItem($item, $quote);
        }
		Mage::log($data['items'],null,"ivan.log");
        //throw new Exception('STOP');
        return $data;
    }

    public function serializeOrderItem(\Mage_Sales_Model_Order_Item $item,$quote)
    {
        $product = $item->getProduct();

         return [
            'sku' => explode('-',$item->getSku())[0],
            'nombre' => $item->getName(),
            'cantidad' => (int) $item->getQtyOrdered(),
            'iva' => (float) $item->getTaxPercent(),
            'pvptotaliva' => (float) $item->getRowTotalInclTax(),
            'pvptotal' => (float) $item->getRowTotal(),
            'discount' => (float) $item->getDiscountAmount(),
            'ivaincluido' => (float) $item->getBasePriceInclTax(),
			'dtolineal' => (float) $item->getBaseDiscountAmount(),
			'pvpunitario' => (float) $item->getBasePrice(),
			'pvpunitarioiva' => (float) $item->getBasePriceInclTax(),
			'pvpsindto' => (float)($item->getBasePrice() * $item->getQtyOrdered()),
			'pvpsindtoiva' => (float)($item->getBasePriceInclTax() * $item->getQtyOrdered()),
            'franja' => $quote->getData('franja'),
            'fechaRecogida' => $this->_formatedDate($quote->getData('fecharecogida')),
	         'cooked_cost' => Mage::getModel('catalog/product')->load($product->getId())->getData('cooked_cost')
        ];

    }
    private function _getShipmentTrackingNumber($order) {
        $numbers = array();
       	$tracks = Mage::getResourceModel('sales/order_shipment_track_collection')->setOrderFilter($order);
        foreach ($tracks as $track) {
            $numbers[] = $track['track_number'];
        }
        if (count($numbers) > 0) {
            return implode(', ', $numbers);
        }
        return false;
    }
     private function _getPaymentMethodTitle($code) {
        if ($code) {
            return Mage::getStoreConfig('payment/' . $code . '/title');
        }
        return false;
    }
    private function _formatedDate($date){
    	$date = explode("-",$date);
    	$aux = $date[0];
    	$date[0] = $date[2];
    	$date[2] = $aux;
    	return implode("/",$date);
    }
}