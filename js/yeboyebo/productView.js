jQuery(document).ready(function(){
	
	jQuery(".add").click(function(){
		var id = "#" + jQuery(this).attr('data-button');
		var name = jQuery(this).attr('data-button');
		var img = jQuery(this).attr('data-img');
		var price = number_format(jQuery(this).attr('data-price'),2);
		jQuery(id).click();
		var type = jQuery(this).attr('data-type');
		console.log(type);
		if(type == "radio")
		{
			var option = jQuery(this).attr('data-option');
			console.log("Option: "+option);
			//Elimino ingredientes añadidos de la misma opción
			jQuery("#burger .ingredient .remove[data-option|="+option+"]").parent().remove();
		}
		//Añado valor
		if(type == "checkbox")
		{
			console.log(name);
			jQuery("input[name='"+name+"'").each(function(){
				jQuery(this).val(jQuery(this).attr('data-value'));
				jQuery(this).prop("checked",true); 
			});
		}
		//Añado a cesta gráfica
		var copia = jQuery(this).parent().clone();
		
		jQuery(copia).children().removeClass("add");
		jQuery(copia).children().addClass("remove");
		jQuery(copia).children().addClass("remove").click(EventoEliminar);
		var amount = parseFloat(jQuery("#product-price-10_clone").text().replace("€","").replace(",","."));
		amount += parseFloat(price);
		jQuery("#product-price-10_clone").text(amount+"€");

		jQuery("#burger").append(copia);
	});
	
var EventoEliminar = function(){
		
		var burguer = jQuery(this).parent();
		var id = "#" + burguer.attr('data-button');
		jQuery(id).prop( "checked", false );
		var name = burguer.attr('data-button');
		var type = burguer.attr('data-type');
		var amount = parseFloat(jQuery("#product-price-10_clone").text().replace("€","").replace(",","."));
		var price = number_format(jQuery(this).attr('data-price'),2);
		
		if(type == "checkbox")
		{
			//Quito valor
			jQuery("input[name='"+name+"'").each(function(){
				jQuery(this).val('')
			});
		}
		amount -= parseFloat(price);
		jQuery("#product-price-10_clone").text(amount+"€");
		//Elimino hamburguesa
		burguer.remove();
	};

		jQuery(".remove").click(EventoEliminar);
		
		
		/*Abrir y cerrar paneles*/
	jQuery(document).on('click', '.panel-heading span.clickable', function(e){
	    var $this = jQuery(this);
		if(!$this.hasClass('panel-collapsed')) {
			$this.parents('.panel').find('.panel-body').slideUp();
			$this.addClass('panel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
		} else {
			$this.parents('.panel').find('.panel-body').slideDown();
			$this.removeClass('panel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
		}
	});

});




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
	