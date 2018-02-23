<?php
class Interactiv4_Mrw_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Export shipping table rates in csv format
     *
     */
    public function exportAction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = 'SELECT t.*, c.iso3_code country, r.default_name region FROM i4mrwes_tablerate t
            LEFT JOIN directory_country c ON c.country_id = t.dest_country_id
            LEFT JOIN directory_country_region r ON r.region_id = t.dest_region_id
            WHERE website_id = :website_id';
        $website = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        $tablerate = $connection->fetchAll($sql, array(':website_id' => $website->getId()));
        $data = array();
        $data[] = array(
            $this->_getMrwHelper()->__('Country'), 
            $this->_getMrwHelper()->__('Region/State'), 
            $this->_getMrwHelper()->__('Postcode/Zip'), 
            $this->_getMrwHelper()->__('Weight/Price (and above)'), 
            $this->_getMrwHelper()->__('Shipping Price'), 
            $this->_getMrwHelper()->__('Method'), 
            $this->_getMrwHelper()->__('CashOnDelivery Surcharge'), 
            $this->_getMrwHelper()->__('Minimum COD Surcharge'), 
            $this->_getMrwHelper()->__('Price vs Dest'));
        foreach($tablerate as $rate) {
            $data[] = array(
                $rate['country'],
                $rate['dest_region_id'] == 0 ? '*' : $rate['region'],
                $rate['dest_zip'] ? $rate['dest_zip'] : '*',
                $rate['weight'],
                $rate['price'],
                $rate['method'],
                $rate['cashondelivery_surcharge'],
                $rate['cod_min_surcharge'],
                $rate['price_vs_dest']
            );
        }
        $content = '';
        foreach($data as $line) {
            $content .= $this->_arrayToCsv($line, ',', '"', true) . "\n";
        }
        $fileName   = 'tablerates.csv';
        $this->_prepareDownloadResponse($fileName, $content);
    }
    protected function _arrayToCsv( array &$fields, $delimiter = ',', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');
        $output = array();
        foreach ( $fields as $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }
            if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }
        return implode( $delimiter, $output );
    }
    
    /**
     *
     * @return Interactiv4_Mrw_Helper_Data 
     */
    protected function _getMrwHelper() {
        return Mage::helper('i4mrwes');
    }
}
