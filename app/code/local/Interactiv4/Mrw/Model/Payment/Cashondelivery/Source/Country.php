<?php
/**
 * Description of Country
 *
 * @author davidslater
 */
class Interactiv4_Mrw_Model_Payment_Cashondelivery_Source_Country extends Mage_Adminhtml_Model_System_Config_Source_Country {
    protected static $_allAllowedCountries = null;
    
    public function toOptionArray($isMultiselect = false) {
        $options = parent::toOptionArray($isMultiselect);
        foreach ($options as $key => $option) {
            if ($option['value'] && !in_array($option['value'], self::getAllAllowedCountries()) ) {
                unset($options[$key]);
            }
        }
        return $options;
    }
    
    /**
     *
     * @return array 
     */
    public static function getAllAllowedCountries() {
        if (!is_array(self::$_allAllowedCountries)) {
            self::$_allAllowedCountries = explode(",", Mage::getStoreConfig("payment/i4mrwes_cashondelivery/all_allowed_countries"));
        }
        return self::$_allAllowedCountries;
    }    
    
}
?>
