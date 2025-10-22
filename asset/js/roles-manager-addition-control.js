$(document).ready(function() {
	
	if(RolesManagerDeniedProperties){
		RolesManagerDeniedProperties.forEach(function(term){
		//$.find('[class="resource-property"]').each(function(obj){
			
			var target = '.resource-property[data-property-term="' + term + '"]';
			//var field = $(target).outerHTML();
			var field = $(target).prop('outerHTML');
			//$("<div />").append($("#el").clone()).html();
			
			//$( '<div style="display: none;">'+field+'</div>' ).replaceAll(target);
			
			//var obj = $('div[class="resource-property"]');
			//var term = obj.data('property-term');
			 //[data-property-term="'+v+'"]
			//$('[data-property-term="'+v+'"]').hide();
			//obj.find('textarea').attr('disabled', 'disabled');
			//obj.find('input').attr('disabled', 'disabled');
			//obj.find('select').attr('disabled', 'disabled');
			
			//console.log(field);
			//if(RolesManagerDeniedProperties.includes(term)){
				//field.ready(function() {
					
				//field.hide();
				//field.find('a').hide();
				//field.addClass('hidden');
				
				//console.log(obj);
				//});
			//}
		});
	}
	
});
