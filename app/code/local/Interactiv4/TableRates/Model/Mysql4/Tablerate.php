<?php
class Interactiv4_TableRates_Model_Mysql4_Tablerate extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Initialize resource model
     *
     */
    protected function _construct() {
        $this->_init(self::_getHelper()->getTableratesDbTable(), self::getDbTableIdField());
    }
    /**
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);
        self::prepareSelectColumns($select, $this->getTable(self::_getHelper()->getTableratesDbTable()));
        $selectStr = (string) $select;
        $this->_getHelper()->log($selectStr);
        return $select;
    }
    /**
     *
     * @return string 
     */
    public static function getDbTableIdField() {
        $map = self::getLogicalDbFieldNamesMap();
        return self::getDbTableFieldName($map['pk']);
    }
    /**
     * Puede que la tabla de tablerates para el método de envío tenga alguna 
     * columna cuyo nombre es distanto que lo normal para las extensiones i4. 
     * Hay que configurar las columnas así en el config del método de envío. 
     * P.ej. la columna id normalmente se llama 'pk', pero si es otra cosa (p.ej. "entity_id", 
     * pondremos en la config del módulo del método de envío esto:
     * carriers/carrier_code/i4tablerates/db_table_field_names/pk/entity_id
     * 
     * 
     * @param string $logicalName
     * @return string 
     */
    public static function getDbTableFieldName($logicalName) {
        $mappedFieldName = self::_getHelper()->getCarrierConfigDbTableFieldName($logicalName);
        return $mappedFieldName ? $mappedFieldName : $logicalName;
    }
    /**
     *
     * @return array 
     */
    public static function getLogicalDbFieldNames() {
        return array(
            'pk',
            'website_id',
            'dest_country_id',
            'dest_region_id',
            'dest_zip',
            'weight_price',
            'price_vs_dest',
            'price_percentage',
            'method',
            'product',
            'markup',
            'cashondelivery_surcharge',
            'cod_min_surcharge'
        );
    }
    /**
     *
     * @return array 
     */
    public static function getLogicalDbFieldNamesMap() {
        $map = array();
        foreach (self::getLogicalDbFieldNames() as $logicalName) {
            $map[$logicalName] = self::getDbTableFieldName($logicalName);
        }
        return $map;
    }
    /**
     *
     * @param Zend_Db_Select $select
     * @return \Zend_Db_Select 
     */
    public static function prepareSelectColumns(Zend_Db_Select $select, $table = 'main_table') {
        $map = self::getLogicalDbFieldNamesMap();
        
        $pricePercentageColName = $map['price_percentage'];
        if (self::_getHelper()->supportsMarkup()) {
            $markupColumnName = $map['markup'];
            $select->columns(array('shipping_price' => new Zend_Db_Expr("IF ({$table}.{$markupColumnName} = '0', {$table}.{$pricePercentageColName}, NULL)")));
            $select->columns(array('shipping_percentage' => new Zend_Db_Expr("IF ({$table}.{$markupColumnName} <> '0', {$table}.{$pricePercentageColName}, NULL)")));
            $select->columns(array('shipping_price_grid' => new Zend_Db_Expr("IF ({$table}.{$markupColumnName} = '0' and {$table}.{$pricePercentageColName} >= 0, {$table}.{$pricePercentageColName}, NULL)")));
            $select->columns(array('shipping_percentage_grid' => new Zend_Db_Expr("IF ({$table}.{$markupColumnName} <> '0' and {$table}.{$pricePercentageColName} >= 0, {$table}.{$pricePercentageColName}, NULL)")));            
        } else {
            
            $select->columns(array('shipping_price' => "{$table}.{$pricePercentageColName}"));
            $select->columns(array('shipping_percentage' => null));
            $select->columns(array('shipping_price_grid' => new Zend_Db_Expr("IF({$table}.{$pricePercentageColName} >= 0, {$table}.{$pricePercentageColName}, NULL)")));
            $select->columns(array('shipping_percentage_grid' => null));
            
        }
        
        $select->columns(array('is_enabled_grid' => new Zend_Db_Expr("IF ({$table}.{$pricePercentageColName} >= 0, 1, 0)")));
        
        $weightPriceColName = $map['weight_price'];
        if (self::_getHelper()->supportsPriceVsDest()) {
            $priceVsDestColName = $map['price_vs_dest'];
            $select->columns(array('weight_and_above' => new Zend_Db_Expr("IF ({$table}.{$priceVsDestColName} = '0' , {$table}.{$weightPriceColName}, NULL)")));
            $select->columns(array('price_and_above' => new Zend_Db_Expr("IF ({$table}.{$priceVsDestColName} <> '0' , {$table}.{$weightPriceColName}, NULL)")));
        } else {
            $select->columns(array('weight_and_above' => "{$table}.{$weightPriceColName}"));
            $select->columns(array('price_and_above' => null));            
        }
        
        if (self::_getHelper()->supportsCashOnDelivery()) {
            $cashOnDeliverySurchargeColName = $map['cashondelivery_surcharge'];
            $select->columns(array('cod_surcharge_price' => new Zend_Db_Expr("IF(not ISNULL({$table}.{$cashOnDeliverySurchargeColName}) and RIGHT({$table}.{$cashOnDeliverySurchargeColName}, 1) <> '%' , CAST({$table}.{$cashOnDeliverySurchargeColName} AS DECIMAL(10,2)), NULL) ")));
            $select->columns(array('cod_surcharge_percentage' => new Zend_Db_Expr("IF(RIGHT({$table}.{$cashOnDeliverySurchargeColName}, 1) = '%', CAST(LEFT({$table}.{$cashOnDeliverySurchargeColName}, LENGTH({$table}.{$cashOnDeliverySurchargeColName}) - 1) AS DECIMAL(10,2)), NULL)")));
        } else {
            $select->columns(array('cod_surcharge_price' => null, 'cod_surcharge_percentage' => null));
        }
        
        return $select;
    }
    /**
     *
     * @return Interactiv4_TableRates_Helper_Data 
     */
    protected static function _getHelper() {
        return Mage::helper('i4tablerates');
    }
}
