<?php
abstract class Interactiv4_Mrw_Model_Shipping_Carrier_Abstract extends Mage_Shipping_Model_Carrier_Abstract
{
    public function getTrackingInfo($tracking)
    {
        $info = array();
        $result = $this->getTracking($tracking);
        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }
        return false;
    }
    public function isTrackingAvailable()
    {
        return true;
    }
    public function isCityRequired()
    {
        return false;
    }
    /*public function isZipCodeRequired()
    {
        return true;
    }*/
}
