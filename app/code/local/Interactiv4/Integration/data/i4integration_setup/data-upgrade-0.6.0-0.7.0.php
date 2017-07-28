<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

//Sync Customer Flag
$insert = array('code' => 'i4sync_catalog', 'label' => 'Sync Catalog');
Mage::getModel('i4integration/process')->setData($insert)->save();
