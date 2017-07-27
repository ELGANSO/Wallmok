<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Sync_Customer extends Mage_Core_Model_Abstract
{

    protected $_process_name = 'i4sync_customer';
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
                //
            }
            if ($_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::OUT || $_sync_type == Interactiv4_Integration_Model_System_Config_Source_Sync_Type::BOTH) {
                if (!$this->_createData()) {
                    return false;
                }
                $_files = $this->_sendFiles();
                $this->_getLogger()->saveLog('Export process has finished.', $this->_process_name);
            }
        } else {
            $this->_getLogger()->saveLog('Sync Customer is fully disabled.', $this->_process_name);
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
        $path = Mage::getBaseDir() . Mage::getStoreConfig($this->_process_name . '/upload/local') . DS;
        $file = new Varien_Io_File();
        $file->setAllowCreateFolders(true);
        $file->open(array (
            'path' => $path
        ));
        $_field_separator = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_separator');
        $_field_enclosure = Mage::getStoreConfig($this->_process_name . '/mapper_out/field_enclosure');
        $_serialized_values = Mage::getStoreConfig($this->_process_name . '/mapper_out/customer');
        $_unserialized_values = Mage::helper('i4integration')->getMappedFields($_serialized_values);
        $_columns = array();
        $_attributes_to_add = array();
        $_header_names = array();
        foreach ($_unserialized_values as $_unserialized_value) {
            $_columns[$_unserialized_value['position']] = array('local' => $_unserialized_value['local'],'remote' => $_unserialized_value['remote']);
            $_attributes_to_add[] = $_unserialized_value['local'];
            $_header_names[$_unserialized_value['position']] = $_field_enclosure . $_unserialized_value['remote'] . $_field_enclosure;
        }
        $_attributes_billing_to_add = array();
        $_serialized_values = Mage::getStoreConfig($this->_process_name . '/mapper_out/billing');
        $_unserialized_values = Mage::helper('i4integration')->getMappedFields($_serialized_values);
        foreach ($_unserialized_values as $_unserialized_value) {
            $_columns[$_unserialized_value['position']] = array('local' => 'billing_' . $_unserialized_value['local'],'remote' => $_unserialized_value['remote']);
            $_attributes_billing_to_add[] = array('alias' => 'billing_' . $_unserialized_value['local'], 'attribute' => $_unserialized_value['local']);
            $_header_names[$_unserialized_value['position']] = $_field_enclosure . $_unserialized_value['remote'] . $_field_enclosure;
        }
        $_attributes_shipping_to_add = array();
        $_serialized_values = Mage::getStoreConfig($this->_process_name . '/mapper_out/shipping');
        $_unserialized_values = Mage::helper('i4integration')->getMappedFields($_serialized_values);
        foreach ($_unserialized_values as $_unserialized_value) {
            $_columns[$_unserialized_value['position']] = array('local' => 'shipping_' . $_unserialized_value['local'],'remote' => $_unserialized_value['remote']);
            $_attributes_shipping_to_add[] = array('alias' => 'shipping_' . $_unserialized_value['local'], 'attribute' => $_unserialized_value['local']);
            $_header_names[$_unserialized_value['position']] = $_field_enclosure . $_unserialized_value['remote'] . $_field_enclosure;
        }
        $_attributes_newsletter_to_add = array();
        $_serialized_values = Mage::getStoreConfig($this->_process_name . '/mapper_out/newsletter');
        $_unserialized_values = Mage::helper('i4integration')->getMappedFields($_serialized_values);
        foreach ($_unserialized_values as $_unserialized_value) {
            $_columns[$_unserialized_value['position']] = array('local' => $_unserialized_value['local'],'remote' => $_unserialized_value['remote']);
            $_attributes_newsletter_to_add[] = array('alias' => $_unserialized_value['local'], 'attribute' => $_unserialized_value['local']);
            $_header_names[$_unserialized_value['position']] = $_field_enclosure . $_unserialized_value['remote'] . $_field_enclosure;
        }
        ksort($_columns);
        $_filter = $this->_getDateFilter();
        if ($_filter) {
            $_customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect($_attributes_to_add)->addFieldToFilter('updated_at', array('gteq' => $_filter));
        } else {
            $_customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect($_attributes_to_add);
        }
        $this->_updateFlag();
        if ($_customers->getSize() > 0) {
            $file->streamOpen($this->_getMapperOutFileName());
            if (Mage::getStoreConfig($this->_process_name . '/mapper_out/names')) {
                ksort($_header_names);
                $file->streamWrite("" . implode($_field_separator, $_header_names) . "\n");
            }
            foreach ($_attributes_billing_to_add as $_attributes_billing) {
                $_customers->joinAttribute($_attributes_billing['alias'], 'customer_address/' . $_attributes_billing['attribute'], 'default_billing', null, 'left');
            }
            foreach ($_attributes_shipping_to_add as $_attributes_shipping) {
                $_customers->joinAttribute($_attributes_shipping['alias'], 'customer_address/' . $_attributes_shipping['attribute'], 'default_shipping', null, 'left');
            }
            $_customers->getSelect()->joinLeft(
                array('newsletter_subscriber' => 'newsletter_subscriber'),
                'newsletter_subscriber.customer_id=e.entity_id',
                array('subscriber_status' => 'subscriber_status')
            );
            foreach ($_customers as $_customer) {
                $_line = array();
                foreach ($_columns as $_column) {
                    $_line[] = $_field_enclosure . str_replace("\n", ", ", $_customer->getData($_column['local'])) . $_field_enclosure;
                }
                $file->streamWrite("" . implode($_field_separator, $_line) . "\n");
            }
            $file->streamClose();
            $this->_getLogger()->saveLog($_customers->getSize() . ' customer(s) exported.', $this->_process_name);
        } else {
            $this->_getLogger()->saveLog('No new customer(s) found to be exported.', $this->_process_name);
        }
        return true;
    }
    
    private function _getDateFilter() {
        $_filter = Mage::getModel('i4integration/flag')->getFlagByCode($this->_process_name);
        if ($_filter) {
            return $_filter->getFlag();
        }
        return false;
    }
    
    private function _updateFlag() {
        $_flag = Mage::getModel('i4integration/flag')->getFlagByCode($this->_process_name);
        Mage::getModel('i4integration/flag')->updateFlag($_flag->getIntegrationFlagId(), now());
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