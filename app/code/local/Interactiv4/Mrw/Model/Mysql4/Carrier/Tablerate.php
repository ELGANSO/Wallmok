<?php
class Interactiv4_Mrw_Model_Mysql4_Carrier_Tablerate extends Mage_Core_Model_Mysql4_Abstract {
    const CSV_COL_IDX_COUNTRY_ID = 0;
    const CSV_COL_IDX_REGION = 1;
    const CSV_COL_IDX_ZIP = 2;
    const CSV_COL_IDX_WEIGHT_PRICE = 3;
    const CSV_COL_IDX_PRICE_PERCENTAGE = 4;
    const CSV_COL_IDX_METHOD = 5;
    const CSV_COL_IDX_COD_SURCHARGE = 6;
    const CSV_COL_IDX_COD_MIN_SURCHARGE = 7;
    const CSV_COL_IDX_COD_PRICE_VS_DEST = 8;    
    
    const CSV_COL_COUNT = 9; 
    
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;
    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = array();
    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;
    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = array();
    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;
    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;
    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;
    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;
    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = array();
    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('i4mrwes/carrier_tablerate', 'pk');
    }
    /**
     * Return table rate array or false by rate request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array|false
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request) {
        $adapter = $this->_getReadAdapter();
        $bind = array(
            ':website_id' => (int) $request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id' => $request->getDestRegionId(),
            ':postcode' => $request->getDestPostcode(),
            ':weight' => (float) $request->getPackageWeight(),
            ':price' => (float) $request->getData('i4_table_price')         
        );
        $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('website_id=:website_id')
                ->order(array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'method DESC', 'price_vs_dest DESC', 'weight DESC'));
        // render destination condition
        $orWhere = '(' . implode(') OR (', array(
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                    "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",
                )) . ')';
        $select->where($orWhere);
        $select->where('((weight <= :weight and price_vs_dest = 0) or (weight <= :price and price_vs_dest = 1))');
        
        $result = $adapter->fetchAll($select, $bind);
        return $result;
    }
    /**
     * Return CashOnDelivery Surcharge Value
     *
     * @param Varien_Object
     * @return array|null
     */
    public function getCashondeliverySurchage(Varien_Object $request) {
        $adapter = $this->_getReadAdapter();
        $bind = array(
            ':website_id' => (int) $request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id' => $request->getDestRegionId(),
            ':postcode' => $request->getDestPostcode(),
            ':weight' => (float) $request->getPackageWeight(),
            ':method' => (string) $request->getMethod(),
            ':price' => (float) $request->getData('i4_table_price'),            
        );
        $select = $adapter->select()
                ->from($this->getMainTable(), array('cashondelivery_surcharge', 'cod_min_surcharge'))
                ->where('website_id=:website_id')
                ->order(array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'method DESC', 'price_vs_dest DESC', 'weight DESC'));
        // render destination condition
        $orWhere = '(' . implode(') OR (', array(
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                    "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                    "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                    "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",
                )) . ')';
        $select->where($orWhere);
        $select->where('((weight <= :weight and price_vs_dest = 0) OR (weight <= :price and price_vs_dest = 1)) ');
        $select->where('method = :method');
        $result = $adapter->fetchRow($select, $bind);
        $result = $result && isset($result['cashondelivery_surcharge']) ? $result : null;
        return $result;
    }
    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     * @param string|null $csvFile
     * @throws Mage_Core_Exception
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object, $csvFile = null) {
        $this->_getHelper()->log(__METHOD__);
        if (!$csvFile && !empty($_FILES['groups']['tmp_name']['i4mrwes']['fields']['import']['value'])) {  
            $csvFile = $_FILES['groups']['tmp_name']['i4mrwes']['fields']['import']['value'];
        }
        if (!$csvFile) {
            return $this;
        }
        
        $website = Mage::app()->getWebsite($object->getScopeId());
        $this->_importWebsiteId = (int) $website->getId();
        $this->_importUniqueHash = array();
        $this->_importErrors = array();
        $this->_importedRows = 0;
        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < self::CSV_COL_COUNT) {
            $io->streamClose();
            Mage::throwException($this->_getHelper()->__('El formato de la tabla de tarifas es incorrecto.'));
        }
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $rowNumber = 1;
            $importData = array();
            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();
            // delete old data by website and condition name
            $condition = array(
                'website_id = ?' => $this->_importWebsiteId,
            );
            $adapter->delete($this->getMainTable(), $condition);
            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber++;
                if (empty($csvLine)) {
                    continue;
                }
                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }
                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException($this->_getHelper()->__('Ha ocurrido un error importando la tabla de tarifas.'));
        }
        $adapter->commit();
        if ($this->_importErrors) {
            $importErrors = $this->_importErrors;
            array_unshift($importErrors, "");
            $error = $this->_getHelper()->__('%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s', $this->_importedRows, implode("<br/>", $importErrors));
            Mage::getSingleton('core/session')->addNotice($error);
        }
        return $this;
    }
    /**
     * Load directory countries
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _loadDirectoryCountries() {
        if (!is_null($this->_importIso2Countries) && !is_null($this->_importIso3Countries)) {
            return $this;
        }
        $this->_importIso2Countries = array();
        $this->_importIso3Countries = array();
        /** @var $collection Mage_Directory_Model_Mysql4_Country_Collection */
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection->getData() as $row) {
            $this->_importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->_importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }
        return $this;
    }
    /**
     * Load directory regions
     *
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _loadDirectoryRegions() {
        if (!is_null($this->_importRegions)) {
            return $this;
        }
        $this->_importRegions = array();
        /** @var $collection Mage_Directory_Model_Mysql4_Region_Collection */
        $collection = Mage::getResourceModel('directory/region_collection');
        foreach ($collection->getData() as $row) {
            $this->_importRegions[$row['country_id']][$row['code']] = (int) $row['region_id'];
        }
        return $this;
    }
    /**
     * Return import condition full name by condition name code
     *
     * @return string
     */
    protected function _getConditionFullName($conditionName) {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }
        return $this->_conditionFullNames[$conditionName];
    }
    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0) {
        // validate row
        if (count($row) != self::CSV_COL_COUNT) {
            $this->_importErrors[] = $this->_getHelper()->__('Tarifa con formato inválido en la fila #%s', $rowNumber);
            return false;
        }
        // validate country
        if (isset($this->_importIso2Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]])) {
            $countryId = $this->_importIso2Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]];
        } else if (isset($this->_importIso3Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]])) {
            $countryId = $this->_importIso3Countries[$row[self::CSV_COL_IDX_COUNTRY_ID]];
        } else if ($row[self::CSV_COL_IDX_COUNTRY_ID] == '*' || $row[self::CSV_COL_IDX_COUNTRY_ID] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = $this->_getHelper()->__("País inválido '%s' en la fila #%s.", $row[self::CSV_COL_IDX_COUNTRY_ID], $rowNumber);
            return false;
        }
        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[self::CSV_COL_IDX_REGION]])) {
            $regionId = $this->_importRegions[$countryId][$row[self::CSV_COL_IDX_REGION]];
        } else if ($row[self::CSV_COL_IDX_REGION] == '*' || $row[self::CSV_COL_IDX_REGION] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = $this->_getHelper()->__("Región/Provincia no válida '%s' en la fila #%s.", $row[self::CSV_COL_IDX_REGION], $rowNumber);
            return false;
        }
        // detect zip code
        if ($row[self::CSV_COL_IDX_ZIP] == '*' || $row[self::CSV_COL_IDX_ZIP] == '') {
            $zipCode = '';
        } else {
            $zipCode = $row[self::CSV_COL_IDX_ZIP];
        }
        // validate condition value
        $weight = $this->_parseDecimalValue($row[self::CSV_COL_IDX_WEIGHT_PRICE]);
        if ($weight === false || $weight < 0) {
             $this->_importErrors[] = $this->_getHelper()->__('Precio/peso "%s" en la fila #%s.', $row[self::CSV_COL_IDX_WEIGHT_PRICE], $rowNumber);
            return false;           
        }
        // validate price
        $price = $this->_parseDecimalValue($row[self::CSV_COL_IDX_PRICE_PERCENTAGE]);
        if ($price === false) {
            $this->_importErrors[] = $this->_getHelper()->__("Precio inválido '%s' en la fila #%s.", $row[self::CSV_COL_IDX_PRICE_PERCENTAGE], $rowNumber);
            return false;
        }
        
        $method = $this->_parseMethod($row[self::CSV_COL_IDX_METHOD]);
        if ($method === false) {
            $this->_importErrors[] = $this->_getHelper()->__("Método inválido '%s' en la file #%s.", $row[self::CSV_COL_IDX_METHOD]);
            return false;
        }
        $cashondelivery_surcharge = $this->_parseCashOnDeliverySurcharge($row[self::CSV_COL_IDX_COD_SURCHARGE]);
        if ($cashondelivery_surcharge === false) {
             $this->_getHelper()->__("Invalid Cash On Delivery Surcharge '%s' in the Row #%s.", $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);
        }
        
        // Validar el sobrecargo contrareembolso mínimo. 
        $minCodSurcharge = $this->_parseMinCodSurcharge($row[self::CSV_COL_IDX_COD_MIN_SURCHARGE], $cashondelivery_surcharge);    
        if ($minCodSurcharge === false) {
            $this->_importErrors[] = $this->_getHelper()->__('Invalid Minimum COD Surcharge "%s" in the Row #%s. The minimum COD surcharge must be greater or equal to zero, and can only be used where the Cash On Delivery Surcharge is specified as a percentage.', $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);
            return false;
        }     
        
        $priceVsDest = $row[self::CSV_COL_IDX_COD_PRICE_VS_DEST] ? $row[self::CSV_COL_IDX_COD_PRICE_VS_DEST]  : '0';
        if (array_search($priceVsDest, array('0', '1')) === false) {
             $this->_importErrors[] = $this->_getHelper()->__('Invalid value Price vs Dest value "%s" in the Row #%s. The value should be 0 (Weight vs Dest) or 1 (Price vs Dest).', $row[self::CSV_COL_IDX_COD_SURCHARGE], $rowNumber);
             return false;            
        }                
        
        
        
        $this->_getHelper()->log("[$countryId] [$regionId] [$zipCode] [$weight] [$price] [$method] [$cashondelivery_surcharge] [$minCodSurcharge] [$priceVsDest]");
        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F-%s-%d", $countryId, $regionId, $zipCode, $weight, $method, $priceVsDest);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = $this->_getHelper()->__("fila duplicada #%s (País '%s', Region/Provincia '%s', CP '%s', Precio '%s', Método '%s y CashOnDelivery Surcharge %s').", $rowNumber, $row[self::CSV_COL_IDX_COUNTRY_ID], $row[self::CSV_COL_IDX_REGION], $zipCode, $weight, $method, $cashondelivery_surcharge);
            return false;
        }
        $this->_importUniqueHash[$hash] = true;
        return array(
            $this->_importWebsiteId, // website_id
            $countryId, // dest_country_id
            $regionId, // dest_region_id,
            $zipCode, // dest_zip
            (float) $weight, // weight
            (float) $price, // price
            $method, // method
            $cashondelivery_surcharge,   // cashondelivery_surcharge
            $minCodSurcharge,
            $priceVsDest
        );
    }
    /**
     * Save import data batch
     *
     * @param array $data
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate
     */
    protected function _saveImportData(array $data) {
        if (!empty($data)) {
//            $columns = array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip',
//                'condition_name', 'condition_value', 'price');
            $columns = array(
                'website_id', 
                'dest_country_id', 
                'dest_region_id', 
                'dest_zip',
                'weight', 
                'price', 
                'method', 
                'cashondelivery_surcharge', 
                'cod_min_surcharge', 
                'price_vs_dest');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }
        return $this;
    }
    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal 
     *
     * @param string $value
     * @param int $precision
     * @return bool|float
     */
    protected function _parseDecimalValue($value, $precision = 4) {
        return $this->_getHelper()->parseDecimalValue($value, $precision);
    }
    /**
     * Parse and validate positive decimal value
     *
     * @see Mage_Shipping_Model_Mysql4_Carrier_Tablerate::_parseDecimalValue()
     * @deprecated since 1.4.1.0
     * @param string $value
     * @return bool|float
     */
    protected function _isPositiveDecimalNumber($value) {
        return $this->_parseDecimalValue($value);
    }
    
    /**
     * Se parsea el surcharge de contrareembolso. 
     * Si va en blanco, se devuelve null. 
     * Si no tiene forma numérica o porcentaje, se devuelve false. 
     * Se se parsea correctamente se devuelve tal como se recibe.
     * @param mixed $value
     * @return string|null|boolean 
     */
    protected function _parseCashOnDeliverySurcharge($value) {
        if (!isset($value) ) {
            return null;
        }
        $value = trim(strval($value));
        if ($value === "") {
            return null;
        }
        
        $asDecimal = $this->_parseDecimalValue($value);
        if ($asDecimal !== false) {
            if ($asDecimal >= 0) {
                return $value;
            } else {
                return false;
            }
        }
        
        if ($this->_getHelper()->parsePercentageValueAsFraction($value) !== false) {
            return $value;
        } else {
            return false;
        }
    }      
    
    /**
     * Validar el sobrecargo contrareembolso mínimo. O debe estar vacío, o
     * debe contener un decimal válido y positivo. Además, está opción sólo es
     * válida si se ha especificado un sobrecargo contrareembolso como un porcentaje.
     * @param type $value 
     * @param string $value
     * @param string $cashondelivery_surcharge
     * @return boolean 
     */
    protected function _parseMinCodSurcharge($value, $cashondelivery_surcharge) {
        // El valor puede estar en vacío cuando no hay mínimo. Devolvemos null
        if (empty($value)) {
            return null;
        }
        
        // No está vacío, entonces hay que contener un decimal válido y positivo.
        $minCodSurcharge = $this->_parseDecimalValue($value, 2);
        if ($minCodSurcharge === false || $minCodSurcharge < 0)  {
            return false;
        }
       
        // Sólo podemos especificar un mínimo si hemos especificado el COD surcharge como un porcentaje.    
        if ($this->_getHelper()->parsePercentageValueAsFraction($cashondelivery_surcharge) === false) {
            return false;
        } 
        return $minCodSurcharge;
    }     
    
    /**
     * Se pone el método MRW en la forma correcta con sus 0s al principio (a veces los quita excel)
     * y comprueba que el método es un método válido de MRW.
     * Se devuelve el método en su forma correcta si es válido, o false si no es válido.
     * @param string $method
     * @return string
     */
    protected function _parseMethod($method) {
        $method = str_pad($method, 4, '0', STR_PAD_LEFT);
        $methods = Mage::getSingleton('i4mrwes/shipping_carrier_mrw_source_method'); /* @var $methods Interactiv4_Mrw_Model_Shipping_Carrier_Mrw_Source_Method */
        if (array_key_exists($method, $methods->toOptionArray())) {
            return $method;
        } else {
            return false;
        }
    }
    
    /**
     *
     * @return Interactiv4_Mrw_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('i4mrwes');
    }    
}
