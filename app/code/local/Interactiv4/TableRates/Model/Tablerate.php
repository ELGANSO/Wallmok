<?php
class Interactiv4_TableRates_Model_Tablerate extends Mage_Core_Model_Abstract {
    const COD_NOT_AVAILABLE = 0;
    const COD_SURCHARGE_ZERO = 1;
    const COD_SURCHARGE_FIXED = 2;
    const COD_SURCHARGE_PERCENTAGE = 3;
    
    /**
     *
     * @var array 
     */
    protected $_map = null;
    public function _construct() {
        parent::_construct();
        $this->_map = Interactiv4_TableRates_Model_Mysql4_Tablerate::getLogicalDbFieldNamesMap();
        $this->_init('i4tablerates/tablerate');
    }
    /**
     *
     * @param string $logicalFieldName
     * @return mixed 
     */
    public function getMappedData($logicalFieldName) {
        return $this->getData($this->getMappedName($logicalFieldName));
    }
    /**
     *
     * @param string $logicalFieldName
     * @return string
     * @throws Exception 
     */
    public function getMappedName($logicalFieldName) {
        if (isset($this->_map[$logicalFieldName])) {
            return $this->_map[$logicalFieldName];
        } else {
            throw new Exception("Invalid logical field name $logicalFieldName");
        }
    }
    
    /**
     *
     * @return int 
     */
    public function getCashOnDeliverySurchargeOption() {
        if (is_null($this->getMappedData('cashondelivery_surcharge'))) {
            return self::COD_NOT_AVAILABLE;
        } elseif (round($this->getData('cod_surcharge_price'), 2) > 0) {
            return self::COD_SURCHARGE_FIXED;
        } elseif (round($this->getData('cod_surcharge_percentage'), 2) > 0) {
            return self::COD_SURCHARGE_PERCENTAGE;
        } else {
            return self::COD_SURCHARGE_ZERO;
        }
    }
    
}
