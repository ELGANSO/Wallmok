<?php
/**
 * Description of Addressvalidation
 *
 * @author david.slater@interactiv4.com
 */
class Interactiv4_PackedShipment_Block_Addressvalidationdialog extends Mage_Adminhtml_Block_Template
{
    /*
     * @see setOrder
     */
    protected $_order;
    protected $_postcode;
    protected $_city;
    protected $_countryId;
    /*
     * Se establece el pedido para el bloque
     * @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }
    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /*
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->_postcode = $postcode;
        return $this;
    }
    public function getPostcode()
    {
        return $this->_postcode;
    }
    /*
     * @param string $city
     */
    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }
    public function getCity()
    {
        return $this->_city;
    }
    /**
     *
     * @param string $countryId
     * @return \Interactiv4_PackedShipment_Block_Addressvalidationdialog
     */
    public function setCountryId($countryId)
    {
        $this->_countryId = $countryId;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_countryId;
    }
}
?>
