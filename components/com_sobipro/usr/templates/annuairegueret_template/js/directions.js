
function geolocalisation(){
	var gc = new google.maps.Geocoder();
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function (po) {
			gc.geocode({"latLng":  new google.maps.LatLng(po.coords.latitude, po.coords.longitude) }, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					jQuery("#mj_rs_ref_lat").val(po.coords.latitude) ;
					jQuery("#mj_rs_ref_lng").val(po.coords.longitude) ;
					jQuery("#mj_rs_center_selector").val();
					jQuery("input#saddr").val(results[0]["formatted_address"]);
					jQuery("#saddr-msg").html('Votre position a été localisée');
				} else {
					jQuery("#saddr-msg").html('Erreur lors de la géolocalisation : '+ status);
				}
			});
		});
	}
	else{
		jQuery("#saddr-msg").html('Partage de position non autorisé.');
	}
}
/*
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
}*/

function googlemapdirections () {

	//Si le formulaire de directions est affiché par la map....
	if (jQuery('form.mapdirform').length>0) {
		
		//Ajout d'un champ de message
		jQuery("input#saddr").after('<div class="small text-info" id="saddr-msg">Recherche de votre position...</div>');
		
		//Localisation de l'utilisateur
		geolocalisation();
		//if (navigator.geolocation) navigator.geolocation.getCurrentPosition(setStartAddress, erreurPosition);
		
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
}