<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function formatDate($value, $format, $separator) {
        $_values = explode($separator, $value);
        switch($format) {
        	case 'mm/dd/yyyy':
                if (@checkdate($_values[0], $_values[1], $_values[2])) {
                    $return['year'] = $_values[2];
                    $return['month'] = $_values[0];
                    $return['day'] = $_values[1];
                    return $return;
                }
                return false;
            break;
            case 'yyyy-mm-dd':
                if (@checkdate($_values[1], $_values[2], $_values[0])) {
                    $return['year'] = $_values[0];
                    $return['month'] = $_values[1];
                    $return['day'] = $_values[2];
                    return $return;
                }
                return false;
            break;
            default:
                return false;
        }
        return true;
    }
    
    public function getMappedFields($field) {
        return unserialize($field);
    }

}