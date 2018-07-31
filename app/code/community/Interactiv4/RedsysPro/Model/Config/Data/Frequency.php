<?php

/**
 * Redsys Pro
 *
 * @category    Interactiv4
 * @package     Interactiv4_RedsysPro
 * @copyright   Copyright (c) 2015 Interactiv4 SL. (http://www.interactiv4.com)
 * @author      Oscar Salueña Martín <oscar.saluena@interactiv4.com> @osaluena
 * @author      David Slater
 */
class Interactiv4_RedsysPro_Model_Config_Data_Frequency extends Mage_Core_Model_Config_Data {

    const CRON_STRING_PATH  = 'crontab/jobs/i4checkemailnotifications/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/i4checkemailnotifications/run/model';

    /**
     * @throws Exception
     */
    protected function _afterSave() {
        $cronExprString = $this->getData('groups/i4redsysproemailnotification/fields/checknotificationfrequency');

        try {
            Mage::getModel('core/config_data')
                    ->load(self::CRON_STRING_PATH, 'path')
                    ->setValue($cronExprString)
                    ->setPath(self::CRON_STRING_PATH)
                    ->save();
            Mage::getModel('core/config_data')
                    ->load(self::CRON_MODEL_PATH, 'path')
                    ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                    ->setPath(self::CRON_MODEL_PATH)
                    ->save();
        } catch (Exception $e) {
            throw new Exception($this->_getHelper()->__('Unable to save Cron expression'));
        }
    }
    
    /**
     *
     * @return Interactiv4_RedsysPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4redsyspro');
    }        

}
