function changeDetailsStackingOrder() {

	//on déplace la carte, les categories et les news 
	if (jQuery(window).width() < 767) {
				
		jQuery(".SPDE-Galery").insertAfter(jQuery("#activite_detaillee"));
		jQuery(".SPTitle").insertBefore(".SPDetailEntry-Sidebar-adresse");
		jQuery(".spField#title").insertAfter(".SPTitle");		
	}
}

jQuery(document).ready(function() {

	//Support swipe dans le défilé d'images dans la vue détail 
	jQuery("#spdecarousel").swiperight(function() {  
		jQuery("#spdecarousel").carousel('prev');  
	});  
	jQuery("#spdecarousel").swipeleft(function() {  
		jQuery("#spdecarousel").carousel('next');  
	});  
});
 
jQuery(window).load(function(){ 

	//changeDetailsStackingOrder();	
		
	//Si le formulaire de direction est affiché, on gélocalise...
	googlemapdirections();
	
});