<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Model_System_Config_Cron_Sync_Customer extends Mage_Core_Model_Config_Data
{

    const CRON_STRING_PATH = 'crontab/jobs/i4integration_sync_customer/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/i4integration_sync_customer/run/model';

    protected function _afterSave() {
        $cronExprString = $this->getData('groups/cronjob/fields/cron_syntax');
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
            throw new Exception(Mage::helper('i4integration')->__('Unable to save Cron expression'));
        }
    }

}
