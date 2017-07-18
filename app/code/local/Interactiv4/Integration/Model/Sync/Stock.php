<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Sync_Stock extends Mage_Core_Model_Abstract
{

    const INDEX_PROCESS_STOCK_CODE = 'cataloginventory_stock';
    
    protected $_process_name = 'i4sync_stock';
    private $_remote_data;
    private $_logger;
    private $_mapped_in_columns;
    
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
                $_products = $this->_getProducts();
                foreach ($_products as $_product) {
                    $_quantity = $this->_remote_data[$_product->getSku()];
                    $_product_id = $_product->getEntityId();
                    $_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product_id);
                    $_stock->setData('qty', $_quantity);
                    $_stock->setData('is_in_stock', $_quantity > 0 ? 1 : 0);
                    $_stock->save();
                    if ($_quantity > 0) {
                        $this->_getLogger()->saveLog('Product SKU ' . $_product->getSku() . ' has new stock: ' . $_quantity, $this->_process_name);
                    } else if ($_quantity != null && $_quantity <= 0) {
                        $this->_getLogger()->saveLog('Product SKU ' . $_product->getSku() . ' has changed to OUT OF STOCK.', $this->_process_name);
                    }
                    // Reindexamos los stocks y atributos del producto actualizado y de los relacionados
                    Mage::getResourceModel('cataloginventory/indexer_stock')->reindexProducts([$_product_id]);
		    //Mage::getResourceModel('catalog/indexer_attribute')->reindexProducts([$_product_id]);
                }
                $this->_validateCatalog();
                $this->_getLogger()->saveLog('Import process has finished.', $this->_process_name);

		try {
			$indexer = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_attribute');
			$indexer->reindexEverything();
		    } catch (Exception $e) {
			//Some error handling
		    }
            }
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::OUT || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                if (!$this->_createData()) {
                    return false;
                }
                $_files = $this->_sendFiles();
                $this->_getLogger()->saveLog('Export process has finished.', $this->_process_name);
            }
        } else {
            $this->_getLogger()->saveLog('Sync Stock is fully disabled.', $this->_process_name);
        }
        $this->sendEmail();
        
	

        /* Reindex stock */
        //$indexer = Mage::getSingleton('index/indexer');
        /* @var $indexer Mage_Index_Model_Indexer */
        
       // $process = $indexer->getProcessByCode(self::INDEX_PROCESS_STOCK_CODE);  /* @var $process Mage_Index_Model_Process */
       // $process->reindexEverything();
        
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
    
    private function _parseData() {
        $_data = array();
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_enclosure');
        $_path_local = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/download/local');
        $_directory = dir($_path_local);
        $_file_io = new Varien_Io_File();
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
                        $this->_matchInColumns($_line);
                        $_first_line = false;
                        if ($this->_avoidInFirstLine()) {
                            continue;
                        }
                    }
                    if ($_field_enclosure) {
                        $_line = str_replace($_field_enclosure, '', trim($_line));
                    }
                    $_values = explode($_field_separator, $_line);
                    if (count($_values) == 2) {
                        $_mapped_columns = $this->_mapped_in_columns;
                        if (count($_mapped_columns) == 2) {
                            $_product_sku = $_values[$_mapped_columns['sku']];
                            $_product_stock = $_values[$_mapped_columns['stock']];
                            if ($_product_sku && is_numeric($_product_stock)) {
                                $_data[$_product_sku] = $_product_stock;
                            }
                        }
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
    
    private function _getProducts() {
        $_ids = $this->_remote_data;
        $_search = array();
        foreach ($_ids as $_id => $_stock) {
            $_search[] = $_id;
        }
        $_products = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToFilter('sku', array('in' => $_search));
        $this->_getLogger()->saveLog($_products->getSize() . ' products found into the catalog.', $this->_process_name, true);
        return $_products;
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
        $path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS;
        $file = new Varien_Io_File();
        $file->setAllowCreateFolders(true);
        $file->open(array (
            'path' => $path
        ));
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
        $file->streamOpen($this->_getMapperOutFileName());
        $_serialized_values = Mage::getStoreConfig($this->_process_name . '/mapper_out/fields');
        $_unserialized_values = Mage::helper('i4integration')->getMappedFields($_serialized_values);
        $_columns = array();
        $_attributes_to_add = array();
        $_header_names = array();
        $_columns[Mage::getStoreConfig($this->_process_name . '/mapper_out/stock_position')] = array('local' => 'i4sync_stock_qty','remote' => Mage::getStoreConfig($this->_process_name . '/mapper_out/stock_remote'));
        $_header_names[Mage::getStoreConfig($this->_process_name . '/mapper_out/stock_position')] = $_field_enclosure . Mage::getStoreConfig($this->_process_name . '/mapper_out/stock_remote') . $_field_enclosure;
        foreach ($_unserialized_values as $_unserialized_value) {
            $_columns[$_unserialized_value['position']] = array('local' => $_unserialized_value['local'],'remote' => $_unserialized_value['remote']);
            $_attributes_to_add[] = $_unserialized_value['local'];
            $_header_names[$_unserialized_value['position']] = $_field_enclosure . $_unserialized_value['remote'] . $_field_enclosure;
        }
        ksort($_columns);
        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            ksort($_header_names);
            $file->streamWrite("" . implode($_field_separator, $_header_names) . "\n");
        }
        $_products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect($_attributes_to_add);
        foreach ($_products as $_product) {
            $_line = array();
            foreach ($_columns as $_column) {
                if ($_column['local'] == 'i4sync_stock_qty') {
                    $_line[] = $_field_enclosure . Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty() . $_field_enclosure;
                } else {
                    $_line[] = $_field_enclosure . $_product->getData($_column['local']) . $_field_enclosure;
                }
            }
            $file->streamWrite("" . implode($_field_separator, $_line) . "\n");
        }
        $file->streamClose();
        $this->_getLogger()->saveLog($_products->getSize() . ' file(s) exported.', $this->_process_name);
        return true;
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
    
    private function _avoidOutFirstLine() {
        if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
            return true;
        }
        return false;
    }
    
    private function _matchInColumns($line) {
        $_values = array();
        $_match_by = Mage::getStoreConfig($this->_process_name . '/mapper_in/columns');
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_in/field_enclosure');
        switch ($_match_by) {
            case Interactiv4_Integration_Model_System_Config_Source_Mapper_Columns::NAME:
                if ($_field_enclosure) {
                    $line = str_replace($_field_enclosure, '', $line);
                }
                $_columns = explode($_field_separator, $line);
                $_map_stock = array_search(Mage::getStoreConfig($this->_process_name . '/mapper_in/stock_remote'), $_columns);
                if (is_numeric($_map_stock)) {
                    $_values['stock'] = $_map_stock;
                }
                $_map_sku = array_search(Mage::getStoreConfig($this->_process_name . '/mapper_in/sku_remote'), $_columns);
                if (is_numeric($_map_sku)) {
                    $_values['sku'] = $_map_sku;
                }
            break;
            case Interactiv4_Integration_Model_System_Config_Source_Mapper_Columns::POSITION:
                $_values['stock'] = (Mage::getStoreConfig($this->_process_name . '/mapper_in/stock_position') - 1);
                $_values['sku'] = (Mage::getStoreConfig($this->_process_name . '/mapper_in/sku_position') - 1);
            break;
        }
        $this->_mapped_in_columns = $_values;
        return true;
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
    
    private function _validateCatalog() {
        $this->_getLogger()->saveLog('Starting catalog validation.', $this->_process_name);
        $_products = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        foreach ($_products as $_product) {
            $_stock = $this->_checkConfigurable($_product->getId());
//Mage::log("Articulo ".$_product->getId()."   en stock ".$_stock);
            if (!$_stock) {
                $this->_unsetIsInStock($_product->getId());
            } else {
                $this->_setIsInStock($_product->getId());
            }
        }
        if ($_products->getSize() > 0) {
            $this->_getLogger()->saveLog($_products->getSize() . ' Configurable products were updated.', $this->_process_name);
        }
        $this->_getLogger()->saveLog('Catalog validation has finished.', $this->_process_name);
    }
    
    private function _checkConfigurable($configurable_id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $select = $connection->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('catalog/product_relation'), 'child_id')
            ->where('parent_id = ?', $configurable_id);

        $rows = $connection->fetchAll($select);
        $_child_ids = array();
        foreach ($rows as $row) {
            $_child_ids[] = $row['child_id'];
        }

        $select = $connection->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item'))
            ->where('product_id IN (?) AND is_in_stock <> 0', $_child_ids);

        if ($connection->fetchOne($select) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function _unsetIsInStock($product_id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $table = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item');
        $connection->query("UPDATE {$table} SET is_in_stock = 0 WHERE product_id = {$product_id};");
    }
    
    private function _setIsInStock($product_id) {
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $table = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item');
        $connection->query("UPDATE {$table} SET is_in_stock = 1 WHERE product_id = {$product_id};");
    }
    

}
