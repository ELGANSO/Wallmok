<?php 
require_once ('Serializers/OrderSerializer.php');
require_once ('Serializers/OrderLineSerializer.php');
require_once (Mage::getBaseDir().'/app/Mage.php');
Mage::app();

class Restaurante {

	private $id;
	private $descripcion;
	private $conexion;
	private $direccion;
	private $ciudad;
	private $provincia;
	private $codpostal;
	private $pais;
	private $arqueo;
	private $puntodeventa;
	private $agente;
	private $imagen;
	private $horaapertura;
	private $horacierre;
	private $online;
	private $telefono;

	public function __construct($data){
		try{
		$db = new PDO($data["driver"].':dbname='.$data["nombrebd"].';host='.$data["servidor"].';port='.$data["puerto"],$data["usuario"],$data["password"]);

		//Activo excepciones bd
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		//Asigno datos
		$this->conexion = $db;
		$this->id = $data["id"];
		$this->descripcion = $data['descripcion'];
		$this->direccion = $data["direccion"];
		$this->ciudad = $data['ciudad'];
		$this->provincia = $data['provincia'];
		$this->codpostal = $data['codpostal'];
		$this->pais = $data['pais'];
		$this->arqueo = $data['arqueo'];
		$this->puntodeventa = $data['puntodeventa'];
		$this->agente = $data['agente'];
		$this->codtienda = $data['codtienda'];
		$this->almacen = $data['almacen'];
		$this->imagen = $data['imagen'];
		$this->horaapertura = $data["horaapertura"];
		$this->horacierre = $data['horacierre'];
		$this->telefono = $data['telefono'];


		}catch(Exception $e){
			Mage::log($e,null,"conexiones.log");
		}
	}

	public function isAvailable()
	{
		try{
			//Compruebo si la tienda esta cerrada
			$res = $this->conexion->prepare("SELECT * FROM diascerrados WHERE fecha ='".date("Y-m-d")."' and cerrado = 'True';");
			$res->execute();
			$res = $res->fetchAll();
			$now = Mage::getModel('core/date')->date('H:i:s');
			if(count($res) == 0 && $now >= $this->horaapertura && $now < $this->horacierre){	
				$online = true; //Si no esta cerrada
				$this->online = "Abierto";
			}
			else{
				$online = false;
				$this->online = "Cerrado";
			}

		}catch(Exception $e){
			//Si se produce alguna excepci�n en la conexi�n, -> Restaurante no disponible
			$online = false;
			$this->online = $online;
			die($e);
		}
		return $online;
	}
	public function getDescription(){
		
		$description = array(
			"id"=>$this->id,
			"description" => $this->descripcion,
			"online" => $this->online,
			"direccion" => $this->direccion." ".$this->ciudad." (".$this->provincia.")",
			"horario" => $this->horaapertura." - ".$this->horacierre,
			"imagen" => $this->imagen,
			"telefono" => $this->telefono
			);
		return $description;
	}
	public function getId(){
		return $this->id;
	}
	/* Crea la comanda en la base de datos del restaurante */
	public function sendOrder($order)
	{
		$i = 0;
		$serializer = new OrderSerializer();
		$lineSerializer = new OrderLineSerializer();
		$data = $serializer->serialize($order); //Pedido serializado

		$arqueo = $this->getArqueo();
		try{
			//Inicio Transacci�n
			$this->conexion->beginTransaction();
			//Creo Comanda
			$comanda = $this->creaComanda($data,$arqueo);
			$this->creaEnvioComanda($data,$comanda);


			//Añado lineas
			foreach ($order->getAllItems() as $item) {

				if($parent = $item->getParentItem()){
					$lineaPadre = $this->getLineaComandaPadre($comanda,explode('-',$parent->getProduct()->getSku())[0]); 
	    		}else{
	    			$lineaPadre = false;
	    		}
				//Añado elementos de la hamburguesa
				$json = "";
				Mage::log($item->getProduct()->getTypeId(),null,"ivan.log");
	    		if($item->getProduct()->getTypeId() == 'bundle')
	    		{
	    			$json = $lineSerializer->serialize($item->getProduct(),$data['items']);
	    			$data['items'][$i]['nombre'] = $this->getLineDescription($data['items'][$i]['nombre'],$json);

	    			//throw new Exception('No te borres');
	    		}
	    		//Añado las líneas sin padre, las que tienen padre van en el json.
	    		if(!$lineaPadre)
	    		{
	    			$linea = $this->creaLineaComanda($comanda,$data['items'][$i],$lineaPadre,json_encode($json));
	    		}

				$i++;
			}

			//Inserto comanda en magento
			$increment_id = $order->getIncrementId();
			$this->creaComandaMagento($increment_id,$comanda["codcomanda"],$this->id);
			//throw new Exception();
			//Creo el pago de la comanda
			if($order->getPayment()->getMethod() != 'cashondelivery'){
				Mage::log("Creo Pago",null,"ivan.log");
				$this->creaPagoComanda($comanda,$data,$arqueo);
				Mage::log("Finalizo",null,"ivan.log");
			}

			$this->conexion->commit();
		}catch (Exception $e){
			Mage::log($e,null,"ivan.log");
			//throw $e;
			$this->conexion->rollBack();
			$this->eliminaComandaMagento($comanda);
		}
		return true;
	}

	protected function creaComanda($d,$arqueo){
		
		$comanda = $this->getNextComanda();

		$sql = "INSERT INTO tpv_comandas (codigo,idtpv_comanda,idtpv_arqueo, saldoconsumido,estado,hora, direccion, codtpv_puntoventa,codpago, total,nombrecliente,codpais,editable,codalmacen,saldopendiente,provincia,tipopago,codtpv_agente,pagado,anulada,fecha,neto,codtarifa,pendiente,saldonosincro,ptesaldo,ptesincrofactura,ptepuntos,codtienda,sincronizada,codpostal,tipodoc,totaliva,referencia,ciudad,wm_pedidoweb) 
		VALUES('".$comanda['codcomanda']."','".$comanda['idtpv_comanda']."', '".$arqueo."', 0,'Abierta','".date('H:i:s')."','".$d['shipping_address']['street']."', '".$this->puntodeventa."','CONT', '".$d['grand_total']."','".$d['shipping_address']['firstname']." ".$d['shipping_address']['lastname']."','".$d['shipping_address']['country_id']."','True','".$this->almacen."','".$d['grand_total']."','".$d['shipping_address']['region']."','Efectivo','".$this->agente."','0','False','".date('Y-m-d')."','".$d['subtotal']."','','".$d['grand_total']."',0,'False','True','True','".$this->codtienda."','False', '".$d['shipping_address']['postcode']."','VENTA','".$d['tax_amount']."','".$d['increment_id']."','".$d['shipping_address']['city']."',True);";
		Mage::log($sql,null,"ivan.log");
		$res = $this->conexion->prepare($sql);
		$res->execute();
		return $comanda;
	}

	protected function creaEnvioComanda($d,$comanda){

		$franja = $this->getFranja($d['franja'], $d['fechaRecogida'])[0];
		$sql = "INSERT INTO mg_datosenviocomanda (idtpv_comanda,mg_nombreenv,mg_apellidosenv,mg_ciudadenv,mg_direccionenv,mg_telefonoenv,mg_metodopago,mg_gastosenv,mg_email,mg_unidadesenv,mg_paisenv,mg_metodoenvio,mg_codpostalenv,mg_pesototal,mg_provinciaenv,wm_fecharec, wm_horarec) VALUES('".$comanda['idtpv_comanda']."','".$d['shipping_address']['firstname']."','".$d['shipping_address']['lastname']."','".$d['shipping_address']['city']."','".$d['shipping_address']['street']."','".$d['shipping_address']['telephone']."','".$d['payment_method']."','".$d['shipping_price']."','".$d['email']."','".$d['units']."','".$d['shipping_address']['country_id']."','".$d['shipping_method']."','".$d['shipping_address']['postcode']."','".$d['weight']."','".$d['shipping_address']['region']."','".$d['fechaRecogida']."','".$franja['franja']."')";
		try{
			Mage::log($sql,null,"ivan.log");
			$res = $this->conexion->prepare($sql);
			$res->execute();
			$resul = true;
		}catch(Exception $e){
			Mage::log($e,null,"ivan.log");
			throw $e;
			
			$resul = false;
		}
		return $resul;
	}

	protected function creaLineaComanda($comanda,$d, $sincroPadre,$json){
		Mage::log("Crea Linea de Comanda \n \n", null, "ivan.log");
		$linea = $this->getNextLineaComanda();
		$idsincro =  $comanda['codcomanda']."_".$linea;

		$sql = "INSERT INTO tpv_lineascomanda (idtpv_linea,idtpv_comanda,codcomanda,codtienda,idsincro,wm_idsincropadre, referencia,pvptotal, ivaincluido, canregalo, cantidad,dtolineal,descripcion, iva, pvpunitarioiva,dtopor,pvptotaliva,pvpsindto,pvpsindtoiva,pvpunitario,ptestock, wm_datoscomp, wm_estadococina) VALUES ('".$linea."','".$comanda['idtpv_comanda']."','".$comanda['codcomanda']."','".$this->codtienda."','".$idsincro."','".$sincroPadre."', '".$d['sku']."','".$d['pvptotal']."', 'True', 0, '".$d['cantidad']."',0,'".$d['nombre']."', '".$d['iva']."', '".$d['pvpunitarioiva']."',0,'".$d['pvptotaliva']."','".$d['pvpsindto']."','".$d['pvpsindtoiva']."','".$d['pvpunitario']."','True','".$json."','Por hacer')";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		//throw new Exception("Stop");
		if(empty($d['cooked_cost'])) $d['cooked_cost'] = 0;
		$this->addOrderOnFranja($d['franja'], $linea, $d['cooked_cost'], $d["fechaRecogida"]);

		return $linea;
	}
	protected function creaPagoComanda($comanda,$d,$arqueo){

		$idpago = $this->getNextPago();
		$idsincro =  $comanda['codcomanda']."_".$idpago;

		$sql = "INSERT INTO tpv_pagoscomanda (idpago,idtpv_comanda,codcomanda,codtienda,estado,codtpv_puntoventa,editable,nogenerarasiento,importe,codtpv_agente,fecha,idsincro,ptepuntos,idtpv_arqueo) VALUES ('".$idpago."','".$comanda['idtpv_comanda']."','".$comanda['codcomanda']."','".$this->codtienda."','Pagado','".$this->puntodeventa."','True','False','".$d['grand_total']."','".$this->agente."','".date('Y-m-d')."','".$idsincro."','True','".$arqueo."');";
		$res = $this->conexion->prepare($sql);
		$res->execute();

		$sql = "UPDATE tpv_comandas set pendiente = '0' where codigo = '".$comanda['codcomanda']."'";
		Mage::log($sql,null,"ivan.log");
		$res = $this->conexion->prepare($sql);
		$res->execute();
	}

	protected function getArqueo(){
		$sql = "select idtpv_arqueo from tpv_arqueos where abierta = true and diadesde = '".date('Y-m-d')."';";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();

		if(count($res) == 0)
		{
			$id = $this->getNextArqueo();
			//Creo arqueo	
			$sql = "INSERT INTO tpv_arqueos (idtpv_arqueo,diadesde,horadesde, abierta,codtpv_agenteapertura,ptoventa,codtienda,m010,b10,totalmov,totalvale,sincronizado,b500,m020,m1,m2,inicio,b20,diferenciatarjeta,b200,nogenerarasiento,totalcaja,totaltarjeta,diferenciaefectivo,diferenciavale,m001,b100,m002,b5,m005,m050,b50) VALUES('".$id."','".date('Y-m-d')."','".date('H:i:s')."', 'True','".$this->agente."','".$this->puntodeventa."','".$this->codtienda."',0,0,0,0,'False',0,0,0,0,0,0,0,0,'False',0,0,0,0,0,0,0,0,0,0,0);";
			$new = $this->conexion->prepare($sql);
			$new->execute();
		}
		else{
			//Devuelvo el arqueo encontrado
			$id = $res[0]["idtpv_arqueo"];
		}
		return $id;
	}
	protected function getNextArqueo(){
		$sql = "select max(idtpv_arqueo) from tpv_arqueos;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$id = explode($this->arqueo,$res[0]["max"])[1];
		$id++;
		return $this->arqueo.str_pad($id, 6, "0", STR_PAD_LEFT);
	}

	protected function getNextComanda(){

		$sql = "select max(codigo) as codcomanda, max(idtpv_comanda) as idtpv_comanda from tpv_comandas;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$id = explode($this->arqueo,$res[0]["codcomanda"])[1];
		$id++;
		$idtpv = $res[0]["idtpv_comanda"] + 1;
		//Actualizo
		$sql = "update tpv_secuenciascomanda set valor = '".$id."' where prefijo = '".$this->arqueo."';";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		return array("codcomanda"=>$this->arqueo.str_pad($id, 10, "0", STR_PAD_LEFT),"idtpv_comanda"=>$idtpv);
	}
	protected function getNextLineaComanda(){

		$sql = "select last_value from tpv_lineascomanda_idtpv_linea_seq;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$id = $res[0]["last_value"]+1;

		try{
		$sql = "SELECT nextval('tpv_lineascomanda_idtpv_linea_seq')";
		Mage::log($sql,null,"ivan.log");
		$res = $this->conexion->prepare($sql);
		$res->execute();
	}catch(Exception $e){
		Mage::log("Error ".$e,null,"ivan.log");
	}
		return $id;
	
	}
	protected function getLineaComandaPadre($comanda,$sku){
		$sql = "select idtpv_linea from tpv_lineascomanda  where referencia = '".$sku."' and codcomanda = '".$comanda['codcomanda']."';";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		return $res[0]["idtpv_linea"];
	}
	protected function getNextPago(){
		$sql = "select max(idpago) from tpv_pagoscomanda;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		return $res[0]["max"]+1;
	}
	protected function creaComandaMagento($increment_id,$codcomanda,$idRestaurante){

		$db = Mage::getSingleton('core/resource');
		$writeConnection = $db->getConnection('core_write');

		$sql = ("INSERT INTO comandas(increment_id,idrestaurante,codcomanda) VALUES('".$increment_id."','".$idRestaurante."','".$codcomanda."')");
		$writeConnection->query($sql);
	}
	protected function eliminaComandaMagento($comanda){

		$db = Mage::getSingleton('core/resource');
		$writeConnection = $db->getConnection('core_write');
		$sql = ("DELETE FROM comandas WHERE codcomanda ='".$comanda['codcomanda']."'");
		
		$writeConnection->query($sql);
	}

	protected function getLineDescription($name,$json){

		unset($json[0]);
		//Mage::log($json,null,"json.json");
		foreach ($json as $group) {
			foreach ($group['opciones'] as $item) {
				if($group['exclusivo'] == "S")
				{
					if($item['defecto'] == 'N' && $item['on']=='S')
					{
						$name .= ' '.$item['opcion'];
					}
				}else{
					if($item['defecto'] == 'N' && $item['on']=='S')
					{
						$name .= ' +'.$item['opcion'];
					}else if($item['defecto'] == 'S' && $item['on']=='N'){
						$name .= ' -'.$item['opcion'];
					}
				}
			}
		}
				Mage::log("Name ".$name,null,"ivan.log");
		return $name;
	}
	public function getFranjasDisponibles($date, $hour){

		if(!isset($date) || empty($date)) {
			$date = date("d/m/Y");
		}

		if(!isset($hour) || empty($hour)) {
			$hour = Mage::getModel( 'core/date' )->date( 'H:i:s' );
		}

		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date."' or l.fecha is null) and f.franja >= '".$hour."' group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		Mage::log($sql,null,"ivan.log");
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$franjas = array();
		$coste = $this->getBagCost();
		$puntosReservados = 0;

		foreach ($res as $franja){

			if($franja['puntosusados'] < $franja['puntosmax'] && $coste <= ($franja['puntosmax']- $franja['puntosusados'] + $puntosReservados)){
				array_push($franjas,$franja);
				$puntosReservados = 0;
			}else{
				$puntosReservados = $franja['puntosmax']- $franja['puntosusados'];
			}
		}

		return $franjas;
	}

	protected function getBagCost(){

		$cart = Mage::getModel('checkout/cart')->getQuote();
		$coste = 0;

		foreach ($cart->getAllItems() as $item) {
			$productId = $item->getProductId();
			$product = Mage::getModel('catalog/product')->load($productId);
			$coste += $product->getData('cooked_cost');
		}

		return $coste;
	}

	protected function addOrderOnFranja($idfranja, $idtpv_linea, $puntos, $fecha){
		Mage::log("\n \n addOrderOnFranja \n \n ",null,"ivan.log");
		$franja = $this->getFranja($idfranja, $fecha)[0];
		if(empty($franja['puntosusados'])) $franja["puntosusados"] = 0;

		try {
			if ( $franja['puntosmax'] > ( $franja['puntosusados'] + $puntos ) ) {
				$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $franja["idfranja"] . "','" . $puntos . "','". $fecha . "')";
				$new = $this->conexion->prepare( $sql );
				$new->execute();
			} else {

				$prev = $this->getPreviusFranja( $idfranja, $fecha );
				if(empty($prev['puntosusados'])) $prev["puntosusados"] = 0;

				if ( $prev['puntosmax'] - $prev['puntosusados'] < $puntos ) {
					$puntosAux = $prev["puntosmax"] - $prev["puntosusados"];
					$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $prev["idfranja"] . "','" .$puntosAux. "','" . $fecha . "')";
					$new = $this->conexion->prepare( $sql );
					$new->execute();
					$puntos -= $prev['puntosmax'] - $prev['puntosusados'];
				}
				$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $idfranja . "','" . $puntos . "','" . $fecha . "')";

				$new = $this->conexion->prepare( $sql );
				$new->execute();

			}
			return true;
		}catch (Exception $e){
			Mage::log($e,null, "Exception.log");
			Mage::log($sql,null, "Exception.log");
			return false;
		}
	}

	protected function getFranja($franja, $date){
		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date."' or l.fecha is null) and f.idfranja = '".$franja."' group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		return $res->fetchAll();
	}

	protected function getPreviusFranja($idfranja,$date){
		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date."' or l.fecha is null) group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		for($i = 0; $i < count($res); $i++){
			if($res[$i]["idfranja"] == $idfranja)
			{
				return $res[$i-1];
			}
		}
		return false;
	}
}