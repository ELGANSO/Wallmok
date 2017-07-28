<?php
/**
 * Description of Data
 *
 * @author davidslater
 */
class Interactiv4_EmailDownloader_Helper_Data extends Mage_Core_Helper_Abstract {
    
    /**
     *
     * @param string $message
     * @return \Interactiv4_EmailDownloader_Helper_Data 
     */
    public function log($message) {
        Mage::log($message, null, 'i4emaildownloader.log');
        return $this;
    }
    
}
