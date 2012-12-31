/**
 * @version: $Id: jmapsmarker.js Cindy Johnson $
 * @package: Jmaps Marker Field Application
 * ===================================================
 * @author
 * Name: Cindy Johnson, websolutionware.com
 * Email: cindy[at]websolutionware.com
 * Url: http://www.websolutionware.com
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 websolutionware.com (http://www.websolutionware.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-06-09 11:59:04 +0200 (Sat, 09 June 2012) $
 * $Revision: 1100 $
 * $Author: Cindy Johnson $
 */
 
//LGW
//jQuery.noConflict();

if (typeof SPJmapsMarkerReq == 'undefined') {
	var SPJmapsMarkerReg = {};
	var jmapsMarkerArray = new Array();
	var jmapsMarkersBuilt = 0;
}
function SPJmapsMarkerGeoCoder(Opt, pid) {

	jQuery(document).ready(function() { 

		var jmfaUpdater = new SPJmapsMarkerUpdate(Opt); 
		if(pid) {
			var id = Opt.Id;
			try {
				jQuery('#' + pid).bind('click', function() {
					if(this.checked) {
						jQuery('#' + id).fadeTo('slow', 1);
						JmapsMarkerFields(id, true);
					} else {
							jQuery('#' + id).fadeTo('slow', 0.1);
							JmapsMarkerFields(id, false);
							}
				});
				if(jQuery('#' + pid).val() != undefined && !(jQuery('#' + pid).checked)) {
					jQuery('#' + id).fadeTo('slow', 0.1);
				}
				JmapsMarkerFields(id, false);
				function JmapsMarkerFields(id, state) {
					SP_id(id +'_'+'jmlatitude').enabled = state;
					SP_id(id +'_'+'jmlongitude').enabled = state;	
					SP_id(id +'_'+'jmcolor').enabled = state;
					SP_id(id +'_'+'jmimage').enabled = state;	
					jmfaUpdater.GetCoordinates(false);
				};
			} catch(e) {}
		}
	});
}

function SPJmapsMarkerUpdate(Opt) {
	this.Fields = [];
	this.Address = {};
	this.Opt = Opt;
	//LGW
	this.MarkerLock = false;
	
	this.Trigger;
	this.Address;
	
	//LGW : localisation par défaut
	this.defaultLocation = new google.maps.LatLng(46.1667, 1.8667);
	
	this.FieldEvent = function(field) {
		var jmfaUpdater = this;
		field.bind('blur', function() {
			jmfaUpdater.GetCoordinates(false);
		});		
	};
	
	this.ButtonEvent = function(field) {
		var jmfaUpdater = this;
		field.bind('click', function() {
			jmfaUpdater.GetCoordinates(true);
		});		
	};
	
	this.GetCoordinates = function(force) {
		change = false;
		if (force) {
			change = true;
		}
		// if these data has been changed - replace it in the array and eventually get new coordinates
		for(var i = 0; i < this.Fields.length; i++) {
			id = this.Fields[i].attr('id');
			field = jQuery('#' +  id);
			// if value has been changed
			if(this.Address[id] != field.attr('value')) {				
				this.Address[id] = field.attr('value');
				change = true;
			}
		}
		// if changed
		if(change) {
		
			//LGW
			// if the marker was adjusted manually before - ask the user first
			/*if( this.MarkerLock ) {
				change = confirm( this.Opt.ChngMsg );
			}*/
				
			//LGW
			if (change) {
		
				var searchAddress = new Array();
				c = 0;
				for(var i in this.Address) {
					searchAddress[c] = this.Address[i];
					c++
				}
				var jmfaGeocoder = new google.maps.Geocoder();
				var jmfaUpdater = this;
				jmfaGeocoder.geocode({'address': searchAddress.join('+')}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						// LGW : reset lock
			        	this.MarkerLock = false;
					
						jmfaUpdater.SetCoordinates(results[0].geometry.location);
					} 
				});	
			}
		}
	};
	
	//LGW
	this.SetCoordinates = function( data, init )
	{	
		var newLocation = new google.maps.LatLng( data.lat(), data.lng() );
		this.Map.setCenter( newLocation );
		
		this.AdjustCoordinates( data.lat(), data.lng() );	
		
		// if marker has been already created
		try {
			this.Marker.setPosition( newLocation );
		} catch( e ) {
			// otherwise create new marker
			this.Marker = new google.maps.Marker( {
				position: newLocation, 
				map: this.Map,
				animation: google.maps.Animation.DROP,
				draggable:true
			} );
		}
		
		// insert into current scope
		var watcher = this;
		google.maps.event.addListener( this.Marker, 'dragend', function ( ev ) {
			watcher.AdjustCoordinates( this.getPosition().lat(), this.getPosition().lng() );
			watcher.MarkerLock = true;
		} );
	};
	
	//LGW
	this.AdjustCoordinates = function( latitude, longitude )
	{
		jQuery( 'input#'+this.Opt.Id+'_'+'jmlatitude' ).val(latitude);
		jQuery( 'input#'+this.Opt.Id+'_'+'jmlongitude' ).val(longitude);
	};	
		
	this.JmapsMarkerInit = function() {
		var initialLocation = this.defaultLocation;
		
		//LGW : intialisation de la map d'affichage
		this.Map;
		this.Marker;
		var mapOptions = {
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP
	    }

		this.Map = new google.maps.Map( SP_id( this.Opt.Id+'_'+'canvas'), mapOptions );
		
		// Try W3C Geolocation (Preferred)
		var u = this;
		var tempLatitude = jQuery('input#'+this.Opt.Id+'_jmlatitude').val();
		var tempLongitude = jQuery('input#'+this.Opt.Id+'_jmlongitude').val();
		if ((!tempLatitude || tempLatitude == 0) && (!tempLongitude || tempLongitude == 0)) {
			if (navigator.geolocation) {
			
				navigator.geolocation.getCurrentPosition(function(position) {
				
					initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					try {
						u.SetCoordinates(initialLocation, true);
					} catch(e) {}
				}, function() { u.NoInit(); });
			}
			// Try Google Gears Geolocation
			else if (this.Opt.Sensor && google.gears) {
				var jmGeo = google.gears.factory.create('beta.geolocation');
				jmGeo.getCurrentPosition(function(position) {
					initialLocation = new google.maps.LatLng(position.latitude,position.longitude);
					try {
						u.SetCoordinates(initialLocation, true);
					} catch(e) {}
				}, function() { u.NoInit();	});
				// Browser doesn't support Geolocation
			} 
			else {
				if(!(google.gears)) {
					alert('Votre navigateur ne supporte pas la GeoLocation. Pour plus d\'infos: http://gears.google.com/ ');
				}
				this.NoInit();
			}
		}
		//LGW : la position est déjà renseignée
		else 
		{
			initialLocation = new google.maps.LatLng(tempLatitude,tempLongitude);
			u.SetCoordinates(initialLocation, true);
		}
			
	};
	this.NoInit = function() {
	
		//LGW: position par défaut
		this.SetCoordinates(this.defaultLocation, true);
	};
	this.JmapsMarkerInit();	
	// traverse address fields and store these as DOM objects
	for(var i = 0; i < Opt.Fields.length; i++) {
		try {
			this.Fields[i] = jQuery('#' +  Opt.Fields[i]) ;
		} catch(e) {}
	}	
	// last field is the trigger
	this.Trigger = Opt.Fields[Opt.Fields.length - 1];
	// traverse again and add events
	for(var i = 0; i < this.Fields.length; i++) {
		this.FieldEvent(this.Fields[i]);		
	}
	this.ButtonEvent(jQuery('#SPJmapsmarkerSetLatLong'));		
}
function SPJmapsMarkerAddMarkers() {
	jQuery(document).ready(function() { 
		if(jmapsMarkersBuilt != 0) {
//	      console.log('Build');
			if(window.jmaps_Initialize) {
//	        	console.log('About to INIT');	
//				jmaps_Initialize();
//				console.log('About to Add Search Markers');
				var cookieCount = jmapsMarkersBuilt;
				var iconOptions = new Object;
				buildJmapMarkers(jmapsMarkerArray, cookieCount, iconOptions);
				jmapsMarkersBuilt = 0;
	//			zoomCenterMap();
			}
		} else {
				if(window.jmaps_Initialize) {
					jQuery('#loadmessagehtml').hide();
				}
				}
	});
}