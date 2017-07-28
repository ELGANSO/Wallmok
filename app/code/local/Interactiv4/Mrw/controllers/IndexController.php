<?php
class Interactiv4_Mrw_IndexController extends Mage_Core_Controller_Front_Action
{
    
    public function indexAction()
    {
        //probando conversion fecha con preg
        $fecha = "12092011";
        $fecha = preg_replace('/(\d{2})(\d{2})(\d{4})/i', '$1/$2/$3', $fecha);
        
        echo $fecha;
        exit;
    	
    	
    	
    	
    	//invocando al método TransmitirEnvio
        $client = new Zend_Soap_Client_DotNet("http://sagec-test.mrw.es/MRWEnvio.asmx?wsdl");
        
        $authinfo = array(
            'Cliente'=>'750533',
            'Password'=>'U9ZOmvYXACzI0LKV',
            //'UserName'=>'SGC02650FLOREX',
            'Franquicia'=>'02650'
        );
        
        $authHeader = new SoapHeader("http://www.mrw.es/", 'AuthInfoSWGE', $authinfo);
        $client->addSoapInputHeader($authHeader);
        
        $params = array(
            'request'=>array(
                'Fecha'=>"01/10/2011",
                'Nombre'=>'SERGEN SA',
                'VerificacionDireccion'=>'0',
                'Via'=>'CL',
                'Direccion'=>'Emilio Prados',
                'NumeroDireccion'=>'1',
                'RestoDireccion'=>'Pt 2, Atico B',
                'CodigoPostal'=>'29649',
                'Poblacion'=>'Mijas Costa',
                'EnFranquicia'=>'N',
                'Referencia'=>'0001541548145715',
                'Servicio'=>'0000',
                'Bultos'=>'1',
                'Kilos'=>'1',
                'Puentes'=>'1',
                'Nif'=>'27388871V',
                'Reembolso'=>'R',
                'ComisionReembolso'=> '0',
                'ImporteReembolso'=>'100,00',
                'Mercancia'=>'',
                'ValorDeclarado'=>'',
                'AtencionDe'=>'Oscar Garcia',
                'Telefono'=>'95258000',
                'Observaciones'=>'',
                'EntregaPartirDe'=>'10:00',
                'ConfirmacionInmediata'=>'N',
                'Retorno'=>'N',
                'Gestion'=>'N',
                'EntregaSabado'=>'N',
                'Entrega830'=>'N',
                'CodigoPromocion'=>''
            )
        );
        $result = $client->TransmitirEnvio($params);
        
        print_r($result);exit;
        
    }
    
    /**
     * testing porque coge los rates sin decimales.
     */
    public function ratesAction()
    {
    	/*Mage_Shipping_Model_Rate_Request $request)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':website_id'   => (int)$request->getWebsiteId(),
            ':country_id'   => $request->getDestCountryId(),
            ':region_id'    => $request->getDestRegionId(),
            ':postcode'     => $request->getDestPostcode(),
            ':weight'		=> (float)$request->getPackageWeight()
        );*/
    	
    	print_r(Mage::app()->getStore()->getWebsiteId());exit;
    	
    	$data = array(
    		'website_id' => Mage::app()->getStore()->getWebsiteId(),
    		'country_id' => 'ES',
    		''
    	);
    	
    	$request = Mage::getModel('shipping/rate_request', $data);
    	print_r($request);exit;
    	return Mage::getResourceModel('i4mrwes/carrier_tablerate')->getRate($request);
    }
    
    public function envioAction()
    {
        //funciona
        //invocando al método TransmEnvio
        $client = new Zend_Soap_Client_DotNet("http://sagec-test.mrw.es/MRWEnvio.asmx?wsdl");
        
        $fecha = $this->getRequest()->getParam('fecha', '03/12/2011');
        
        $authinfo = array(
            'CodigoFranquicia'=>'02650',
        	'CodigoAbonado'=>'750533',
            'UserName'=>'SGC02650FLOREX',
        	'Password'=>'U9ZOmvYXACzI0LKV',  
        );
        
        $authHeader = new SoapHeader("http://www.mrw.es/", 'AuthInfo', $authinfo);
        $client->addSoapInputHeader($authHeader);
        
        $params = array(
            'request'=>array(
                'DatosEntrega'=>array(
                    'Direccion'=>array(
                        'CodigoTipoVia'=>'CL',
                        'Via'=>'Emilio Prados',
                        'Numero'=>'2',
                        'CodigoPostal'=>'29649',
                        'Poblacion'=>'Mijas Costa',
                        'Provincia'=>'Malaga',
                        //'Estado'=>'Malaga',
                        //'CodigoPais'=>'ES'
                    ),
                    'Nif'=>'27388871N',
                    'Nombre'=>'Nombre de empresa',
                    'Telefono'=>'952580000',
                    'Contacto'=>'Antonio Contacto',
                    'ALaAtencionDe'=>'A la atencion de Juan Rodriguez',
                    /*'Horario'=>array(
                        'Rango'=>array('Desde'=>'10:00', 'Hasta'=>'12:00')
                    ),*/
                    //'Observaciones'=>'',
                ),
                'DatosServicio'=>array(
                    //'Fecha'=>"$fecha",
                    'Fecha'=>$fecha,
                	'Referencia'=>'0001541548145715',
                    'EnFranquicia'=>'N',
                    'CodigoServicio'=>'0000',
                    'NumeroBultos'=>'1',
                    'Peso'=>'1',
                    'NumeroPuentes'=>'1',
                    'EntregaSabado'=>'N',
                    'Entrega830'=>'N',
                    'EntregaPartirDe'=>'10:00',
                    'Gestion'=>'N',
                    'Retorno'=>'N',
                    'ConfirmacionInmediata'=>'N',
                    'Reembolso'=>'R',
                    'ImporteReembolso'=>'100,00',
                    'PortesDebidos'=>'N'
                )
            )
        );
        
       
        
        $result = $client->TransmEnvio($params);
        
        if($result->Estado == 1)
        {
            //ok
            $params = array(
                'fecha'=>$fecha,
            	'solicitud' => $result->NumeroSolicitud,
            	'numeroEnvio' => $result->NumeroEnvio
            );
            
            $this->_forward('etiqueta', null, null, $params);
            
        } else {
            //print_r($result);//debug
            echo $result->Mensaje;
            exit;
        }
        
    }
    
    
    
    
    
    
    
    
    public function trackAction()
    {
    	$trackingNumber = $this->getRequest()->getParam('n');
    	
    	/*
    	 * 026508010927
026508010910
026508010908
026508010934
 
Con Incidencias:
 
026508010935
026508010937
 
En reparto:
 
026508010904
026508010926
    	 */
    	
    	
        //invocando al método TransmEnvio
        $client = new Zend_Soap_Client_DotNet("http://seguimiento.mrw.es/swc/wssgmntnvs.asmx?WSDL");
        
        //print_r($client->getFunctions());exit;
        
        $params = array(
        	'Franquicia'=>'02650',
        	'Cliente'=>'750533',
            'Password'=>'J8E24E',
        	'NumeroMRW'=>'026508010114',
        	'Agrupado'=>0,
        	'Referencia'=>''
        );
        
        $result = $client->SeguimientoNumeroEnvioMRWNacional($params);
        
        echo $client->getLastRequest();
        
        print_r($result);exit;
        
        if($result->Estado == 1)
        {
            //ok
            $params = array(
                'fecha'=>$fecha,
            	'solicitud' => $result->NumeroSolicitud,
            	'numeroEnvio' => $result->NumeroEnvio
            );
            
            $this->_forward('etiqueta', null, null, $params);
            
        } else {
            //print_r($result);//debug
            echo $result->Mensaje;
            exit;
        }
    	
    }
    
    
    
    
    
    public function etiquetaAction()
    {
        $numeroEnvio = $this->getRequest()->getParam('numeroEnvio');
        $fecha = $this->getRequest()->getParam('fecha');
        
$envelope = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <AuthInfo xmlns="http://www.mrw.es/">
      <CodigoFranquicia>02650</CodigoFranquicia>
      <CodigoAbonado>750533</CodigoAbonado>
      <UserName>SGC02650FLOREX</UserName>
      <Password>U9ZOmvYXACzI0LKV</Password>
    </AuthInfo>
  </soap:Header>
  <soap:Body>
    <GetEtiquetaEnvio xmlns="http://www.mrw.es/">
      <request>
        <NumeroEnvio>$numeroEnvio</NumeroEnvio>
        <FechaInicioEnvio>$fecha</FechaInicioEnvio>
        <ReportTopMargin>1100</ReportTopMargin>
        <ReportLeftMargin>650</ReportLeftMargin>
      </request>
    </GetEtiquetaEnvio>
  </soap:Body>
</soap:Envelope>
EOF;
        $this->_saveEtiqueta($this->_sendRequest($envelope, "http://sagec-test.mrw.es/MRWEnvio.asmx?wsdl"), $numeroEnvio);
        
        
    }
    
    
    
    protected function _sendRequest($request,$url)
	{
		$headers = array(             
            //"Content-Type: application/soap+xml; charset=utf-8" //esto hace que funcione curl2
            "Content-Type: text/xml; charset=utf-8"//esto hace que funcione curl
        ); 
	    
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT, 60);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$request);
        $result=curl_exec ($ch); 
	    
        //print_r($result);exit;
        //print_r(curl_getinfo($ch));
        
	    return $result;
	    
	  
	}
    
	
	protected function _saveEtiqueta($result, $numeroEnvio)
	{
	    $dom = new DOMDocument();
	    $dom->loadXML($result);
	    $envioResult = $dom->documentElement->firstChild->firstChild->firstChild;
	    
	    $simpleXml = simplexml_import_dom($envioResult);
	    $estado = $simpleXml->Estado;
	    $msg = $simpleXml->Mensaje;
	    $imageData = base64_decode($simpleXml->EtiquetaFile);
	    
	    //guardamos file
	    $fileName = Mage::getBaseDir('media') .'/MrwLabels/'."$numeroEnvio.pdf";
	    $result = file_put_contents ($fileName, $imageData);
	    
	    if($result !== FALSE)
	    {
	        //renderizamos a browser
    	    header("Content-type: application/pdf");
            echo ($imageData);
	    } else {
	        echo "ha habido un error generando el pdf";
	    }   
	    
	}
    
    
    
    
    
    
    /**
     * TEST WITH CURL:
     */
    
    
    public function curlAction()
    {
    
        
$envelope = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <AuthInfo xmlns="http://www.mrw.es/">
      <CodigoFranquicia>02650</CodigoFranquicia>
      <CodigoAbonado>750533</CodigoAbonado>
      <UserName>SGC02650FLOREX</UserName>
      <Password>U9ZOmvYXACzI0LKV</Password>
    </AuthInfo>
  </soap:Header>
  <soap:Body>
    <TransmEnvio xmlns="http://www.mrw.es/">
      <request>
        <DatosEntrega>
          <Direccion>
            <CodigoDireccion></CodigoDireccion>
            <CodigoTipoVia>CL</CodigoTipoVia>
            <Via>Emilio Prados</Via>
            <Numero>2</Numero>
            <Resto>Portal 3, Atico B</Resto>
            <CodigoPostal>29649</CodigoPostal>
            <Poblacion>Mijas</Poblacion>
            <Provincia>Malaga</Provincia>
          </Direccion>
          <Nif>string</Nif>
          <Nombre>string</Nombre>
          <Telefono>string</Telefono>
          <Contacto>string</Contacto>
          <ALaAtencionDe>string</ALaAtencionDe>
          <Observaciones>string</Observaciones>
        </DatosEntrega>
        <DatosServicio>
          <Fecha>29/09/2011</Fecha>
          <Referencia>0001541548145715</Referencia>
          <EnFranquicia>N</EnFranquicia>
          <CodigoServicio>0000</CodigoServicio>
          <NumeroBultos>1</NumeroBultos>
          <Peso>1</Peso>
          <NumeroPuentes>1</NumeroPuentes>
          <EntregaSabado>N</EntregaSabado>
          <Entrega830>N</Entrega830>
          <EntregaPartirDe>10:00</EntregaPartirDe>
          <Gestion>N</Gestion>
          <Retorno>N</Retorno>
          <ConfirmacionInmediata>N</ConfirmacionInmediata>
          <Reembolso>R</Reembolso>
          <ImporteReembolso>100,00</ImporteReembolso>
          <PortesDebidos>N</PortesDebidos>
        </DatosServicio>
      </request>
    </TransmEnvio>
  </soap:Body>
</soap:Envelope>
EOF;
//echo $envelope;exit;
print_r($this->_sendRequest($envelope, "http://sagec-test.mrw.es/MRWEnvio.asmx?wsdl"));
        
        
    }
    
    
    
    
    
    public function curl2Action()
    {
    //este funciona
$envelope = <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Header>
    <AuthInfoSWGE xmlns="http://www.mrw.es/">
      <Cliente>750533</Cliente>
      <Password>U9ZOmvYXACzI0LKV</Password>
      <Departamento></Departamento>
      <Franquicia>02650</Franquicia>
	</AuthInfoSWGE>
  </soap12:Header>
  <soap12:Body>
    <TransmitirEnvio xmlns="http://www.mrw.es/">
      <Request>
        <Fecha>01/01/2008</Fecha>
        <Nombre>SERGEN SA</Nombre>
        <VerificacionDireccion>0</VerificacionDireccion>
        <Via>CL</Via>
        <Direccion>REAL</Direccion>
        <NumeroDireccion>1</NumeroDireccion>
        <RestoDireccion>LOCAL 1</RestoDireccion>
        <CodigoPostal>09280</CodigoPostal>
        <Poblacion>PANCORBO</Poblacion>
        <EnFranquicia>N</EnFranquicia>
        <SMSRecogida>N</SMSRecogida>
        <SMSEntrega>N</SMSEntrega>
        <Referencia>0001541548145715</Referencia>
        <Servicio>0000</Servicio>
        <Bultos>1</Bultos>
        <Kilos>1</Kilos>
        <Puentes>1</Puentes>
        <Nif>41165251N</Nif>
        <Reembolso>R</Reembolso>
        <ComisionReembolso>0</ComisionReembolso>
        <ImporteReembolso>100,00</ImporteReembolso>
        <Mercancia></Mercancia>
        <ValorDeclarado></ValorDeclarado>
        <AtencionDe>DIEGO MARTINEZ</AtencionDe>
        <Telefono>947001001</Telefono>
        <Observaciones></Observaciones>
        <EntregaPartirDe>10:00</EntregaPartirDe>
        <ConfirmacionInmediata>N</ConfirmacionInmediata>
        <Retorno>N</Retorno>
        <Gestion>N</Gestion>
        <EntregaSabado>N</EntregaSabado>
        <Entrega830>N</Entrega830>
        <CodigoPromocion></CodigoPromocion>
      </Request>
    </TransmitirEnvio>
  </soap12:Body>
</soap12:Envelope>
EOF;
//echo $envelope;exit;
    print_r($this->_sendRequest($envelope, "http://sagec-test.mrw.es/MRWEnvio.asmx?wsdl"));
        
        
    }
    
    
    
    
    
    
 
    
}
