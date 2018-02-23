<?php
require_once ('../../app/Mage.php');
Mage::app('admin');
require_once(Mage::getBaseDir('lib').'/yeboyebo/restaurantes_online.php');
$restaurantes = new RestaurantesOnline();
error_reporting(0);

switch ($_POST["accion"]) {
	case 'selecionarRestaurante':
		$restaurantes->selectRestaurant($_POST["id"],$_POST["envio"]);
		echo json_encode($_POST["id"]);
		break;
	case 'getRestaurantAddress':
		echo json_encode($restaurantes->getRestaurantAddress($_POST["id"]));
		break;
	default:
			$data = array();

			if (isset($_POST['codPostal']) && !empty($_POST['codPostal'])) {
					$res = $restaurantes->getRestaurantes($_POST['codPostal']);
					$data["html"] = html_format($res);
					$data["json"] = json_encode($res);
			}else{
					$res = $restaurantes->getAllRestaurantes($_POST['codPostal']);
					$data["html"] = html_format($res);
					$data["json"] = json_encode($res);
			}
			echo json_encode($data);
		break;
}

function html_format($restaurantes){
	
	$html ='<ul>';

	foreach($restaurantes as $item){
		$html .= '<li data-id="'.$item["id"].'" class="'.$item["online"].'" style="background: url('.$item["imagen"].') no-repeat">
		<div class="box">
			<h2>'.$item['description'].'</h2>
			<p>'.$item["direccion"].'
				</br>
				Tel. '.$item["telefono"].'
			</p>
			<span>'.$item["online"].' - Horario: '.$item["horario"].'</span>
		</div	
		</li>';
	}
	$html .='</ul>';
	return $html;
}


?>