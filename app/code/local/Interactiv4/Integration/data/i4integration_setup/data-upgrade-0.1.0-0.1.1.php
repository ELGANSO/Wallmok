<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

//FTP
$insert = array('code' => 'ftp', 'label' => 'FTP');
Mage::getModel('i4integration/process')->setData($insert)->save();
//Sync Stock
$insert = array('code' => 'i4sync_stock', 'label' => 'Sync Stock');
Mage::getModel('i4integration/process')->setData($insert)->save();
