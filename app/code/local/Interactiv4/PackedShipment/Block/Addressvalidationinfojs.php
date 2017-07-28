<?php
/**
 * Bloque que se inyecciona en la página 'new shipment' que contiene información 
 * que necesita el servicio de validación de direcciones.
 *
 * @author david.slater@interactiv4.com
 */
class Interactiv4_PackedShipment_Block_Addressvalidationinfojs extends Mage_Core_Block_Template
{
    /*
     * @see _getOrder()
     */
    protected $_order;
    
    /*
     * Se devuelve el pedido actual.
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
	// Dado que este bloque se muestra en la página de 'new shipment'
	// sabemos que se encuentra el id del pedido actual en el parametro querystring
	// order_id.
	if (!$this->_order)
	{
	    $this->_order = Mage::getModel('sales/order');
	    $orderId = $this->getRequest()->getParam('order_id');
	    if ($orderId)
	    {
		$this->_order->load($orderId);
	    }
	}
	return $this->_order;
    }
    
    /*
     * Se devuelve 'true' se el carrier del actual pedido permite la validación de
     * direcciones.
     * @param string $countryId 
     * @return bool
     */    
    public function isAddressValidationAvailable()
    {
	if ($this->_getOrder()->getId())
	{
	    $carrier = $this->_getOrder()->getShippingCarrier();
            return $this->helper('i4packedshipment')->carrierSupportsAddressValidation($carrier, $this->getShippingAddressCountryId());
	}
	return false;
    }
    
    /*
     * Se devuelve la población de la dirección de envío del pedido actual.
     * @return string
     */
    public function getShippingAddressCity()
    {
	if ($this->_getOrder()->getId())
	{
	    return $this->_getOrder()->getShippingAddress()->getCity();	   
	}
	return '';
    }
    
     /*
     * Se devuelve  el código postal de la dirección de envío del pedido actual.
     * @return string
     */
    public function getShippingAddressPostcode()
    {
	if ($this->_getOrder()->getId())
	{
	    return $this->_getOrder()->getShippingAddress()->getPostcode();	   
	}
	return '';
    }   
    
    /**
     * Se devuelve el código del país de la dirección de envío del pedido actual.
     * @return string 
     */
    public function getShippingAddressCountryId() {
 	if ($this->_getOrder()->getId())
	{
	    return $this->_getOrder()->getShippingAddress()->getCountryId();	   
	}
	return '';
    }
    
    /*
     * Se devuelve el id del pedido actual.
     * @return int
     */
    public function getOrderId()
    {
	$orderId = $this->_getOrder()->getId();
	return $orderId ? $orderId : 0;
    }
    
    /*
     * Se devuelve el URL de la acción que provee el HTML que parece en el díalogo 
     * de validación de direcciones.
     * @return string
     */
    public function getAddressValidationDialogHtmlActionUrl()
    {
	return $this->helper("adminhtml")->getUrl('i4packedshipment/adminhtml_index/addressvalidationdialoghtml');
    }
    
    
}
?>
