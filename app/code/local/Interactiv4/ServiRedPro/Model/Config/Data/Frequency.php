<?php
/**
 * Description of Data
 *
 * @author davidslater
 */
class Interactiv4_ServiRedPro_Model_Config_Data_Frequency extends Mage_Core_Model_Config_Data {
    const CRON_STRING_PATH = 'crontab/jobs/checkemailnotifications/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/checkemailnotifications/run/model';
    protected function _afterSave() {
        $cronExprString = $this->getData('groups/serviredproemailnotification/fields/checknotificationfrequency');
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
            throw new Exception($this->_getHelper->__('Unable to save Cron expression'));
        }
    }
    
    /**
     *
     * @return Interactiv4_ServiRedPro_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('serviredpro');
    }        
}
?>
