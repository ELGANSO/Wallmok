<?php
/**
 * Description of Packagelist
 *
 * @author davidslater
 */
class Interactiv4_PackedShipment_Model_Packedshipment {
    protected $_packages = array();
    /**
     *
     * @var Mage_Sales_Model_Order_Shipment 
     */
    protected $_shipment = null;
    
    /**
     * Indica si el packed shipment se ha creado a través del proceso de 
     * envío en masivo en un bulto. 
     * @var boolean 
     */
    protected $_bulkSend = false;
    /**
     *
     * @var float 
     */
    protected $_shippingCost = null;
    public function __construct($shipment) {
        $this->_bulkSend = $this->_getHelper()->isRegisteredBulkSend();
        if (is_array($shipment) && count($shipment) > 0) {
            $arguments = $shipment;
            $keys = array_keys($arguments);
            $shipment = array_key_exists('shipment', $arguments) ? $arguments['shipment'] : $arguments[$keys[0]];
        }
        if (!($shipment instanceof Mage_Sales_Model_Order_Shipment)) {
            $message = "Invalid argument of type " . is_object($shipment) ? get_class($shipment) : gettype($shipment);
            $message = $this->_getHelper()->log($message, __METHOD__, __LINE__);
            throw new Exception($message);
        }
        $this->_shipment = $shipment;
        if (!$this->_getHelper()->carrierSupportsPackedShipment($shipment)) {
            return;
        }
        if (!$this->_shouldCommunicateShipment()) {
            return;
        }
        if (!$this->_dialogWasSkipped()) {
            $this->_loadPackagesFromDialog();
        } else {
            $this->_createOnePackageForShipment();
        }
    }
    /**
     *
     * @return Interactiv4_PackedShipment_Model_Carrier_Interface 
     */
    protected function _getPackedShipmentCarrier() {
        return $this->_shipment->getOrder()->getShippingCarrier();
    }
    /**
     *
     * @return boolean 
     */
    protected function _shouldCommunicateShipment() {
        if ($this->_bulkSend) {
            return true;
        } else {
            return $this->_getRequest()->getParam('packedshipment_communicate_shipment') == 'Y' ? true : false;
        }
    }
    /**
     *
     * @return  Mage_Core_Controller_Request_Http
     */
    protected function _getRequest() {
        return Mage::app()->getRequest();
    }
    /**
     * Determinamos si el cliente ha omitido el diálogo de packed shipment. 
     * Si estamos creando un packed shipment a través del proceso cron de bulk send,
     * devolvemos automáticamente true.
     * Si no, miramos si el cliente tiene la opción de omitir el diálogo de packed shipment por defecto, 
     * y si sí, si ha optado omitir el díalogo.
     * En cualquier otro caso, el cliente está forzado usar el diálogo, así que devolvemos false.
     * @return boolean 
     */
    protected function _dialogWasSkipped() {
        if ($this->_bulkSend) {
            return true;
        } elseif ($this->_getHelper()->skipDialogByDefault($this->_shipment->getStoreId())) {
            $packedShipment = $this->_getRequest()->getParam('packedshipment');
            if (!is_array($packedShipment) || !array_key_exists('show_dialog', $packedShipment)) {
                return true;
            } else {
                return $packedShipment['show_dialog'] != 'Y';
            }
        } else {
            return false;
        }
    }
    /**
     * Si hemos omitido el diálog de agrupación de bultos.
     * @return boolean
     */
    public function dialogWasSkipped() {
        return $this->_dialogWasSkipped();
    }
    /**
     *
     * @return boolean 
     */
    protected function _loadPackagesFromDialog() {
        $packages = Mage::app()->getRequest()->getParam('packages');
        if (!is_array($packages)) {
            return false;
        }
        foreach ($packages as $packageData) {
            //para evitar errores comprobamos que cada package tiene productos
            if (!empty($packageData['ids'])) {
                $package = new Interactiv4_PackedShipment_Model_Package($this->_shipment, $packageData['ids'], $packageData['ref']);
                $this->_packages[] = $package;
            }
        };
        return true;
    }
    /**
     *
     * @return boolean 
     */
    protected function _createOnePackageForShipment() {
        $refs = array();
        $ids = array();
        foreach ($this->_shipment->getAllItems() as $item) { /* @var $item Mage_Sales_Model_Order_Shipment_Item */
            if ($item->getOrderItem()->getParentItemId()) {
                continue;
            }
            for ($i = 0; $i < $item->getQty(); $i++) {
                $ids[] = $item->getProductId();
                $refs[] = $item->getSku();
            }
        }
        $ref = implode(" ", $refs);
        $package = new Interactiv4_PackedShipment_Model_Package($this->_shipment, $ids, $ref);
        $this->_packages = array($package);
        return true;
    }
    /**
     *
     * @param string &$errorStr
     * @return boolean 
     */
    public function getShippingCost(&$errorStr) {
        if (!$this->_getPackedShipmentCarrier()->supportsCalculationOfShippingCosts()) {
            return false;
        }
        if (!isset($this->_shippingCost)) {
            if ($this->_dialogWasSkipped()) {
                // Como se ha omitido el diálogo, no habremos calculado el coste de envío. Ahora lo haremos.
                $order = $this->_shipment->getOrder();
                $address = $order->getShippingAddress() ? $order->getShippingAddress() : $order->getBillingAddress(); /* @var $address Mage_Sales_Model_Order_Address */
                $errorStr = null;
                $shippingCost = $this->_getPackedShipmentCarrier()
                        ->getShippingCost($order, $address->getCity(), $address->getPostcode(), $this->_getPackageWeights(), $errorStr);
                if (!$errorStr) {
                    $this->_shippingCost = $shippingCost;
                } else {
                    $this->_shippingCost = false;
                }
            } else {
                $shippingCost = $this->_getRequest()->getParam('i4ShippingResportsShippingCost');
                if (isset($shippingCost) && strlen($shippingCost) > 0) {
                    $this->_shippingCost = $shippingCost;
                } else {
                    $this->_shippingCost = false;
                }
            }
        }
        return $this->_shippingCost;
    }
    /**
     *
     * @return array
     */
    public function getPackages() {
        return $this->_packages;
    }
    /**
     *
     * @return boolean 
     */
    public function hasPackages() {
        return count($this->_packages) > 0;
    }
    /**
     *
     * @return float 
     */
    public function getTotalWeight() {
        return array_sum($this->_getPackageWeights());
    }
    /**
     *
     * @return array 
     */
    protected function _getPackageWeights() {
        $packageWeights = array();
        foreach ($this->_packages as $package) { /* @var $package Interactiv4_PackedShipment_Model_Package */
            $packageWeights[] = $package->getPackageWeight();
        }
        return $packageWeights;
    }
    /**
     *
     * @return float 
     */
    public function getTotalPrice() {
        $totalPrice = 0.0;
        foreach ($this->_packages as $package) { /* @var $package Interactiv4_PackedShipment_Model_Package */
            $totalPrice += $package->getPackagePrice();
        }
        return $totalPrice;
    }
    /**
     *
     * @return Interactiv4_PackedShipment_Helper_Data 
     */
    protected function _getHelper() {
        return Mage::helper('i4packedshipment');
    }
}
?>
