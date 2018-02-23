<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Cron
{
    
	private $_logger;

    public function runSyncStock() {
        $_process_name = 'i4sync_stock';
        $this->_getLogger()->saveLog('Sync Stock has started.', $_process_name, true);
        $_sync = Mage::getModel('i4integration/sync_stock'); 
        $_sync->execute();
        $this->_getLogger()->saveLog('Sync Stock has finished.', $_process_name, true);
        unset($_sync);
    }
    
    public function runSyncPrice() {
        $_process_name = 'i4sync_price';
        $this->_getLogger()->saveLog('Sync Price has started.', $_process_name, true);
        $_sync = Mage::getModel('i4integration/sync_price'); 
        $_sync->execute();
        $this->_getLogger()->saveLog('Sync Price has finished.', $_process_name, true);
        unset($_sync);
    }
    
    public function runSyncCustomer() {
        $_process_name = 'i4sync_customer';
        $this->_getLogger()->saveLog('Sync Customer has started.', $_process_name, true);
        $_sync = Mage::getModel('i4integration/sync_customer'); 
        $_sync->execute();
        $this->_getLogger()->saveLog('Sync Customer has finished.', $_process_name, true);
        unset($_sync);
    }
    
    public function runSyncOrder() {
        $_process_name = 'i4sync_order';
        $this->_getLogger()->saveLog('Sync Order has started.', $_process_name, true);
        $_sync = Mage::getModel('i4integration/sync_order'); 
        $_sync->execute();
        $this->_getLogger()->saveLog('Sync Order has finished.', $_process_name, true);
        unset($_sync);
    }
    
    public function runSyncCatalog() {
        $_process_name = 'i4sync_catalog';
        $this->_getLogger()->saveLog('Sync Catalog has started.', $_process_name, true);
        $_sync = Mage::getModel('i4integration/sync_catalog'); 
        $_sync->execute();
        $this->_getLogger()->saveLog('Sync Catalog has finished.', $_process_name, true);
        unset($_sync);
    }
    
    private function _humanSize($size) {
        $suffix = array('Bytes','KB','MB','GB','TB', 'PB','EB','ZB','YB','NB','DB');
        $i = 0;
        while ($size >= 1024 && ($i < count($suffix) - 1)){
            $size /= 1024;
            $i++;
        }
        return round($size, 2).' '.$suffix[$i];
    }
    
    private function _logDebug($message, $size = false) {
        $_log_file = 'i4integration-debug-' . date('Y-m-d') . '.log';
        Mage::log($message, null, $_log_file);
        if ($size) {
            Mage::log("Memory: " . $this->_humanSize($size) . ".", null, $_log_file);
        }
    }
    
    protected function _getLogger() {
    	if(!isset($this->_logger)) {
    		$this->_logger = Mage::getModel('i4integration/logs');
    	}
    	return $this->_logger;
    }
        
}