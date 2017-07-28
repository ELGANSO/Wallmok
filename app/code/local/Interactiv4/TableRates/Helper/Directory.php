<?php
class Interactiv4_TableRates_Helper_Directory extends Mage_Directory_Helper_Data {
    /**
     * Json representation of regions data
     *
     * @var string
     */
    protected $_regionJson2;
    public function getRegionJson2()
    {
        if (!$this->_regionJson2) {
            $cacheKey = 'CORE_DIRECTORY_REGIONS_JSON2_STORE'.Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $countryIds = array();
                foreach ($this->getCountryCollection() as $country) {
                    $countryIds[] = $country->getCountryId();
                }
                $collection = Mage::getModel('directory/region')->getResourceCollection()
                    ->addCountryFilter($countryIds)
                    ->load();
                $regions = array(
                    'config' => array(
                        'show_all_regions' => true,
                        'regions_required' => Mage::helper('core')->jsonEncode(array()),
                    )
                );
                foreach ($collection as $region) {
                    if (!$region->getRegionId()) {
                        continue;
                    }
                    $regions[$region->getCountryId()][$region->getRegionId()] = array(
                        'code' => $region->getCode(),
                        'name' => $this->__($region->getName())
                    );
                }
                $json = Mage::helper('core')->jsonEncode($regions);
                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            }
            $this->_regionJson2 = $json;
        }
        return $this->_regionJson2;
    }
}
