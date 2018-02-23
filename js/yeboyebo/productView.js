jQuery(document).ready(function(){

	var basePrice = parseFloat(jQuery("#price").text().replace("€","").replace(",","."));
	//Eventos
	jQuery(".add").click(function(){

		if (jQuery(this).hasClass('included')){
			return false;
		}
		var id = "#" + jQuery(this).attr('data-button');
		var name = jQuery(this).attr('data-button');
		var img = jQuery(this).attr('data-img');
		var amount = parseFloat(jQuery("#price").text().replace("€","").replace(",","."));
		var price = number_format(jQuery(this).attr('data-price'),2);
		var type = jQuery(this).attr('data-type');
		var option = jQuery(this).attr('data-option');
		//Desmarco input
		jQuery(id).click();
		if(type == "radio")
		{
			//Elimino ingredientes añadidos de la misma opción
			var priceElmino = jQuery("#burger .ingredient .remove[data-option|="+option+"]").attr("data-price");
			//if(priceElmino >0)
			//amount -= parseFloat(priceElmino);
			jQuery("#burger .ingredient .remove[data-option|="+option+"]").click();
		}
		//Añado valor
		if(type == "checkbox")
		{
			jQuery("input[name='"+name+"'").each(function(){
				jQuery(this).val(jQuery(this).attr('data-value'));
				jQuery(this).prop("checked",true); 
			});
		}
		//Añado a cesta gráfica
		var copia = jQuery(this).parent().clone();
		
		jQuery(copia).children().first().removeClass("add");
		jQuery(copia).children().first().addClass("remove");
		jQuery(copia).children().first().addClass("remove").click(EventoEliminar);
		
		console.log("default: "+jQuery(this).attr('data-default'));
		if(jQuery(this).attr('data-default') != 1){
			if(price > 0)
				amount += parseFloat(price);
			console.log(jQuery("#price").text()+" - "+price);
			jQuery("#price").text(number_format(amount,2)+"€");
		}
		jQuery("#selected").text(parseInt(jQuery("#selected").text())+1);
		jQuery("#burger").append(copia);

		//Marco original como incluido
		jQuery(this).removeClass("add");
		jQuery(this).addClass("included");
		
		//Actualizo contador de incluidos en grupos
		var included = parseInt(jQuery(".panel-title[data-option ='"+option+"'] #qtyIncluded").text());
		jQuery(".panel-title[data-option ='"+option+"'] #qtyIncluded").text(included+1);
	});
	
var EventoEliminar = function(){
		
		var burguer = jQuery(this);
		var id = "#" + burguer.attr('data-button');
		jQuery(id).prop( "checked", false );
		var name = burguer.attr('data-button');
		var type = burguer.attr('data-type');
		var option = burguer.attr('data-option');
		var amount = parseFloat(jQuery("#price").text().replace("€","").replace(",","."));
		var price = number_format(jQuery(this).attr('data-price'),2);

		if(type == "checkbox")
		{
			//Quito valor
			jQuery("input[name='"+name+"'").each(function(){
				jQuery(this).val('')
			});
		}
		console.log("default: "+jQuery(this).attr('data-default'));
		if(jQuery(this).attr('data-default') != 1){
			amount -= parseFloat(price);
			if(amount < basePrice) amount = basePrice;
			jQuery("#price").text(number_format(amount,2)+"€");
		}
		jQuery("#selected").text(parseInt(jQuery("#selected").text())-1);
		
		//Desmarco incluido
		jQuery(".ingredient div[data-button ='"+name+"']").removeClass("included");
		jQuery(".ingredient div[data-button ='"+name+"']").addClass("add");
		//Actualizo contador de incluidos en grupos
		var included = parseInt(jQuery(".panel-title[data-option ='"+option+"'] #qtyIncluded").text());
		jQuery(".panel-title[data-option ='"+option+"'] #qtyIncluded").text(included-1);
		//Elimino hamburguesa
		burguer.parent().remove();

	};
		jQuery(".remove").click(EventoEliminar);
		
		
		/*Abrir y cerrar paneles*/
	jQuery(document).on('click', '.panel-heading span.clickable, .panel-heading h3', function(e){
	    
	    var $this = jQuery(this);
		jQuery('.panel-body').slideUp();
		jQuery('.panel-body').addClass('panel-collapsed');
		jQuery('.panel-body').find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');

		$this.parents('.panel').find('.panel-body').slideDown();
		$this.removeClass('panel-collapsed');
		$this.find('.panel-body').show();
		$this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');

	});

	//Modificar producto
	jQuery(document).on('click', '.modify', function(e){
		if(jQuery('.product-img').is(':visible'))
		{
			 e.preventDefault(); 
			jQuery('.product-img').hide();
			var grupos = jQuery('.panel-heading span.clickable');
			grupos[0].click();
			jQuery('.option').fadeIn('slow');
		
		}else{

			jQuery('.option').hide();
			jQuery('.product-img').fadeIn('slow');
		}
	});

	//Inicializo configuración del producto
	var defaultProducts = jQuery('.ingredient').find('[data-default="1"]');
	defaultProducts.click();
	jQuery("#selected").text(defaultProducts.length);
});


//Funciones

function number_format(amount, decimals) {

    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0) 
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}
