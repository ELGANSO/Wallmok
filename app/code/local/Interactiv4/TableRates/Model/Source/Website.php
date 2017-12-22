<?php
/**
 * Description of Website
 *
 * @author davidslater
 */
class Interactiv4_TableRates_Model_Source_Website {
    
    /**
     *
     * @return array 
     */
    public function getWebsites() {
        $websiteOptions = array();
        foreach (Mage::getModel('core/website')->getCollection() as $website) {
            $websiteOptions[$website->getId()] = $website->getName();
        }  
        return $websiteOptions;
    }
    
    /**
     *
     * @return array 
     */
    public function toOptionArray() {
        $options = array();
        foreach ($this->getWebsites() as $value => $name) {
            $options[] = array('label' => $name, 'value' => $value);
        }
        return $options;
    }
}
?>
