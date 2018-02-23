<?php

namespace Ontic\SyncApi\Serializers;

class OrderSerializer
{
    public function serialize(\Mage_Sales_Model_Order $order)
    {
        $addressSerializer = new AddressSerializer();

        $data = [
            'entity_id' => (int) $order['entity_id'],
            'created_at' => $order['created_at'],
            'payment_method' => $order->getPayment()->getMethod(),
            'email' => $order['customer_email'],
            'billing_address' => $addressSerializer->serializeAddress($order->getBillingAddress()),
            'shipping_address' => $addressSerializer->serializeAddress($order->getShippingAddress()),
            'weight' => (float) $order['weight'],
            'subtotal' => (float) $order['subtotal'],
            'tax_amount' => (float) $order['tax_amount'],
            'discount_amount' => (float) $order['discount_amount'],
            'grand_total' => (float) $order['grand_total'],
        ];

        foreach($order->getAllVisibleItems() as $item)
        {
            $data['items'][] = $this->serializeOrderItem($item, $order);
            Mage::log($data['items'],null,"ivan.log");
        }

        return $data;
    }

    public function serializeOrderItem(\Mage_Sales_Model_Order_Item $item, $order)
    {
        $product = $item->getProduct();

        return [
            'sku' => $product['sku'],
            'qty' => (int) $item->getQtyOrdered(),
            'tax_percent' => (float) $item->getTaxPercent(),
            'total' => (float) $item->getRowTotalInclTax(),
            'subtotal' => (float) $item->getRowTotal(),
            'discount' => (float) $item->getDiscountAmount()
        ];
    }
}