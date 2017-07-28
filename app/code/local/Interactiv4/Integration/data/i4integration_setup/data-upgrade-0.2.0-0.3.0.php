<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

//Sync Price
$insert = array('code' => 'i4sync_customer', 'label' => 'Sync Customer');
Mage::getModel('i4integration/process')->setData($insert)->save();
