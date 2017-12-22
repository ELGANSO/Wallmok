<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
/*if(!file_exists(Mage::getBaseDir('media') .'/MrwLabels')) {
	mkdir(Mage::getBaseDir('media') .'/MrwLabels');
}*/
/**
 * cambiamos el numero de lineas del atributo street address para que incluya más
 * lineas para acomodar el formato de entrada a lo que espera MRW:
 * CodigoTipoVia: Dirección descripción del tipo de vía o su abreviatura (CALLE, CL, AVENIDA, AV...).
 * Via: Nombre de la vía de la dirección del destinatario.
 * Numero: Número de la dirección del destinatario del envío.
 * Resto: otra información referente a la dirección del destinatario (escalera,piso, puerta, bloque, edificio...).
 * 
 */
Mage::getSingleton('eav/config')->getAttribute('customer_address', 'street')->setMultilineCount(4)->save();
/**
 * Necesitamos guardar tambien algunos valores del config, modificados
 * el numero de lineas que sera cuatro para las direcciones
 * y tambien los formatos por defecto que tenemos en el admin para los address
 * que no queremos que tengan tantos saltos de líneas.
 * @see Mage_Core_Model_Config::saveConfig()
 */
$config = new Mage_Core_Model_Config();
$config->saveConfig('customer/address/street_lines', "4");
$format = <<<EOF
{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}<br/>
{{depend company}}{{var company}}<br />{{/depend}}
{{if street1}}{{var street1}} {{/if}}
{{depend street2}}{{var street2}} {{/depend}}
{{depend street3}}{{var street3}}, {{/depend}}
{{depend street4}}{{var street4}}<br />{{/depend}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}<br/>
{{var country}}<br/>
{{depend telephone}}T: {{var telephone}}{{/depend}}
{{depend fax}}<br/>F: {{var fax}}{{/depend}}
EOF;
$config->saveConfig('customer/address_templates/html',$format);
$format = <<<EOF
{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}
{{depend company}}{{var company}}{{/depend}}
{{if street1}}{{var street1}} {{/if}}{{depend street2}}{{var street2}} {{/depend}}{{depend street3}}, {{var street3}}{{/depend}}{{depend street4}}{{var street4}}{{/depend}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}
{{var country}}
T: {{var telephone}}
{{depend fax}}F: {{var fax}}{{/depend}}
EOF;
$config->saveConfig('customer/address_templates/text',$format);
$format = <<<EOF
{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}|
{{depend company}}{{var company}}|{{/depend}}
{{if street1}}{{var street1}} {{/if}}{{depend street2}}{{var street2}} {{/depend}}{{depend street3}}{{var street3}}, {{/depend}}{{depend street4}}{{var street4}}|{{/depend}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}|
{{var country}}|
{{depend telephone}}T: {{var telephone}}{{/depend}}|
{{depend fax}}<br/>F: {{var fax}}{{/depend}}|
EOF;
$config->saveConfig('customer/address_templates/pdf',$format);
$format = <<<EOF
#{prefix} #{firstname} #{middlename} #{lastname} #{suffix}<br/>#{company}<br/>#{street0} #{street1} #{street2} #{street3} #{street4}<br/>#{city}, #{region}, #{postcode}<br/>#{country_id}<br/>T: #{telephone}<br/>F: #{fax}
EOF;
$config->saveConfig('customer/address_templates/js_template',$format);
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('i4mrwes_ships')};
        CREATE TABLE {$this->getTable('i4mrwes_ships')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned, -- Shipment Id
          `url` tinytext NOT NULL,
          `log_fecha_alta` datetime DEFAULT NULL,
  		  `log_fecha_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		DROP TABLE IF EXISTS {$this->getTable('i4mrwes_tablerate')};
		CREATE TABLE {$this->getTable('i4mrwes_tablerate')} (
		  `pk` int(10) unsigned NOT NULL auto_increment,
		  `website_id` int(11) NOT NULL default '0',
		  `dest_country_id` varchar(4) NOT NULL default '0',
		  `dest_region_id` int(10) NOT NULL default '0',
		  `dest_zip` varchar(10) NOT NULL default '',
		  `weight` decimal(12,4) NOT NULL default '0.0000',
		  `price` decimal(12,4) NOT NULL default '0.0000',
		  `method` varchar(4) NOT NULL default '0',
		  PRIMARY KEY  (`pk`),
		  UNIQUE KEY `dest_country` (`website_id`,`dest_country_id`,`dest_region_id`,`dest_zip`,`weight`,`method`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
