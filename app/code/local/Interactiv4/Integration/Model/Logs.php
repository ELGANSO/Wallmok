<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_Logs extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('i4integration/logs');
    }
    
    public function saveLog($message, $process_name, $log_anyway = false, $allow_url = false)
    {
        $_enable = null;
        if (!$log_anyway) { 
            $_enable = $settings = Mage::getStoreConfig($process_name . '/logs/enable');
        } else {
            $_enable = true;
        }
        if ($_enable) {
            $model = Mage::getModel('i4integration/logs');
            if ($allow_url) {
                $model->setLogDate(now())->setProcessName($process_name)->setMessage($message);
            } else {
                $model->setLogDate(now())->setProcessName($process_name)->setMessage(filter_var($message, FILTER_SANITIZE_STRING));
            }
            $model->save();
        }
    }
    
    public function cleanOld($process_name) {
        $_days = Mage::getStoreConfig($process_name . '/logs/save_days_logs');
        if ($_days) {
            $_date = new Zend_Date(Mage::app()->getLocale()->date(now()), Zend_Date::ISO_8601);
            $connection = Mage::getSingleton('core/resource')->getConnection('read');
            $table = Mage::getSingleton('core/resource')->getTableName('i4integration_logs');
            $connection->query("DELETE FROM {$table} WHERE process_name = '{$process_name}' AND log_date <= '{$_date->subDay($_days)->toString('yyyy-MM-dd HH:mm:ss')}';");
        }
    }
    
}