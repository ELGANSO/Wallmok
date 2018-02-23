<?php
/**
 * Mrw
 *
 * @category    Interactiv4
 * @package     Interactiv4_Mrw
 * @copyright   Copyright (c) 2012 Interactiv4 SL. (http://www.interactiv4.com)
 */
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('i4mrwes_tablerate'), 'cashondelivery_surcharge', "decimal(12,4) DEFAULT NULL");
$installer->endSetup();
