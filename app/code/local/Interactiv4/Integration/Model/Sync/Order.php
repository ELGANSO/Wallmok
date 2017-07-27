<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Sync_Order extends Mage_Core_Model_Abstract
{

    protected $_process_name = 'i4sync_order';
    private $_remote_data;
    private $_logger;
    private $_mapped_in_columns;
    
    private $_mark_as_exported = array();
    
    public function execute() {
        $_sync_enable = Mage::getStoreConfig($this->_process_name . '/sync/enable');
        if ($_sync_enable) {
            $this->_getLogger()->cleanOld($this->_process_name);
            $this->_deleteOldFiles();
            $_sync_type = Mage::getStoreConfig($this->_process_name . '/sync/type');
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::IN || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                //
            }
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::OUT || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                if (!$this->_createData()) {
                    return false;
                }
                $this->_markExportedOrders();
                $_files = $this->_sendFiles();
                $this->_getLogger()->saveLog('Export process has finished.', $this->_process_name);
            }
        } else {
            $this->_getLogger()->saveLog('Sync Order is fully disabled.', $this->_process_name);
        }
        $this->sendEmail();
        return true;
    }
    
    private function _sendFiles() {
        $_files = $this->_getFilesForUpload();
        $_use_ftp = Mage::getStoreConfig($this->_process_name . '/ftp/enable');
        if ($_use_ftp == 1) {
            $_ftp = Mage::helper('i4integration/ftp');
            $_ftp->openConnection($this->_process_name);
            $_ftp->doLogin();
            $_file_io = new Varien_Io_File();
            $_file_io->setAllowCreateFolders(true);
            $_ftp->checkFolderExists(Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS . 'uploaded');
            foreach($_files as $file) {
                $_ftp_upload_result = false;
                $_ftp_upload_result = $_ftp->uploadFile(Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local'), Mage::getStoreConfig($this->_process_name . '/upload/remote'), $file);
                if ($_ftp_upload_result == true) {
                    $_file_io->mv(Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS . $file, Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS . 'uploaded' . DS . $file);
                }
            }    
            $_ftp->closeConnection();
            unset($_ftp);
            unset($_file_io);
            $this->_getLogger()->saveLog(count($_files) . ' file(s) uploaded.', $this->_process_name);
        }
        return count($_files);
    }
    
    private function _getFilesForUpload() {
        $_files = array();
        $_path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS;
        $_directory = dir($_path);
        while ($_file = $_directory->read()) {
            if (!is_dir($_path . $_file) && is_file($_path . $_file)) {
                $_files[] = $_file;
            }
        }
        $_directory->close();
        return $_files;
    }
    
    private function _createData() {
	$_orders = $this->_getOrders();

	if($_orders->getSize() > 0) {
		$path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS;
		$file = new Varien_Io_File();
		$file->setAllowCreateFolders(true);
		$file->open(array (
		    'path' => $path
		));
		$file->streamOpen($this->_getMapperOutFileName());
		$_orders = $this->_getOrders();
		foreach ($_orders as $_order) {
		    $_header = $this->_getOrderHeader($_order);
		    if ($_header) {
		        $file->streamWrite($_header);
		    }
		    $_line = $this->_getOrderLine($_order);
		    if ($_line) {
		        $file->streamWrite($_line);
		    }
		    $_promotion = $this->_getOrderPromotion($_order);
		    if ($_promotion) {
		        $file->streamWrite($_promotion);
		    }
		    $_shipment = $this->_getOrderShipment($_order);
	//Mage::log($_order);
		    if ($_shipment) {
		        $file->streamWrite($_shipment);
		    }
		    $this->_mark_as_exported[] = $_order->getEntityId();
		}
		$file->streamClose();
	}

        $this->_getLogger()->saveLog($_orders->getSize() . ' orders(s) exported.', $this->_process_name);
        return true;
    }
    
    private function _markExportedOrders() {
        if (count($this->_mark_as_exported) > 0) {
            $resource = Mage::getSingleton('core/resource');
            $conn = $resource->getConnection('core_write');
            $table = $resource->getTableName('sales/order');
            $ids = implode(',', $this->_mark_as_exported);
            $query = "UPDATE {$table} SET is_exported = 1 WHERE entity_id IN(" . $ids . ")";
            $conn->query($query);
        }
    }
    
    private function _getOrders() {
        $_order_status = explode(',', Mage::getStoreConfig($this->_process_name . '/mapper_out/order_status'));
        
        $_collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('is_exported', array(
                                                array('is_exported', 'null' => true),
                                                array('is_exported', 'neq' => 1)
                                ))
            ->addFieldToFilter('status', array('in' => $_order_status));
        return $_collection;
    }
    
    private function _getOrderHeader($order) {
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
        $_line = '';
        //TODO: buscar otro approach
        $_header_names = array();
        $_header_names[] = $_field_enclosure . 'TIPO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NUM PEDIDO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'FECHA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TIPO TRANSACCION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'SOLICITA FACTURA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'METODO DE PAGO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'N CLIENTE' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'EMAIL' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NOMBRE FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'APELLIDO FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'DIRECCION FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'COD. POSTAL FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'CIUDAD FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PROVINCIA FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PAIS FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TELEFONO FACTURACION' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NIF' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PESO TOTAL' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'SUBTOTAL' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'IMPUESTOS' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'DESCUENTOS' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TOTAL' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
	$_header_names[] = $_field_enclosure . 'TIPO VIA' . $_field_enclosure;
	$_header_names[] = $_field_enclosure . 'NUMERO' . $_field_enclosure;
	$_header_names[] = $_field_enclosure . 'OTROS' . $_field_enclosure;

        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            $_line .= implode($_field_separator, $_header_names) . "\n";
        }
        $_data = array();
        $_data[] = $_field_enclosure . 'C' . $_field_enclosure; //TIPO
        $_data[] = $_field_enclosure . $order->getIncrementId() . $_field_enclosure; //NUM PEDIDO
        $_date = new Zend_Date(Mage::app()->getLocale()->date($order->getCreatedAt()), Zend_Date::ISO_8601);
        $_data[] = $_field_enclosure . $_date->toString('dd/MM/yyyy HH:mm') . $_field_enclosure; //FECHA
        $_data[] = $_field_enclosure . 'P' . $_field_enclosure; //TIPO TRANSACCION
        $_data[] = $_field_enclosure . '' . $_field_enclosure; //SOLICITA FACTURA - Especificación pendiente
        $_data[] = $_field_enclosure . $this->_getPaymentMethodTitle($order->getPayment()->getMethod()) . $_field_enclosure; //METODO DE PAGO
        $_data[] = $_field_enclosure . $order->getCustomerId() . $_field_enclosure; //N CLIENTE
        $_data[] = $_field_enclosure . $order->getCustomerEmail() . $_field_enclosure; //EMAIL
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getFirstname() . $_field_enclosure; //NOMBRE FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getLastname() . $_field_enclosure; //APELLIDO FACTURACION
$street = $order->getBillingAddress()->getStreet();
	$_data[] = $_field_enclosure . $street[1] . $_field_enclosure; //DIRECCION FACTURACION
        //$_data[] = $_field_enclosure . str_replace("\n", ", ", $order->getBillingAddress()->getStreetFull()) . $_field_enclosure; //DIRECCION FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getPostcode() . $_field_enclosure; //COD. POSTAL FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getCity() . $_field_enclosure; //CIUDAD FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getRegion() . $_field_enclosure; //PROVINCIA FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getCountryId() . $_field_enclosure; //PAIS FACTURACION
        $_data[] = $_field_enclosure . $order->getBillingAddress()->getTelephone() . $_field_enclosure; //TELEFONO FACTURACION
        $vat_number = trim($order->getBillingAddress()->getData('vat_id') !== null ? $order->getBillingAddress()->getData('vat_id') : $order->getCustomerTaxvat());
        //$_data[] = $_field_enclosure . $order->getCustomerTaxvat() . $_field_enclosure; //NIF
        $_data[] = $_field_enclosure . $vat_number . $_field_enclosure; //NIF
        $_data[] = $_field_enclosure . $order->getWeight() . $_field_enclosure; //PESO TOTAL
        $_data[] = $_field_enclosure . $order->getBaseSubtotal() . $_field_enclosure; //SUBTOTAL
        $_data[] = $_field_enclosure . $order->getBaseTaxAmount() . $_field_enclosure; //IMPUESTOS
        $_data[] = $_field_enclosure . $order->getBaseDiscountAmount() . $_field_enclosure; //DESCUENTOS
        $_data[] = $_field_enclosure . $order->getBaseGrandTotal() . $_field_enclosure; //TOTAL
        $_data[] = $_field_enclosure . '' . $_field_enclosure; //
        $_data[] = $_field_enclosure . '' . $_field_enclosure; //

	$_data[] = $_field_enclosure . $street[0] . $_field_enclosure; //TIPO VIA
	$_data[] = $_field_enclosure . $street[2] . $_field_enclosure; //NUMERO
	$_data[] = $_field_enclosure . $street[3] . $_field_enclosure; //OTROS
        $_line .= implode($_field_separator, $_data) . "\n";
        return $_line;
    }
    
    
    private function _getOrderLine($order) {
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
        $_line = '';
        //TODO: buscar otro approach
        $_header_names = array();
        $_header_names[] = $_field_enclosure . 'TIPO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NUM PEDIDO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'SKU' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PESO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'UNIDADES' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PRECIO CON IVA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PRECIO SIN IVA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TIPOS DE IVA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'DESCUENTO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PRECIO CON IVA*QTY' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PRECIO SIN IVA*QTY' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            $_line .= implode($_field_separator, $_header_names) . "\n";
        }
        foreach ($order->getAllItems() as $_item) {
            if ($_item->getParentItem()) {
                continue;
            }
            $_data = array();
            $_data[] = $_field_enclosure . 'L' . $_field_enclosure; //TIPO
            $_data[] = $_field_enclosure . $order->getIncrementId() . $_field_enclosure; //NUM PEDIDO
            $_data[] = $_field_enclosure . $_item->getSku() . $_field_enclosure; //SKU
            $_data[] = $_field_enclosure . $_item->getWeight() . $_field_enclosure; //PESO
            $_data[] = $_field_enclosure . $_item->getQtyOrdered() . $_field_enclosure; //UNIDADES
            $_data[] = $_field_enclosure . $_item->getBasePriceInclTax() . $_field_enclosure; //PRECIO CON IVA
            $_data[] = $_field_enclosure . $_item->getBasePrice() . $_field_enclosure; //PRECIO SIN IVA
            $_data[] = $_field_enclosure . $this->_getCalculatedTax($order) . $_field_enclosure; //TIPOS DE IVA - Performance
            $_data[] = $_field_enclosure . $_item->getBaseDiscountAmount() . $_field_enclosure; //DESCUENTO
            $_data[] = $_field_enclosure . ($_item->getBasePriceInclTax() * $_item->getQtyOrdered()) . $_field_enclosure; //PRECIO CON IVA*QTY
            $_data[] = $_field_enclosure . ($_item->getBasePrice() * $_item->getQtyOrdered()) . $_field_enclosure; //PRECIO SIN IVA*QTY
            $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
            $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
            $_line .= implode($_field_separator, $_data) . "\n";
        }
        return $_line;
    }
    
    private function _getOrderPromotion($order) {
        if ($order->getCouponCode()) {
            $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
            $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
            $_line = '';
            //TODO: buscar otro approach
            $_header_names = array();
            $_header_names[] = $_field_enclosure . 'TIPO' . $_field_enclosure;
            $_header_names[] = $_field_enclosure . 'NUM PEDIDO' . $_field_enclosure;
            $_header_names[] = $_field_enclosure . 'CODIGO' . $_field_enclosure;
            $_header_names[] = $_field_enclosure . 'DESCUENTO' . $_field_enclosure;
            $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
            $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
            if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
                $_line .= implode($_field_separator, $_header_names) . "\n";
            }
            $_data = array();
            $_data[] = $_field_enclosure . 'P' . $_field_enclosure; //TIPO
            $_data[] = $_field_enclosure . $order->getIncrementId() . $_field_enclosure; //NUM PEDIDO
            $_data[] = $_field_enclosure . $order->getCouponCode() . $_field_enclosure; //CODIGO
            $_data[] = $_field_enclosure . $order->getBaseDiscountAmount() . $_field_enclosure; //DESCUENTO
            $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
            $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
            $_line .= implode($_field_separator, $_data) . "\n";
            return $_line;
        }
    }
    
    private function _getOrderShipment($order) {
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
        $_line = '';
        //TODO: buscar otro approach
        $_header_names = array();
        $_header_names[] = $_field_enclosure . 'TIPO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NUM PEDIDO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'METODO ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'UNIDADES' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NUM SEGUIMIENTO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NOMBRE ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'APELLIDOS ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'DIRECCION ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'COD. POSTAL ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'CIUDAD ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PROVINCIA ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'PAIS ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TELEFONO ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'GASTOS DE ENVIO' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'TIPO DE IVA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'COMENTARIOS' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . '-' . $_field_enclosure;
	$_header_names[] = $_field_enclosure . 'TIPO VIA' . $_field_enclosure;
        $_header_names[] = $_field_enclosure . 'NUMERO' . $_field_enclosure;
	$_header_names[] = $_field_enclosure . 'OTROS' . $_field_enclosure;
	
        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            $_line .= implode($_field_separator, $_header_names) . "\n";
        }
        $_data = array();
        $_data[] = $_field_enclosure . 'E' . $_field_enclosure; //TIPO
        $_data[] = $_field_enclosure . $order->getIncrementId() . $_field_enclosure; //NUM PEDIDO
        $_data[] = $_field_enclosure . $order->getShippingDescription() . $_field_enclosure; //METODO ENVIO
        $_data[] = $_field_enclosure . $order->getTotalItemCount() . $_field_enclosure; //UNIDADES
        $_data[] = $_field_enclosure . $this->_getShipmentTrackingNumber($order) . $_field_enclosure; //NUM SEGUIMIENTO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getFirstname() . $_field_enclosure; //NOMBRE ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getLastname() . $_field_enclosure; //APELLIDO ENVIO
        
	$street = $order->getShippingAddress()->getStreet();
	$_data[] = $_field_enclosure . $street[1] . $_field_enclosure;

//        $_data[] = $_field_enclosure . str_replace("\n", ", ", $order->getShippingAddress()->getStreetFull()) . $_field_enclosure; //DIRECCION ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getPostcode() . $_field_enclosure; //COD. POSTAL ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getCity() . $_field_enclosure; //CIUDAD ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getRegion() . $_field_enclosure; //PROVINCIA ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getCountryId() . $_field_enclosure; //PAIS ENVIO
        $_data[] = $_field_enclosure . $order->getShippingAddress()->getTelephone() . $_field_enclosure; //TELEFONO ENVIO


	$shipping = floatval($order->getBaseShippingInclTax());
	$surcharge = floatval($order->getBaseFoomanSurchargeAmount());
	$gastos = $shipping + $surcharge; 

        $_data[] = $_field_enclosure . $gastos . $_field_enclosure; //GASTOS DE ENVIO

Mage::log($gastos);

        $_data[] = $_field_enclosure . $this->_getCalculatedTax($order) . $_field_enclosure; //TIPO DE IVA
        $_data[] = $_field_enclosure . $this->_getShipmentComments($order) . $_field_enclosure; //COMENTARIOS
        $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
        $_data[] = $_field_enclosure . '' . $_field_enclosure; //-
	$_data[] = $_field_enclosure . $street[0] . $_field_enclosure;
	$_data[] = $_field_enclosure . $street[2] . $_field_enclosure;
	$_data[] = $_field_enclosure . $street[3] . $_field_enclosure;
        $_line .= implode($_field_separator, $_data) . "\n";
        return $_line;
    }

    private function _getPaymentMethodTitle($code) {
        if ($code) {
            return Mage::getStoreConfig('payment/' . $code . '/title');
        }
        return false;
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
    
    private function _getCalculatedTax($order) {
        $_taxes = Mage::helper('tax')->getCalculatedTaxes($order);
        foreach ($_taxes as $_tax) {
            return $_tax['percent'];
        }
        foreach ($order->getAllItems() as $_item) {
            if ($_item->getTaxPercent() && $_item->getTaxPercent() > 0) {
                return $_item->getTaxPercent();
            } 
        }
        return false;
    }
    
    private function _getShipmentComments($order) {
        $_line = array();
        $_shipments = $order->getShipmentsCollection();
        foreach ($_shipments as $_shipment) {
            $_comments = Mage::getResourceModel('sales/order_shipment_comment_collection')->setShipmentFilter($_shipment->getEntityId());
            foreach ($_comments as $_comment) {
                $_line[] = $_comment->getComment();
            }
        }
        if (count($_line) > 0) {
            return implode('. ', $_line);
        }
        return false;
    }
    
    private function _deleteOldFiles() {
        $_days = Mage::getStoreConfig($this->_process_name . '/logs/save_days_logs');
        if ($_days) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date(now()), Zend_Date::ISO_8601);
            $_date_flag = $_date->subDay($_days)->toString('yyyy-MM-dd HH:mm:ss');
            $_path_local = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS . 'uploaded';
            $_directory = dir($_path_local);
            while ($_file = $_directory->read()) {
                if (is_file($_path_local . DS . $_file)) {
                    $_stat = stat($_path_local . DS . $_file);
                    if ($_stat['ctime'] < strtotime($_date_flag)) {
                        @unlink($_path_local . DS . $_file);
                    }
                }
            }
        }
    }

    public function sendEmail() {
        $use_email = Mage::getStoreConfig($this->_process_name . '/logs/email');
        if ($use_email > 1) {
            $email_data = array();
            switch ($use_email) {
                case 1:
                    $email_data['log'] = $this->_getLogsDetails();
                break;
                case 2:
                    $email_data['log'] = $this->_getErrorMessage();
                break;
                default:
                    $email_data['log'] = '';
            }

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($email_data);
                $mailTemplate = Mage::getModel('core/email_template');
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->sendTransactional(
                        Mage::getStoreConfig($this->_process_name . '/logs/template'),
                        Mage::getStoreConfig($this->_process_name . '/logs/identity'),
                        Mage::getStoreConfig($this->_process_name . '/logs/recipient'),
                        null,
                        array('data' => $postObject)
                    );
                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }
                $translate->setTranslateInline(true);
                $this->_getLogger()->saveLog('Log email sent.', $this->_process_name, true);
                return true;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);
                $this->_getLogger()->saveLog('There was an error trying to send the log email.', $this->_process_name, true);
                return false;
            }

        }
    }

    private function _getErrorMessage() {
        return Mage::getStoreConfig($this->_process_name . '/logs/generic_message');
    }

    private function _getLogsDetails() {
        $today = now();
        $message = '';
        $collection = $this->_getLogger()->getCollection()
                                ->addFieldToFilter('log_date', array('gteq' => $today))
                                ->addFieldToFilter('process_name', array('eq' => $this->_process_name));
        foreach ($collection as $row) {
            $message .= Mage::helper('core')->formatDate($row->getLogDate(), 'short', true) . ": " . $row->getMessage() . "\n";
        }
        return $message;
    }
    
    protected function _getLogger() {
    	if(!isset($this->_logger)) {
    		$this->_logger = Mage::getModel('i4integration/logs');
    	}
    	return $this->_logger;
    }
    
    private function _avoidOutFirstLine() {
        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            return true;
        }
        return false;
    }
    
    private function _getMapperOutFileName() {
        $_file_name = Mage::getStoreConfig($this->_process_name . '/mapper_out/file_name');
        $_file_timestamp = Mage::getStoreConfig($this->_process_name . '/mapper_out/file_timestamp');
        if ($_file_timestamp) {
            $_file_name .= '_' . date($_file_timestamp);
        }
        $_file_extension = Mage::getStoreConfig($this->_process_name . '/mapper_out/file_extension');
        return $_file_name . '.' . $_file_extension; 
    }
    
}
