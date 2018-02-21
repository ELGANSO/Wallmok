jQuery(document).ready(function(){
		/* Carga animada de elementos*/
	contentWayPoint();
	if(window.innerWidth >= 768){
		jQuery(".crop-img, #map").height(window.innerHeight-80);

			// Cerrar sub-menu movil
			jQuery(".parent").click(function(){
				if(jQuery(this).find('.sub-menu').css('display') != 'none'){
					jQuery(this).find('.sub-menu').fadeOut('slow');
				}else{
					jQuery(this).find('.sub-menu').fadeIn('slow');
				}
			});
			//Bloquear scroll al abrir carrito en móvil
			jQuery("#cartIcon, .block-cart-header .btn-remove").click(function(){
				if(jQuery('.cart-content').css('display')!='none'){
					console.log(1);
					jQuery('body').css('overflow','scroll');
				}else{
					console.log(2);
					jQuery('body').css('overflow','hidden');
				}
			});
	}
	/*Controlo vista de catalogo*/
	controlSelectorCatalogo();

	/* Buscar un restaurante con un código postal*/
	jQuery("#getRestaurant button").click(function(){
		jQuery("#loading").css('display','block');
		jQuery.ajax({
			  url: window.location.origin+"/lib/yeboyebo/ajaxRequest.php",
			  context: document.body,
			  dataType: "json",
			  data: jQuery("#getRestaurant").serialize(),
			  type: 'POST'
		}).done(function( data) {
			  jQuery("#loading, #envio").css('display','none');
			  jQuery(".selector").css('top','0');
			  jQuery(".selector").css('height','150px');
			  jQuery(".selector h1, .selector p").hide();
			  showRestaurants(data);
		});
	});
	/* Obtener todos los restaurantes */
	jQuery("#getAllRestaurants").click(function(){
		jQuery(".selector").hide();
		jQuery("#loading").css('display','block');
		jQuery.ajax({
			  url: window.location.origin+"/lib/yeboyebo/ajaxRequest.php",
			  context: document.body,
			  dataType: "json"
			}).done(function( data) {
				jQuery("#loading").css('display','none');
			  	showRestaurants(data);
		});
	});

	/*Recogida en tienda o envio a domicilio */
	jQuery("#envio div").click(function(){
		jQuery("#envio .active").removeClass("active");
		jQuery(this).addClass("active");
		if(jQuery(this).attr("data-type") == "domicilio")
		{
			jQuery(".selector p").text("Usted está haciendo un pedido con envío a domicilio. Por favor introduzca su código postal.");
			jQuery("#getAllRestaurants").hide();
		}else{
			jQuery(".selector p").text("Usted está haciendo un pedido de recogida en la tienda. Haga clic en la brújula o busque su ubicación para encontrar un restaurante cercano.");
			jQuery("#getAllRestaurants").show();
		}
		jQuery("#restaurantSelected input[name='envio']").val(jQuery(this).attr("data-type"));
	});


	// Enlaces de categorias, baja despacio
	jQuery(".menu-item").click(function(){
		jQuery('.main').animate({
			scrollTop: jQuery("#"+jQuery(this).attr('data-scroll')).offset().top 
		}, 2000);
	});

	//Mostrar localización (PopUp)
	jQuery(".localizacionBtn").click(function(){
		//Busco restaurantes y los añado en el mapa
		console.log("localización");
		jQuery.ajax({
			  url: window.location.origin+"/lib/yeboyebo/ajaxRequest.php",
			  context: document.body,
			  dataType: "json"
			}).done(function( data) {	
			  	loadMap(data.json,"mapLoc");
		});
	});

	//Abrir y cerrar menu movil
	jQuery("#menuBtn").click(function(){
		if(jQuery('.principal-menu').css('display') !== 'none') {
   			jQuery('.principal-menu').css('display','none');
		}else{
			jQuery('.principal-menu').css('display','block');
		}
	});


	/* ******** FUNCIONES ************* */
function showRestaurants(data){
	var html = data.html;
	if(html== undefined || html == ""){

		html = "<h2> Lo sentimos, no hay restaurantes disponibles</h2>";
	}

	jQuery("#restaurants").html(html);
	if(window.innerWidth >= 768){
		jQuery("#restaurants ul").height(window.innerHeight-80);
	}
	jQuery("#restaurants ul .Abierto").click(seleccionoRestaurante);
	loadMap(data.json,"map");
}

function controlSelectorCatalogo(){

	if(sessionStorage.getItem('restaurant') == undefined || sessionStorage.getItem('restaurant') < 0)
	{
		console.log("Entro");
		jQuery("#catalogSelector").hide();
		jQuery("#restaurantSelector").show();
	}
	else{
		jQuery("#restaurantSelector").hide();
		jQuery("#catalogSelector").show("slow");
	}
}

var seleccionoRestaurante = function(){
		
		jQuery("#restaurantSelected input[name='id']").val(jQuery(this).attr('data-id'));
		//Copio la información del restaurante para mostrarla en el menu
		jQuery(this).parent().parent().parent().parent().parent().find("#direccionMenu").html(jQuery(this).find(".box").html());
		sessionStorage.setItem('direccion',jQuery(this).find(".box").html());
		jQuery.ajax({
			  url: window.location.origin+"/lib/yeboyebo/ajaxRequest.php",
			  context: document.body,
			  dataType: "json",
			  data: jQuery("#restaurantSelected").serialize(),
			  type: 'POST'
		}).done(function( data) {
			console.log("Selecciono restaurante: "+data);
			sessionStorage.setItem('restaurant',data);
			//Compruebo visibilidad del catalogo
			controlSelectorCatalogo();
		});
		
	};

		/*Vuelvo a escoger restaurantes*/
	jQuery("#locations").click(function(){
		sessionStorage.setItem('restaurant',-1);
		controlSelectorCatalogo();
	});

});

function loadMap(json,map){
	jQuery(".crop-img img").hide();
	console.log(map);
	jQuery("#"+map).show();
	initMap(json, map);
}
 function initMap(json, mapId) {
	        var center = {lat: 40.43, lng: -3.68};
	        var map = new google.maps.Map(document.getElementById(mapId), {
	          zoom: 12,
	          center: center
	        });

	        
			jQuery.each(JSON.parse(json), function() {
				var parent = this;
	        	jQuery.ajax({
					  url: "https://maps.googleapis.com/maps/api/geocode/json?address="+this.direccion.replace(/ /g,"+")+"&key=AIzaSyBnDkhObiNe-pqPyW9iWuILBnX6VCu_NT4",
					  context: document.body,
					  dataType: "json",
					  type: 'GET'
				}).done(function( data) {
					var location = data.results[0].geometry.location;
					var marker = new google.maps.Marker({
			          	position: location,
			          	title: parent.description,
			          	map: map
			        });
			        infoWindowMap(marker,parent, mapId);
				});
	        });	
	        
	      }
function infoWindowMap(marker, json, mapId) {
	
	var html = getHtmlStore(json);
    var infowindow = new google.maps.InfoWindow({
      content: html
    });

    marker.addListener('click', function() {
      infowindow.open(marker.get(mapId), marker);
    });
}
function getHtmlStore(json){
	console.log(json);
	var html = "<style> h2,p,span{ color: #3b4245;}</style>";
	html += "<div><h2>"+json.description+"</h2><p>"+json.direccion+"</p><span>"+json.horario+"</span></div>";
	return html;
}
var contentWayPoint = function() {
		jQuery('.animate-box').waypoint( function( direction ) {
			console.log(direction);
			if( direction === 'down' && !jQuery(this).hasClass('animated') ) {
				jQuery(this.element).addClass('fadeInUp animated');
			}
		} , { offset: '70%' } );
		jQuery('.animate-box').waypoint( function( direction ) {
			if( direction === 'down' && !jQuery(this).hasClass('animated') ) {
				jQuery(this.element).addClass('fadeInUp animated');
			}
		} , { offset: '70%' } );
	};