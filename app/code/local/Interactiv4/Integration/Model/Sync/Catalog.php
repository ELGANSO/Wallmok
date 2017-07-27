<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Sync_Catalog extends Mage_Core_Model_Abstract
{

    protected $_process_name = 'i4sync_catalog';
    private $_remote_data;
    private $_logger;
    private $_mapped_in_columns;
    
    private $_websites;
    private $_stores;
    private $_category_tree;
    
    public function execute() {
        $_sync_enable = Mage::getStoreConfig($this->_process_name . '/sync/enable');
        if ($_sync_enable) {
            $this->_getLogger()->cleanOld($this->_process_name);
            $this->_deleteOldFiles();
            $_sync_type = Mage::getStoreConfig($this->_process_name . '/sync/type');
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::IN || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                $this->_getRemoteData();
                if (!$this->_parseData()) {
                    return false;
                }
                $_products = $this->_processProducts();
                $this->_getLogger()->saveLog($_products . ' processed.', $this->_process_name);
            }
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::OUT || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                //
            }
        } else {
            $this->_getLogger()->saveLog('Sync Catalog is fully disabled.', $this->_process_name);
        }
        $this->sendEmail();
        return true;
    }
    
    private function _getRemoteData() {
        $_use_ftp = Mage::getStoreConfig($this->_process_name . '/ftp/enable');
        if ($_use_ftp == 1) {
            $_ftp = Mage::helper('i4integration/ftp');
            $_ftp->openConnection($this->_process_name);
            $_ftp->doLogin();
            $_file_io = new Varien_Io_File();
            $_file_io->setAllowCreateFolders(true);
            $_path_local = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/download/local');
            $_path_remote = Mage::getStoreConfig($this->_process_name . '/download/remote');
            $_ftp->checkFolderExists($_path_local);
            $_ftp->checkFolderExists($_path_local . DS . 'downloaded');
            $_files = $_ftp->listRemoteFiles($_path_remote);
            if (Mage::getStoreConfig($this->_process_name . '/download/filter_files')) {
                $_find_file = Mage::getStoreConfig($this->_process_name . '/download/filter_pattern');
                $_files = $_ftp->findFile($_find_file, $_files);
            }
            foreach($_files as $_file) {
                if ($_ftp->downloadFile($_path_local, $_path_remote, $_file)) {
                    $_ftp->renameFile($_path_remote . DS . $_file, $_path_remote . DS . 'procesados' . DS . $_file, 1, '/');
                }
            }
            $_ftp->closeConnection();
            unset($_ftp);
            unset($_file_io);
            if (count($_files) > 0) {
                $this->_getLogger()->saveLog('Remote data downloaded.', $this->_process_name, true);
            } else {
                $this->_getLogger()->saveLog('No data found.', $this->_process_name, true);
            }
        }
        return true;
    }
    
    private function _getWebsites() {
        $_codes = array();
        $_websites = Mage::app()->getWebsites(true);
        foreach ($_websites as $_website) {
            $_codes[$_website->getCode()] = $_website->getId();
        }
        $this->_websites = $_codes;
        return $_codes;
    }
    
    private function _getStores() {
        $_codes = array();
        $_stores = Mage::app()->getStores(true);
        foreach ($_stores as $_store) {
            $_codes[$_store->getWebsiteId()][$_store->getCode()] = $_store->getId();
        }
        $this->_stores = $_codes;
        return $_codes;
    }
    
    private function _parseData() {
        $_websites_ids = $this->_getWebsites();
        $_stores_ids = $this->_getStores();
        $_data = array();
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_enclosure');
        $_path_local = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/download/local');
        $_directory = dir($_path_local);
        $_file_io = new Varien_Io_File();
        $_file_io->setAllowCreateFolders(true);
        while ($_file = $_directory->read()) {
            if ($_file != "." && $_file != ".." && $_file != ".svn" && $_file != ".git") {
                $_file_content = file_get_contents($_path_local . DS . $_file);
                if (!$_file_content) {
                    continue;
                }
                $_lines = explode("\n", $_file_content);
                $_first_line = true;
                foreach ($_lines as $_line) {
                    if ($_first_line) {
                        $_first_line = false;
                        if ($this->_avoidInFirstLine()) {
                            continue;
                        }
                    }
                    if ($_field_enclosure) {
                        $_line = str_replace($_field_enclosure, '', trim($_line));
                    }
                    $_values = explode($_field_separator, $_line);
                    
                    $_website_id = (int)$_websites_ids[$_values[2]];
                    $_store_id = (int)$_stores_ids[$_website_id][$_values[3]];
                    if (count($_values) == 38 && is_int($_store_id)) {
                        $_data[] = array(
                                             'website_id' => $_website_id,
                                             'store_id' => $_store_id,
                                             'sku' => $_values[0],
                                             'sku_padre' => $_values[1],
                                             'borrar' => $_values[4],
                                             'categorias' => mb_convert_encoding($_values[5], "UTF-8"),
                                             'nombre' => mb_convert_encoding($_values[6], "UTF-8"),
                                             'descripcion' => mb_convert_encoding($_values[7], "UTF-8"),
                                             'descripcion_corta' => mb_convert_encoding($_values[8], "UTF-8"),
                                             'guia_de_tallas' => $_values[9],
                                             'peso' => $_values[10],
                                             'new_from' => $_values[11],
                                             'new_to' => $_values[12],
                                             'baja' => $_values[13],
                                             'talla' => $_values[14],
                                             'composicion' => $_values[15],
                                             'cuidado' => $_values[16],
                                             'temporada' => $_values[17],
                                             'precio' => $_values[20],
                                             'precio_especial' => $_values[21],
                                             'especial_desde' => $_values[22],
                                             'especial_hasta' => $_values[23],
                                             'qty' => $_values[25],
                                             'disponible' => $_values[26],
                                             'imagen1' => $_values[28],
                                             'imagen2' => $_values[29],
                                             'imagen3' => $_values[30],
                                             'imagen4' => $_values[31],
                                             'visibilidad' => $_values[34],
                                             'estado' => $_values[35],
                                             'composicion_textil' => mb_convert_encoding($_values[36], "UTF-8"),
                                             'lavado' => mb_convert_encoding($_values[37], "UTF-8")

                                         );
                    }
                } 
                $_file_io->checkAndCreateFolder($_path_local . DS . 'downloaded');
                $_file_io->mv($_path_local . DS . $_file, $_path_local . DS . 'downloaded' . DS . $_file);
            }
        }
        $this->_getLogger()->saveLog('Remote data was parsed.', $this->_process_name);
        $this->_remote_data = $_data;
        $_directory->close();
        if (!$this->_remote_data) {
            $this->_getLogger()->saveLog('No new data found.', $this->_process_name);
            return false;
        }
        return true; 
    }
    
    /**
     * CAMBIAR
     */
    private function _processProducts() {
        $_products = $this->_remote_data;
        $_total_records = 0;
        foreach ($_products as $_product) {
            if ($_product['borrar'] == 1) {
                $this->_deleteProduct($_product['sku']);
            } else {
                $_data = Mage::getModel('catalog/product')->loadByAttribute('sku', (string)$_product['sku']);
                if ($_data) {
                    $this->_updateProduct($_data->getId(), $_product);
                } else {
                    $this->_createProduct($_product);
                }
                unset($_data);
            }            
            $_total_records++;
        }
        if ($_total_records > 0) {
            //Mage::getSingleton('catalogindex/indexer')->plainReindex();
        }
        return $_total_records;
    }
    
    private function _deleteProduct($sku) {
        $_data = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if ($_data) {
            $_data->delete();
            $this->_getLogger()->saveLog('SKU ' . $sku . ' was deleted.', $this->_process_name);
        } else {
            $this->_getLogger()->saveLog('SKU ' . $sku . ' was not found into the catalog.', $this->_process_name);
        }
        unset($_data);
    }
    
    private function _createProduct($product) {
        $_product_tax_class_id = Mage::getStoreConfig($this->_process_name . '/mapper_in/product_tax_class');
        $_model = Mage::getModel('catalog/product');
        if ($product['sku_padre']) {
            $_model->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        } else {
            $_model->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
            $_configurable_attributes = Mage::getStoreConfig($this->_process_name . '/mapper_in/configurbale_attributes');
            if ($_configurable_attributes) {
                $_configurable_attributes = explode(',',$_configurable_attributes);
                $_use_attribute_ids = array();
                foreach($_configurable_attributes as $_configurable_attribute){
                    $_attribute = $_model->getResource()->getAttribute($_configurable_attribute);
                    $_use_attribute_ids[] = $_attribute->getAttributeId();
                }
                $_model->getTypeInstance()->setUsedProductAttributeIds($_use_attribute_ids);
                $_configurable_new_attributes = array();
                $_configurable_array_count = 0;
                $_configurable_current_attributes = $_model->getTypeInstance()->getConfigurableAttributesAsArray();
                $_configurable_new_attributes = $_model->getTypeInstance()->getConfigurableAttributesAsArray();
                foreach($_configurable_new_attributes as $_configurable_new_attribute) {
                    $_configurable_current_attributes[$_configurable_array_count]['label'] = $_configurable_new_attribute['frontend_label'];
                    $_configurable_array_count++;
                }
                $_model->setConfigurableAttributesData($_configurable_current_attributes);
            }
            $_model->setCanSaveConfigurableAttributes(true);
        }
        $_model->setAttributeSetId(4);
        $_model->setData('_edit_mode', true);
        $_model->setCategoryIds($this->_getCategoriesIds($product['categorias']));
        $_model->unlockAttribute('media');
        if ($product['store_id'] == Mage::getStoreConfig($this->_process_name . '/mapper_in/default_website')) {
            $_model->setData('website_ids',array(0, $product['website_id']));
        } else {
            $_model->setData('website_ids',array($product['website_id']));
        }
        $_model->setStockData($this->_getStockData($product));
        $_model->lockAttribute('media');
        $_model->setData('sku', $product['sku']);
        $_model->setData('name', $product['nombre']);
        $_model->setData('description', $product['descripcion']);
        $_model->setData('short_description', $product['descripcion_corta']);
        $_model->setData('weight', $product['peso']);
        $_model->setData('price', $product['precio']);
        $_model->setData('tax_class_id', $_product_tax_class_id);
        $_model->setData('composition', $product['composicion']);
        $_model->setData('product_care', $product['cuidado']);
        $_model->setData('composicion_textil', $product['composicion_textil']);
        $_model->setData('lavado', $product['lavado']);
        if ($product['precio_especial']) {
            $_model->setSpecialPrice($product['precio_especial']);
            if ($product['especial_desde']) {
                $_date = new Zend_Date(Mage::app()->getLocale()->date($product['especial_desde']), Zend_Date::ISO_8601);
                $_model->setSpecialFromDate($_date->toString('dd-MM-yyyy'));
            }
            if ($product['especial_hasta']) {
                $_date = new Zend_Date(Mage::app()->getLocale()->date($product['especial_hasta']), Zend_Date::ISO_8601);
                $_model->setSpecialToDate($_date->toString('dd-MM-yyyy'));
            }
        }
        if ($product['new_from']) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date($product['new_from']), Zend_Date::ISO_8601);
            $_model->setNewsFromDate($_date->toString('dd-MM-yyyy'));
        }
        if ($product['new_to']) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date($product['new_to']), Zend_Date::ISO_8601);
            $_model->setNewsToDate($_date->toString('dd-MM-yyyy'));
        }
        $_model->setData('guia_tallas', $this->_getAttributeValue('guia_tallas', $product['guia_de_tallas']));
        $_model->setData('size', $this->_getAttributeValue('size', $product['talla']));
        $_model->setData('season', $this->_getAttributeValue('season', $product['temporada']));
        if ($product['estado']) {
            $_model->setData('status', $product['estado']);
        }
        if ($product['visibilidad']) {
            $_model->setData('visibility', $product['visibilidad']);
        }
        $_model->setData('meta_title', $product['nombre']);
        $_model->setData('meta_keyword', $product['nombre']);
        $_model->setData('meta_description', $product['descripcion']);
        $_errors = $_model->validate();
        if (is_array($_errors)){
            $this->_getLogger()->saveLog('ERROR. Product with SKU ' . $product['sku'] . ' can not be created.', $this->_process_name, true);
            return false;
        }
        try {
            $_model->setStoreId($product['store_id'])->save();
            $_product_id = $_model->getEntityId();
            unset($_model);
        } catch (Mage_Core_Exception $e) {
            $this->_getLogger()->saveLog('There was an error on catalog sync. ERROR: ' . $e->getMessage(), $this->_process_name, true);
            return false;
        }
        
        if (Mage::getStoreConfig($this->_process_name . '/mapper_in/images')) {
            $_model = Mage::getModel('catalog/product')->setStoreId($product['store_id'])->load($_product_id);
            $_model->setMediaGallery(array('images' => array(), 'values' => array()));
            $_images_path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/mapper_in/images');
            if ($product['imagen1']) {
                $_image = $_images_path . DS . $product['imagen1'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen2']) {
                $_image = $_images_path . DS . $product['imagen2'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen3']) {
                $_image = $_images_path . DS . $product['imagen3'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen4']) {
                $_image = $_images_path . DS . $product['imagen4'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            unset($_model);
        }
        if ($product['sku_padre']) {
            $_configurable = Mage::getModel('catalog/product')->loadByAttribute('sku', (string)$product['sku_padre']);
            if ($_configurable && $_configurable->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $_configurbale_new_simple_ids = array();
                $_configurbale_current_simple_ids = $_configurable->getTypeInstance()->getUsedProductIds();
                $_configurbale_current_simple_ids[] = $_product_id;
                $_configurbale_current_simple_ids = array_unique($_configurbale_current_simple_ids);
                foreach($_configurbale_current_simple_ids as $_configurbale_current_simple_id){
                    parse_str("position=", $_configurbale_new_simple_ids[$_configurbale_current_simple_id]);
                }
                $_configurable->setHasOptions(true);
                $_configurable->setRequiredOptions(true);
                $_configurable->setConfigurableProductsData($_configurbale_new_simple_ids);
                $_configurable->save();
                $this->_getLogger()->saveLog('SKU ' . $product['sku'] . ' was associated with SKU ' . $product['sku_padre'] . '.', $this->_process_name);
            }
        }
        $this->_getLogger()->saveLog('Product with SKU ' . $product['sku'] . ' was created.', $this->_process_name, true);
    }
    
    private function _updateProduct($id, $product) {
        $_model = Mage::getModel('catalog/product')->load($id);
        $_product_tax_class_id = Mage::getStoreConfig($this->_process_name . '/mapper_in/product_tax_class');
        if ($product['sku_padre']) {
            //
        } else {
            $_model->setCanSaveConfigurableAttributes(true);
        }
        $_model->setData('_edit_mode', true);
        $_model->setCategoryIds($this->_getCategoriesIds($product['categorias']));
        $_model->unlockAttribute('media');
        $_website_new = array($product['website_id']);
        $_website_ids = array_merge($_website_new, $_model->getWebsiteIds());
        $_model->setData('website_ids',array_unique($_website_ids));
        $_model->setStockData($this->_getStockData($product));
        $_model->lockAttribute('media');
        if ($product['sku']) {
            $_model->setData('sku', $product['sku']);
        }
        if ($product['nombre']) {
            $_model->setData('name', $product['nombre']);
        }
        if ($product['descripcion']) {
            $_model->setData('description', $product['descripcion']);
        }
        if ($product['descripcion_corta']) {
            $_model->setData('short_description', $product['descripcion_corta']);
        }
        if ($product['peso']) {
            $_model->setData('weight', $product['peso']);
        }
        if ($product['precio']) {
            $_model->setData('price', $product['precio']);
        }
        $_model->setData('tax_class_id', $_product_tax_class_id);
        if ($product['composicion']) {
            $_model->setData('composition', $product['composicion']);
        }
        if ($product['cuidado']) {
            $_model->setData('product_care', $product['cuidado']);
        }
        if ($product['precio_especial']) {
            $_model->setSpecialPrice($product['precio_especial']);
            if ($product['especial_desde']) {
                $_date = new Zend_Date(Mage::app()->getLocale()->date($product['especial_desde']), Zend_Date::ISO_8601);
                $_model->setSpecialFromDate($_date->toString('dd-MM-yyyy'));
            }
            if ($product['especial_hasta']) {
                $_date = new Zend_Date(Mage::app()->getLocale()->date($product['especial_hasta']), Zend_Date::ISO_8601);
                $_model->setSpecialToDate($_date->toString('dd-MM-yyyy'));
            }
        }
        if ($product['new_from']) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date($product['new_from']), Zend_Date::ISO_8601);
            $_model->setNewsFromDate($_date->toString('dd-MM-yyyy'));
        }
        if ($product['new_to']) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date($product['new_to']), Zend_Date::ISO_8601);
            $_model->setNewsToDate($_date->toString('dd-MM-yyyy'));
        }
        if ($product['guia_de_tallas']) {
            $_model->setData('guia_tallas', $this->_getAttributeValue('guia_tallas', $product['guia_de_tallas']));
        }
        if ($product['talla']) {
            $_model->setData('size', $this->_getAttributeValue('size', $product['talla']));
        }
        if ($product['temporada']) {
            $_model->setData('season', $this->_getAttributeValue('season', $product['temporada']));
        }
        if ($product['estado']) {
            $_model->setData('status', $product['estado']);
        }
        if ($product['visibilidad']) {
            $_model->setData('visibility', $product['visibilidad']);
        }
        if ($product['nombre']) {
            $_model->setData('meta_title', $product['nombre']);
        }
        if ($product['nombre']) {
            $_model->setData('meta_keyword', $product['nombre']);
        }
        if ($product['descripcion']) {
            $_model->setData('meta_description', $product['descripcion']);
        }
        if ($product['composicion_textil']) {
            $_model->setData('composicion_textil', $product['composicion_textil']);
        }
        if ($product['lavado']) {
            $_model->setData('lavado', $product['lavado']);
        }
        $_errors = $_model->validate();
        if (is_array($_errors)){
            $this->_getLogger()->saveLog('ERROR. Product with SKU ' . $product['sku'] . ' can not be updated.', $this->_process_name, true);
            return false;
        }
        try {
            $_model->setStoreId($product['store_id'])->save();
            $_product_id = $_model->getEntityId();
            unset($_model);
        } catch (Mage_Core_Exception $e) {
            $this->_getLogger()->saveLog('There was an error on catalog sync. ERROR: ' . $e->getMessage(), $this->_process_name, true);
            return false;
        }
        
        if (Mage::getStoreConfig($this->_process_name . '/mapper_in/images')) {
            $_model = Mage::getModel('catalog/product')->setStoreId($product['store_id'])->load($_product_id);
            $_model->setMediaGallery(array('images' => array(), 'values' => array()));
            $_images_path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/mapper_in/images');
            if ($product['imagen1']) {
                $_image = $_images_path . DS . $product['imagen1'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen2']) {
                $_image = $_images_path . DS . $product['imagen2'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen3']) {
                $_image = $_images_path . DS . $product['imagen3'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            if ($product['imagen4']) {
                $_image = $_images_path . DS . $product['imagen4'];
                if (file_exists($_image)) {
                    $_model->addImageToMediaGallery($_image, array('image', 'small_image', 'thumbnail'), false, false);
                    $_model->save();
                } else {
                    $this->_getLogger()->saveLog('SKU: ' . $_model->getSku() . '. Image ' . $_image . ' can not be found.', $this->_process_name, true);
                }
            }
            unset($_model);
        }
        $this->_getLogger()->saveLog('Product with SKU ' . $product['sku'] . ' was updated.', $this->_process_name, true);
    }
    
    private function _deleteOldFiles() {
        $_days = Mage::getStoreConfig($this->_process_name . '/logs/save_days_logs');
        if ($_days) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date(now()), Zend_Date::ISO_8601);
            $_date_flag = $_date->subDay($_days)->toString('yyyy-MM-dd HH:mm:ss');
            $_path_local = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/download/local') . DS . 'downloaded';
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
    
    private function _avoidInFirstLine() {
        if (Mage::getStoreConfig($this->_process_name . '/mapper_in/names')) {
            return true;
        }
        return false;
    }
    
    private function _getStockData($product) {
        $_stock_data = array();
        $_stock_data['qty'] = $product['qty'];
        $_stock_data['is_in_stock'] = $product['disponible'];
        $_stock_data['manage_stock'] = 1;
        $_stock_data['use_config_manage_stock'] = 1;
        return $_stock_data;
    }
    
    private function _getAttributeValue($code, $value) {
        $_options = $this->_getAttributeOptions($code);
        $_value = '';
        foreach($_options as $_option){
            if ($value == $_option->getValue()) {
                $_value = $_option->getOptionId();
            }
        }
        return $_value;
    }
    
    private function _getAttributeOptions($attribute_code, $limit = null) {
        $_attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attribute_code);
        $collection = null;
        if ($_attribute->getId()) {
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($_attribute->getId())
                ->setPositionOrder('asc', true);
                if ($limit) {
                    $collection->setPageSize($limit);
                }
                $collection->load();
        }
        return $collection;
    }
    
    
    private function _generateCategoryTree() {
        $_category_model = Mage::getModel('catalog/category');
        $_tree = $_category_model->getTreeModel()->load();
        $_ids = $_tree->getCollection()->getAllIds();
        $_categories = array();
        if ($_ids){
            foreach ($_ids as $_id){
                $_category = Mage::getModel('catalog/category')->load($_id);
                if ($_category->getIsActive()) {
                    $_fixed_values = array(
                                            'category_id' => $_category->getId(),
                                            'parent_id' => $_category->getParentId(),
                                            'name' => $_category->getName(),
                                            'level' => $_category->getLevel(),
                                            'is_active' => $_category->getIsActive(), 
                                            );
                    $_categories[$_category->getId()] = $_fixed_values;
                }
            }
        }
        $this->_category_tree = $_categories;
    }
    
    private function _searchCategory($string, $level, $parent) {
        $_final_parent = null;
        foreach ($this->_category_tree as $_category) {
            if ($parent) {
                if ($_category['name'] == $string && $_category['level'] == $level && $_category['parent_id'] == $parent) {
                    return $_category['category_id'];
                }
            } else {
                if ($_category['name'] == $string && $_category['level'] == $level) {
                    return $_category['category_id'];
                }
            }
        }
        return false;
    }
    
    private function _getCategoriesIds($string) {
        $this->_generateCategoryTree();
        $_search = explode(',', $string);
        $parent = null;
        $level = 2;
        $_ids = array();
        foreach ($_search as $_word) {
            $parent = $this->_searchCategory($_word, $level, $parent);
            $_ids[] = $parent;
            $level++; 
        }
        return $_ids;
    }


    /*---------------------------------------------------*/
    
    
    public function reindexAll() {
        echo "Reindexing data...\n";
        self::log("Reindexing data...");
        
        $processes = array();
        
        $collection = Mage::getSingleton('index/indexer')->getProcessesCollection();
        foreach ($collection as $process)
        {
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            try
            {
                echo "\t" . $process->getIndexer()->getName() . "... ";
                self::log($process->getIndexer()->getName() . "... ");
                $process->reindexEverything();
                echo "index was rebuilt successfully\n";
                self::log("index was rebuilt successfully");
            }
            catch (Mage_Core_Exception $e)
            {
                echo $e->getMessage() . "\n";
                self::log($e->getMessage());
            }
            catch (Exception $e)
            {
                echo "index process unknown error:\n";
                echo "\t" . $e . "\n";
                self::log("index process unknown error:" . $e->getMessage());
            }
        }
    
        return $processes;
    }
    
    /**
     * Clean cache
     */
    public function cleanCache()
    {
        echo "Cleaning cache...\n";
        self::log("Cleaning cache...");
        
        try
        {
            //oreales: como tenemos memcache y database como caches, nos
            //aseguramos que se borra el fichero de caches si los hubiera
            $salida = shell_exec('rm -rf '. Mage::getBaseDir() . DS .'var/cache/*');
            echo "deleting " . Mage::getBaseDir() . DS .'var/cache/*' . " " .$salida . "\n";
            $salida = shell_exec('rm -rf '. Mage::getBaseDir() . DS .'var/full_page_cache/*');
            echo "deleting " . Mage::getBaseDir() . DS .'var/full_page_cache/*' . " " .$salida . "\n";
            
            Mage::app()->getCacheInstance()->flush();
            Mage::app()->cleanCache();
            
            //purgando la cache de varnish también
            echo "purgando Varnish cache\n";
            Mage::getModel('turpentine/varnish_admin')->flushAll();
        }
        catch (Exception $e)
        {
            echo $e;
            self::log($e->getMessage());
        }
    }
    
    /**
     * Recalculate catalog price rules
     */
    public function applyRules()
    {
        echo "Recalculating catalog price rules...\n";
        self::log("Recalculating catalog price rules...");
        
        try
        {
            Mage::getModel('catalogrule/rule')->applyAll();
            Mage::app()->removeCache('catalog_rules_dirty');
        }
        catch (Exception $e)
        {
            echo $e;
            self::log($e->getMessage());
        }
    }
    
    /**
     * Flush catalog images cache
     */
    public function flushImages()
    {
        echo "Erasing catalog images cache...\n";
        self::log("Erasing catalog images cache...");
        
        try
        {
            Mage::getModel('catalog/product_image')->clearCache();
        }
        catch (Exception $e)
        {
            echo $e;
            self::log($e->getMessage());
        }
    }
    
}
