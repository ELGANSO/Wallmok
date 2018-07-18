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

			$pass =openssl_decrypt(  base64_decode($data["password"]), 'AES-256-CBC', "S0!0c0re99",0, "c0mb0c4l4d4c0mb0" );

			//$db = new PDO($data["driver"].':dbname='.$data["nombrebd"].';host='.$data["servidor"].';port='.$data["puerto"],$data["usuario"],$pass);
			$db = new PDO('sqlsrv:Server=35.224.210.132\sqlexpress, 53100;Database=SIRDemo','pruebas', '2de01ad4#');

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
			//$this = null;
			Mage::log($e,null,"conexiones.log");
		}
	}

	public function isAvailable()
	{
		$this->online = "Abierto";
		return true;

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

		try{
			Mage::log("Abro transaccion",null,"ivan.log");
			//Inicio Transacci�n
			$this->conexion->beginTransaction();

			Mage::log("Creo Comanda",null,"ivan.log");
			//Creo Comanda
			$comanda = $this->creaComanda($data);
			//$this->creaEnvioComanda($data,$comanda);

			Mage::log("Añado lineas",null,"ivan.log");
			//Añado lineas
			foreach ($order->getAllItems() as $item) {


				$json = "";
				if(!$item->getParentItem()){
					//Son INGREDIENTES
					if($item->getProduct()->getTypeId() == "bundle") {
						$json                               = $lineSerializer->serialize( $item, $data['items'] );
						$data['items'][ $i ]['descripcion'] = $this->getLineDescription( $data['items'][ $i ]['nombre'], $json );
					}
						$linea = $this->creaLineaComanda($comanda,$data['items'][$i],null,$json);
					Mage::log("Linea creada: ".$data['items'][$i]['descripcion'],null,"ivan.log");
	    		}else{
					Mage::log($item->getSku()." ".$item->getParentItem()->getSku(),null,"ivan.log");
				}


				$i++;
			}
			//Inserto comanda en magento
			$increment_id = $order->getIncrementId();
			$this->creaComandaMagento($increment_id,$comanda["codcomanda"],$this->id);

			Mage::log("Commit",null,"ivan.log");
			$this->conexion->commit();
		}catch (Exception $e){
			Mage::log($e,null,"ivan.log");
			$this->conexion->rollBack();
			$this->eliminaComandaMagento($comanda);
			throw $e;
		}
		return true;
	}

	protected function creaComanda($d){
		
		$comanda = $this->getNextComanda();
		$codMesa = $this->getNextMesa();
		$this->creaMesa($codMesa,$comanda["codcomanda"],$d);
		$comanda['mesa'] =$codMesa;
		return $comanda;
	}

	protected function creaEnvioComanda($d,$comanda){

		$franja = $this->getFranja($d['franja'], $d['fechaRecogida'])[0];
		$d['payment_method'] = $this->getPaymentMethod($d['payment_method']);
		$sql = "INSERT INTO mg_datosenviocomanda (idtpv_comanda,mg_nombreenv,mg_apellidosenv,mg_ciudadenv,mg_direccionenv,mg_telefonoenv,mg_metodopago,mg_gastosenv,mg_email,mg_unidadesenv,mg_paisenv,mg_metodoenvio,mg_codpostalenv,mg_pesototal,mg_provinciaenv,wm_fecharec, wm_horarec) VALUES('".$comanda['idtpv_comanda']."','".$d['shipping_address']['firstname']."','".$d['shipping_address']['lastname']."','".$d['shipping_address']['city']."','".$d['shipping_address']['street']."','".$d['shipping_address']['telephone']."','".$d['payment_method']."','".$d['shipping_price']."','".$d['email']."','".$d['units']."','".$d['shipping_address']['country_id']."','".$d['shipping_method']."','".$d['shipping_address']['postcode']."','".$d['weight']."','".$d['shipping_address']['region']."','".$d['fechaRecogida']."','".$franja['franja']."')";
		try{
			//Mage::log($sql,null,"ivan.log");
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
		try {

			$franja = $this->getDateofFranja($d["franja"]);
			$date = DateTime::createFromFormat('d/m/Y H:i:s', $d["fechaRecogida"]." ".$franja);
			$recogida = str_replace(" ","T",$date->format("Y-m-d H:i:s"));
			$sql = "INSERT INTO comandas_2 (Comanda,Mesa, Orden_Codigo,Articulo_Codigo,Orden_Cocina,Codigos_Cocina,Camarero,Texto_Articulo,Texto_Auxiliar,Precio_Extras,Unidades,Precio_Articulos,Descuento_Porcentaje,IVA_Porcentaje,Precio_Linea,Terminal,Formato_Cocina,Fecha_Creado,Fecha_Terminado,Situacion_Cocina) 
			VALUES('".$comanda['codcomanda']."','".$comanda['mesa']."','1',N'".$d['sirtpv']."','1','000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000','','".$d['nombre']."','".$d['descripcion']."','".$d['pvpunitario']."','1','".$d['pvpunitario']."','0','".$d['iva']."','".$d['pvptotaliva']."','1','','".$recogida."','','Creado');";
			Mage::log($sql,null,"sincro.log");
			$res = $this->conexion->prepare( $sql );
			$res->execute();
			//Añado los puntos de la linea a su franja
			if ( empty( $d['cooked_cost'] ) ) {
				$d['cooked_cost'] = 0;
			}

			$this->addOrderOnFranja( $d['franja'], '', $d['cooked_cost'], $date->format('Y-m-d') );

		}catch (Exception $e){
			Mage::log($e->getMessage(),null,"sincro.log");
			throw $e;
		}
		return true;
	}
	protected function creaPagoComanda($comanda,$d,$arqueo){

		$idpago = $this->getNextPago();
		$idsincro =  $comanda['codcomanda']."_".$idpago;

		$sql = "INSERT INTO tpv_pagoscomanda (idpago,idtpv_comanda,codcomanda,codtienda,estado,codtpv_puntoventa,editable,nogenerarasiento,importe,codtpv_agente,fecha,idsincro,ptepuntos,idtpv_arqueo,codpago) VALUES ('".$idpago."','".$comanda['idtpv_comanda']."','".$comanda['codcomanda']."','".$this->codtienda."','Pagado','".$this->puntodeventa."','True','False','".$d['grand_total']."','".$this->agente."','".date('Y-m-d')."','".$idsincro."','True','".$arqueo."','TARJ');";
		$res = $this->conexion->prepare($sql);
		$res->execute();

		$sql = "UPDATE tpv_comandas set pendiente = '0', estado = 'Cerrada' where codigo = '".$comanda['codcomanda']."'";
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

		$sql = "select max(Comanda) as codcomanda from comandas_2;";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$id = $res[0]["codcomanda"];
		$id++;

		//Actualizo
		/*$sql = "update tpv_secuenciascomanda set valor = '".$id."' where prefijo = '".$this->arqueo."';";
		$res = $this->conexion->prepare($sql);
		$res->execute();*/
		return array("codcomanda"=>$id);
	}
	protected function getNextMesa(){

		$sql = "select max(nombre)as cod from mesas where nombre like 'P%';";
		$res = $this->conexion->prepare($sql);
		$res->execute();
		$res = $res->fetchAll();
		$id = explode(".",$res[0]["cod"])[1];
		$id++;

		return "P.".$id;
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

		$sql = "SELECT nextval('tpv_pagoscomanda_idpago_seq')";
		$res1 = $this->conexion->prepare($sql);
		$res1->execute();
		Mage::log("ID Pago: \n",null,"ivan.log");
		Mage::log($res1,null,"ivan.log");

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

		Mage::log("get Descripcion ",null,"ivan.log");
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

		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

		if(!isset($date) || empty($date)) {
			$date = date("d/m/Y");
		}

		if(!isset($hour) || empty($hour)) {
			$hour = Mage::getModel( 'core/date' )->date( 'H:i:s' );
		}
		if($date->format('Y-m-d') == date('Y-m-d')) {
			$sql = "select f.idfranja,f.franja, f.puntosmax from wm_franjas as f where f.franja >= '" . $hour . "' and idrestaurante = " . $this->getId() . " group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		}else{
			$sql = "select f.idfranja,f.franja, f.puntosmax from wm_franjas as f where idrestaurante = " . $this->getId() . " group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		}
		$franjasdisponibles = $readConnection->fetchAll($sql);

		//Obtengo los puntos usados para el dia y la franja
		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date->format('Y-m-d')."' or l.fecha is null) and f.franja >= '".$hour."' and f.idrestaurante = ".$this->getId()." group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		$franjasConCostes = $this->preparaFranjas($readConnection->fetchAll($sql));

		$franjas = array();
		$coste = $this->getBagCost();
		$puntosReservados = 0;

		foreach ($franjasdisponibles as $franja){

			if(isset($franjasConCostes[$franja['idfranja']])){
				$franja = $franjasConCostes[$franja['idfranja']];

				if(!isset($franja['puntosusados']) || empty($franja['puntosusados'])){
					$franja['puntosusados'] = 0;
				}

				if($franja['puntosusados'] < $franja['puntosmax'] && $coste <= ($franja['puntosmax']- $franja['puntosusados'] + $puntosReservados)){
					array_push($franjas,$franja);
					$puntosReservados = 0;
				}else{
					$puntosReservados = $franja['puntosmax']- $franja['puntosusados'];
				}
			}else{
				//No se ha usado todavia esta franja
				$franja['puntosusados'] = 0;
				array_push($franjas,$franja);
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
		$writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$franja = $this->getFranja($idfranja, $fecha)[0];
		if(empty($franja['puntosusados'])) $franja["puntosusados"] = 0;

		try {
			if ( $franja['puntosmax'] > ( $franja['puntosusados'] + $puntos ) ) {
				$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $franja["idfranja"] . "','" . $puntos . "','". $fecha . "')";
				Mage::log("1- ".$sql,null, "ivan.log");
				$writeConnection->query($sql);
			} else {

				$prev = $this->getPreviusFranja( $idfranja, $fecha );
				Mage::log("Prev: ".$prev,null,"ivan.log");
				if(empty($prev['puntosusados'])) $prev["puntosusados"] = 0;

				if ( $prev['puntosmax'] - $prev['puntosusados'] < $puntos ) {
					$puntosAux = $prev["puntosmax"] - $prev["puntosusados"];
					$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $prev["idfranja"] . "','" .$puntosAux. "','" . $fecha . "')";
					Mage::log("2- ".$sql,null, "ivan.log");
					$writeConnection->query($sql);
					$puntos -= $prev['puntosmax'] - $prev['puntosusados'];
				}
				$sql = "INSERT INTO wm_franjaxlinea (idtpv_linea,idfranja,puntos,fecha)VALUES('" . $idtpv_linea . "','" . $idfranja . "','" . $puntos . "','" . $fecha . "')";
				Mage::log("3- ".$sql,null, "ivan.log");
				$writeConnection->query($sql);

			}
			return true;
		}catch (Exception $e){
			Mage::log($e->getMessage(),null, "Exception.log");
			throw $e;
			return false;
		}
	}

	protected function getFranja($franja, $date){
		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date."' or l.fecha is null) and f.idfranja = '".$franja."' group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		$res = $readConnection->fetchAll($sql);
		if(empty($res) || count($res) == 0){
			$sql = "select f.idfranja,f.franja, f.puntosmax from wm_franjas as f where f.idfranja = '".$franja."'";
			$res = $readConnection->fetchAll($sql);
			$res["puntosusados"] = 0;
		}
		return $res;
	}
	protected function getDateofFranja($franja){
		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

		$sql = "select f.franja from wm_franjas as f where f.idfranja = $franja;";
		$res = $readConnection->fetchAll($sql);
		return $res[0]['franja'];
	}

	protected function getPreviusFranja($idfranja,$date){
		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "select f.idfranja,f.franja, f.puntosmax,sum(l.puntos) as puntosusados from wm_franjas as f left join wm_franjaxlinea as l on f.idfranja = l.idfranja where(l.fecha = '".$date."' or l.fecha is null) group by f.franja, f.puntosmax, f.idfranja order by franja asc;";
		Mage::log($sql, null,"ivan.log");
		$res = $readConnection->fetchAll($sql);
		Mage::log($idfranja, null,"ivan.log");
		Mage::log($res, null,"ivan.log");
		for($i = 0; $i < count($res); $i++){
			if($res[$i]["idfranja"] == $idfranja)
			{
				if($i > 0)
					return $res[$i-1];
				else
					return $res[$i+1];
			}
		}
		return false;
	}
	protected function getTipoDeEnvio($envio){
		if($envio == 'freeshipping_freeshipping')
		{
			$tipo = 'RECOGER';
		}else{
			$tipo = 'DOMICILIO';
		}
		return $tipo;
	}
	protected function getPaymentMethod($metodo){
		switch($metodo){
			case 'Payment by cards or by PayPal account':
				$metodo = 'Paypal';
				break;
			case 'freeshipping_freeshipping':
				$metodo = 'freeshipping';
				break;
			case 'PayPal Express Checkout':
				$metodo = 'Paypal';
				break;
		}

	return $metodo;
	}
	protected function creaMesa($nombre,$comanda,$d){
		Mage::log("Crea Mesa \n \n", null, "ivan.log");
		try {
			$now = new DateTime("now", new DateTimeZone('Europe/Madrid') );
			$now = $now->format('Y-m-d H:i:s');
			$now = str_replace(' ','T',$now);
			$sql = "INSERT INTO SIRDemo.dbo.mesas (nombre,Asociar_Mesa,Departamento,Situacion,Terminal_Bloqueo,comensales,Comanda_Actual,Serie_Factura,Numero_Factura,Camarero,texto_factura,Idioma,Cliente_codigo,Descuento_Porcien,Hora_Reserva,Hora_Ocupacion,Hora_Facturacion,Posicion_x,Posicion_y,tipo,sillas_ancho,sillas_alto,Sillas_arriba,Sillas_abajo,Sillas_izquierda,Sillas_derecha,separaciones,Texto_Auxiliar,Departamento_Facturacion,Avisos,Avisadores_Activado,Avisadores_Id,Avisadores_Terminal_Asignado,Relacion_Pedidos,Ultimo_Mensaje,Ultimo_Mensaje_Texto,Idioma2,PLU,Dispositivo_Apertura,Tarjeta_Descuento)
VALUES ('".$nombre."','','Pedidos','OCUPADA',0,0,'".$comanda."',0,0,'','','','',0,'".$now."','".$now."','".$now."',0,0,'',0,0,0,0,0,0,0,'"."WEB_".$d['increment_id']."_".$d['payment_method']."','Pedidos','',0,'','',0,0,'',0,'','',0);";
			Mage::log($sql,null,"sincro.log");
			$res = $this->conexion->prepare( $sql );
			$res->execute();
			//throw new Exception("Stop");

		}catch (Exception $e){
			Mage::log($e->getMessage(),null,"sincro.log");
			throw $e;
		}
		return true;
	}
	protected function preparaFranjas($franjas){
		$arr = array();
		foreach ($franjas as $f){
			$arr[$f["idfranja"]] = $f;
		}
		return $arr;
	}
}