function changeDetailsStackingOrder() {

	//on déplace la carte, les categories et les news 
	if (jQuery(window).width() < 767) {
				
		jQuery(".SPDE-Galery").insertAfter(jQuery("#activite_detaillee"));
		jQuery(".SPTitle").insertBefore(".SPDetailEntry-Sidebar-adresse");
		jQuery(".spField#title").insertAfter(".SPTitle");		
	}
}


function setStartAddress(position) {
  var infopos = position.coords.latitude +"," +position.coords.longitude;
  jQuery("input#saddr").val(infopos);
  jQuery("#saddr-msg").html('Votre position a été localisée');
}

function erreurPosition(error) {
    var info = "Erreur lors de la géolocalisation : ";
    switch(error.code) {
    case error.TIMEOUT:
    	info += "Timeout !";
    break;
    case error.PERMISSION_DENIED:
    info += "Vous n’avez pas donné la permission";
    break;
    case error.POSITION_UNAVAILABLE:
    	info += "La position n’a pu être déterminée";
    break;
    case error.UNKNOWN_ERROR:
    info += "Erreur inconnue";
    break;
    }
	jQuery("#saddr-msg").html(info);


}
 
jQuery(window).load(function(){ 

	//changeDetailsStackingOrder();	
		
	//Map détails :  géolocalisation pour calcul de l'itinéraire
	if (jQuery('form.mapdirform').length>0) {
		
		jQuery("input#saddr").after('<div class="small text-info" id="saddr-msg">Recherche de votre position...</div>');
		if (navigator.geolocation) navigator.geolocation.getCurrentPosition(setStartAddress, erreurPosition);
		
		//Afficher le detail de l'itinéraire
		jQuery('form.mapdirform').find('.button').click(function() {

			dirslink = jQuery('.directions-show');
			dirslink.css('visibility', 'visible');
			
			dirslink.click(function() {
				dirs=jQuery('.directions');
				sectiondiv = jQuery(this).parents('section').children('div');
				if (jQuery(this).hasClass('closed')) {
					dirs.show();
					jQuery(this).removeClass('closed');
					jQuery(this).addClass('open');
				}
				else
				{
					//Mise a jour de la hauteur de la section pour affichage de l'itinéraire
					//sectiondiv.height(sectiondiv.height()-dirs.height());
					dirs.hide();
					jQuery(this).removeClass('open');
					jQuery(this).addClass('closed');
					
					//sectiondiv.css('height','auto');
					
				}
				
			});		
		});	
	}
	
});