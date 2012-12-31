/**
 * @classDescription The Mapifies variable is the main class object for jMaps
 */
var Mapifies;

if (!Mapifies) Mapifies = {};

/**
 * The main object that holds the maps
 */
Mapifies.MapObjects = {};
Mapifies.MarkerArrays = {}; 


/**
 * Creates a new map on the passed element with the defined options.  Creates a global object that contains the map.
 * @method
 * @namespace Mapifies.MapObjects
 * @id Mapifies.MapObjects.Set
 * @alias Mapifies.MapObjects.Set
 * @param {jQuery} element The element that contains the map.
 * @param {Object} options An object that contains the options.
 * @return {Object} The object that contains the map.
 */
Mapifies.MapObjects.Set = function ( element, options ) {
	var mapName = jQuery(element).attr('id');
	var thisMap = new google.maps.Map(element, options);
	Mapifies.MapObjects[mapName] = thisMap;
	Mapifies.MarkerArrays[mapName] = {}; 
	Mapifies.MarkerArrays[mapName].markers = []; 
	return Mapifies.MapObjects[mapName];
};

/**
 * Adds an existing MapObject to additional objects and functions to 
 * @method
 * @namespace Mapifies.MapObjects
 * @id Mapifies.MapObjects.Append
 * @alias Mapifies.MapObjects.Append
 * @param {jQuery} element The element that contains the map
 * @param {Object} description The name of the object to create
 * @param {Object} appending The object or function to append
 */
Mapifies.MapObjects.Append = function ( element, description, appending ) {
	var mapName = jQuery(element).attr('id');
	Mapifies.MapObjects[mapName][description] = appending;
};

/**
 * Returns the current map object for the passed element
 * @method
 * @namespace Mapifies.MapObjects
 * @id Mapifies.MapObjects.Get
 * @alias Mapifies.MapObjects.Get
 * @param {jQuery} element The element that contains the map.
 * @return {Object} Mapifies The Mapifies object that contains the map.
 */
Mapifies.MapObjects.Get = function ( element ) {
	return Mapifies.MapObjects[jQuery(element).attr('id')];
};

/**
 * Returns the current map markers array for the passed element
 * @method
 * @namespace Mapifies.MapObjects
 * @id Mapifies.MapObjects.GetMarkers
 * @alias Mapifies.MapObjects.GetMarkers
 * @param {jQuery} element The element that contains the map.
 * @return {Array} Mapifies The array that contains the map markers.
 */
Mapifies.MapObjects.GetMarkers = function ( element ) {
	var mapID = jQuery(element).attr('id');
	return Mapifies.MarkerArrays[mapID].markers;
};

/**
 * The main function to initialise the map
 * @method
 * @namespace Mapifies
 * @id Mapifies.Initialise
 * @alias Mapifies.Initialise
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Object} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the map object and options.
 */
Mapifies.Initialise = function ( element, options, callback ) {
	/**
	 * Default options for Initialise
	 * @method
	 * @namespace Mapifies.Initialise
	 * @id Mapifies.Initialise.defaults
	 * @alias Mapifies.Initialise.defaults
	 * @param {String}  backgroundColor 		The color of the background div that shows while the map is loading or being redrawn (while dragging).
	 * @param {Object}  center 					An array that contains the Lat/Lng coordinates of the desired map center.
	 * @param {Boolean} disableDefaultUI 		Disable all of the default map controls and set them all individually.  Default false.
	 * @param {Boolean} disableDoubleClickZoom 	Defines if double clicking to zoom in on the map should be disabbled.  Default false.
	 * @param {Boolean} draggable 				Defines if the map is draggable or not.  Default true.
	 * @param {String}  draggableCursor 		String representing the desired cursor to show when a map is draggable.  Default null.
	 * @param {String}  draggingCursor 			String representing the desired cursor to show when a map is being dragged.  Default null.
	 * @param {Boolean} keyboardShortcuts 		Enable standard keyboard shortcuts for the map.  Default true.
	 * @param {Boolean} mapTypeControl 			Defines if the map type control is shown (allowing map to be switched from one type to another.  Default true.
	 * @param {String}  mapTypeControlTypes 	Defines the types of maps that will populate the map type control. Takes a map type constant such as ROADMAP, SATELLITE (default), HYBRID, TERRAIN.
	 * @param {String}  mapTypeControlPosition 	Position of the map type control, if shown, on the map UI. Default topRight.
	 * @param {String}  mapTypeControlStyle 	The style of the map control, if shown, to create.  Takes a string constant of either 'bar' (default), or 'menu'.
	 * @param {String}  mapTypeId 				The type of map to create initially.  Takes a map type constant such as ROADMAP (default), SATELLITE, HYBRID, TERRAIN.
	 * @param {Number}  maxZoom 				The maximum zoom level of the map.
	 * @param {Number}  minZoom 				The minimum zoom level of the map.
	 * @param {Boolean} noClear 				Defines if the map should be locked from being able to be cleared.  Default false.
	 * @param {Boolean} overviewMapControl 		Defines if the map overview is shown.  Default false.
	 * @param {Boolean} overviewMapControlOpened Defines if the map overview, if shown, is displayed in the open position on load.  Default false.
	 * @param {Boolean} panControl 				Defines if the map pan control is shown.  Default true.
	 * @param {String}  panControlPosition 		Position of the pan control, if shown, on the map UI. Default 'topLeft'.
	 * @param {Boolean} scaleControl 			Defines if the map scale control is shown.  Default true.
	 * @param {String}  scaleControlPosition 	Position of the scale control, if shown, on the map UI. Default 'bottomLeft'.
	 * @param {Boolean} scrollwheel 			Defines if the users ability to use the mouse scroll wheel to zoom in and out of the map should be enabbled.  Default false.
	 * @param {Boolean} streetViewControl 		Defines if the map streetview pegman control is shown.  Default true.
	 * @param {String}  streetViewControlPosition Position of the streetview pegman control, if shown, on the map UI. Default 'topLeft'.
	 * @param {Number}  zoom 					The initial zoom level of the map.
	 * @param {Boolean} zoomControl 			Defines if the map zoom control is shown.  Default true.
	 * @param {String}  zoomControlPosition 	Position of the zoom control, if shown, on the map UI. Default 'topLeft'.
	 * @param {String}  zoomControlStyle 		The style of the zoom control, if shown, to create.  Takes a string constant of either 'bar' (default), or 'menu'.
	 * @param {Boolean} debugMode 				Defines if the map object created is returned to the Firebug console.  Default false.
	 * @return {Object} The options for SearchAddress
	 */
	function defaults() {
		return {
			'backgroundColor': null,
			'center': ( google.maps ) ? new google.maps.LatLng(38.898748, -77.037684) : null,
			'disableDefaultUI': false,
			'disableDoubleClickZoom': false,
			'draggable': true,
			'draggableCursor': null,
			'draggingCursor': null,
			'keyboardShortcuts': true,
			'mapMaker': false,
			'mapTypeControl': true,
			'mapTypeControlTypes': ['roadmap','satellite'],
			'mapTypeControlPosition': 'topRight',
			'mapTypeControlStyle': 'bar',
			'mapTypeId': 'roadmap',
			'maxZoom': 18,
			'minZoom': 2,
			'noClear': false,
			'overviewMapControl': false,
			'overviewMapControlOpened': false,
			'panControl': true,
			'panControlPosition': 'topLeft',
			'scaleControl': false,
			'scaleControlPosition': 'bottomLeft',
			'scrollwheel': false,
			'streetViewControl': true,
			'streetViewControlPosition': 'topLeft',
			'zoom': 12,
			'zoomControl': true,
			'zoomControlPosition': 'topLeft',
			'zoomControlStyle': 'small',
			'debugMode': false
		};
	};
	options = jQuery.extend(defaults(), options);
	
//	if (typeof jQuery=="undefined") {
		var mapOptions = {
			'backgroundColor': null,
			'center': ( google.maps ) ? new google.maps.LatLng(38.898748, -77.037684) : null,
			'disableDefaultUI': false,
			'disableDoubleClickZoom': false,
			'draggable': true,
			'draggableCursor': null,
			'draggingCursor': null,
			'keyboardShortcuts': true,
			'mapMaker': false,
			'mapTypeControl': true,
			// mapTypeControlOptions is an object containing an array of the mapTypeIds, the postion, and the style
			'mapTypeControlOptions': {position: google.maps.ControlPosition.TOP_RIGHT, mapTypeIds: [google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.TERRAIN], style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
			'mapTypeId': null,
			'maxZoom': 18,
			'minZoom': 2,
			'noClear': false,
			'overviewMapControl': false,
			'overviewMapControlOptions': null,
			'panControl': true,
			'panControlOptions': {position: google.maps.ControlPosition.TOP_LEFT},
			'scaleControl': false,
			'scaleControlOptions': {position: google.maps.ControlPosition.TOP_LEFT},
			'scrollwheel': false,
			'streetViewControl': true,
			'streetViewControlOptions': {position: google.maps.ControlPosition.TOP_LEFT},
			'zoom': 5,
			'zoomControl': true,
			'zoomControlOptions': {position: google.maps.ControlPosition.TOP_LEFT},
			'zoomControlStyle': {style: google.maps.ZoomControlStyle.SMALL}
		}
//backgroundColor
		mapOptions.backgroundColor = options.backgroundColor;
//center
		mapOptions.center = new google.maps.LatLng(options.center[0], options.center[1]);
//disableDefaultUI
		mapOptions.disableDefaultUI  = options.disableDefaultUI;
//disableDoubleClickZoom
		mapOptions.disableDoubleClickZoom  = options.disableDoubleClickZoom;
//draggable
		mapOptions.draggable  = options.draggable;
//draggableCursor
		mapOptions.draggableCursor  = options.draggableCursor;
//draggingCursor
		mapOptions.draggingCursor  = options.draggingCursor;
//keyboardShortcuts
		mapOptions.keyboardShortcuts  = options.keyboardShortcuts;
//mapMaker
		mapOptions.mapMaker  = options.mapMaker;
//mapTypeControl
		if (options.mapTypeControl) {
			mapOptions.mapTypeControl = true;
			mapOptions.mapTypeControlOptions.mapTypeIds = [];
			for (var i=0; i<options.mapTypeControlTypes.length; i++) {
				if (options.mapTypeControlTypes[i] == 'hybrid') {
					mapOptions.mapTypeControlOptions.mapTypeIds[i] = google.maps.MapTypeId.HYBRID;
				}
				if (options.mapTypeControlTypes[i] == 'roadmap') {
					mapOptions.mapTypeControlOptions.mapTypeIds[i] = google.maps.MapTypeId.ROADMAP;
				}
				if (options.mapTypeControlTypes[i] == 'satellite') {
					mapOptions.mapTypeControlOptions.mapTypeIds[i] = google.maps.MapTypeId.SATELLITE;
				}
				if (options.mapTypeControlTypes[i] == 'terrain') {
					mapOptions.mapTypeControlOptions.mapTypeIds[i] = google.maps.MapTypeId.TERRAIN;
				}
			};
			switch (options.mapTypeControlPosition) {
				case "topLeft":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
					break;
				case "topCenter":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
					break;
				case "topRight":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
					break;
				case "rightTop":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
					break;
				case "rightCenter":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
					break;
				case "rightBottom":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
					break;
				case "bottomRight":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
					break;
				case "bottomCenter":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
					break;
				case "bottomLeft":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
					break;
				case "leftBottom":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
					break;
				case "leftCenter":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
					break;
				case "leftTop":
					mapOptions.mapTypeControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
					break;
			};
			switch (options.mapTypeControlStyle) {
				case "menu":
					mapOptions.mapTypeControlOptions.style = google.maps.MapTypeControlStyle.DROPDOWN_MENU;
					break;
				case "bar":
					mapOptions.mapTypeControlOptions.style = google.maps.MapTypeControlStyle.HORIZONTAL_BAR;
					break;
			};
		} else {
				mapOptions.mapTypeControl = false;
				mapOptions.mapTypeControlOptions = null;
				}
//mapTypeId
		switch (options.mapTypeId) {
			case 'hybrid':
				mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
				break;
			case 'roadmap':
				mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
				break;
			case 'satellite':
				mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
				break;
			case 'terrain':
				mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
				break;
		}
//maxZoom
		mapOptions.maxZoom = options.maxZoom;
//minZoom
		mapOptions.minZoom = options.minZoom;
//noClear
		mapOptions.noClear = options.noClear;
//panControl
  		if (options.panControl) {
			mapOptions.panControl = options.panControl;
			switch (options.panControlPosition) {
				case "topLeft":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
					break;
				case "topCenter":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
					break;
				case "topRight":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
					break;
				case "rightTop":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
					break;
				case "rightCenter":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
					break;
				case "rightBottom":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
					break;
				case "bottomRight":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
					break;
				case "bottomCenter":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
					break;
				case "bottomLeft":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
					break;
				case "leftBottom":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
					break;
				case "leftCenter":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
					break;
				case "leftTop":
					mapOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
					break;
			};
		} else {
				mapOptions.panControl = false;
				mapOptions.panControlOptions = null;
				}
//overviewMapControl
  		if (options.overviewMapControl) {
			mapOptions.overviewMapControl = options.overviewMapControl;
			mapOptions.overviewMapControlOptions = {};
			mapOptions.overviewMapControlOptions.opened = options.overviewMapControlOpened;
		} else {
				mapOptions.overviewMapControl = false;
				mapOptions.overviewMapControlOptions = null;
				}
//scaleControl
  		if (options.scaleControl) {
			mapOptions.scaleControl = options.scaleControl;
			mapOptions.scaleControlOptions = {};
			switch (options.scaleControlPosition) {
				case "topLeft":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
					break;
				case "topCenter":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
					break;
				case "topRight":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
					break;
				case "rightTop":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
					break;
				case "rightCenter":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
					break;
				case "rightBottom":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
					break;
				case "bottomRight":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
					break;
				case "bottomCenter":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
					break;
				case "bottomLeft":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
					break;
				case "leftBottom":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
					break;
				case "leftCenter":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
					break;
				case "leftTop":
					mapOptions.scaleControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
					break;
			};
		} else {
				mapOptions.scaleControl = false;
				mapOptions.scaleControlOptions = null;
				}
//scrollWheel
		mapOptions.scrollwheel = options.scrollwheel;
//streetViewControl
		if (options.streetViewControl) {
			mapOptions.streetViewControl = true;
			mapOptions.streetViewControlOptions = {};
			switch (options.streetViewControlPosition) {
				case "topLeft":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
					break;
				case "topCenter":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
					break;
				case "topRight":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
					break;
				case "rightTop":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
					break;
				case "rightCenter":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
					break;
				case "rightBottom":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
					break;
				case "bottomRight":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
					break;
				case "bottomCenter":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
					break;
				case "bottomLeft":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
					break;
				case "leftBottom":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
					break;
				case "leftCenter":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
					break;
				case "leftTop":
					mapOptions.streetViewControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
					break;
			};
		} else {
				mapOptions.streetViewControl = false;
				mapOptions.streetViewControlOptions = null;
				}
//zoom
		mapOptions.zoom = options.zoom;
//zoomControl
		if (options.zoomControl) {
			mapOptions.zoomControl = true;
			switch (options.zoomControlPosition) {
				case "topLeft":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
					break;
				case "topCenter":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
					break;
				case "topRight":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
					break;
				case "rightTop":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
					break;
				case "rightCenter":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
					break;
				case "rightBottom":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
					break;
				case "bottomRight":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
					break;
				case "bottomCenter":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
					break;
				case "bottomLeft":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
					break;
				case "leftBottom":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
					break;
				case "leftCenter":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
					break;
				case "leftTop":
					mapOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
					break;
			};
			switch (options.zoomControlStyle) {
				case "small":
					mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
					break;
				case "large":
					mapOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.LARGE;
					break;
			};
		} else {
				mapOptions.zoomControl = false;
				mapOptions.zoomControlOptions = null;
				}
//Build the map with all of the options we have set
		var thisMap = Mapifies.MapObjects.Set(element, mapOptions);
//Debug Mode		
		if (options.debugMode) 
			console.log(Mapifies);
		
		google.maps.event.addListener(thisMap, 'click', function() {
			if (typeof infowindow != 'undefined') {
				infowindow.close();
			}
		});
//Call Back		
		if (typeof callback == 'function') 
			return callback(thisMap, element, options);
/*	} else {
		jQuery(element).text('Your browser does not support Google Maps.');
		return false;
	}
*/	return;
};

/**
 * A function to move a map to a passed position
 * @method
 * @namespace Mapifies
 * @id Mapifies.MoveTo
 * @alias Mapifies.MoveTo
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Object} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the map object and options or true.
 */
Mapifies.MoveTo = function ( element, options, callback ) {
	/**
 	 * Default options for MoveTo
   * @method
   * @namespace Mapifies
   * @id Mapifies.MoveTo
   * @alias Mapifies.MoveTo
   * @param {String} centerMethod The element to initialise the map on.
   * @param {String} mapType The type of map to create.  Takes a map type constant such as G_NORMAL_MAP or null(default). (Changed r74).
   * @param {Object} mapCenter An array that contains the Lat/Lng coordinates of the map center.
   * @param {Number} mapZoom The initial zoom level of the map.
   */	
	function defaults() {
		return {
			'centerMethod': 'normal',
			'mapType': null,
			'mapCenter': [],
			'mapZoom': null
		};
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);	
	var point = new google.maps.LatLng(options.mapCenter[0], options.mapCenter[1]);
	switch (options.centerMethod) {
		case 'normal':
			thisMap.setCenter(point);
			thisMap.setZoom(options.mapZoom);
			thisMap.setMapTypeId(options.mapType);
		break;
		case 'pan':
			thisMap.panTo(point);
			thisMap.setZoom(options.mapZoom);
			thisMap.setMapTypeId(options.mapType);
		break;
	}
	if (typeof callback == 'function') return callback(point, options);
};

/**
 * Allows you to pass a google maptype constant and update the map type
 * @method
 * @namespace Mapifies
 * @id Mapifies.SetMapType
 * @alias Mapifies.SetMapType
 * @param {jQuery} element The element to initialise the map on.
 * @param {String} options The option of the maptype.
 * @param {Object} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the map object handler.
 */
Mapifies.SetMapType = function (element, options, callback) {
	var thisMap = Mapifies.MapObjects.Get(element);
	thisMap.setMapTypeId(window[options]);
	if (typeof callback == 'function') return callback(element);
	
}

/**
 * The SearchAddress function takes a map, options and callback function.  The options can contain either an address string, to which a point is returned - or reverse geocoding a GLatLng, where an address is returned
 * @method
 * @namespace Mapifies
 * @id Mapifies.SearchAddress
 * @param {jQuery} element The jQuery object containing the map element.
 * @param {Object} options An object of options
 * @param {Function} callback The callback function that returns the result
 * @return {Function} Returns a passed callback function or true if no callback specified
 */
Mapifies.SearchAddress = function( element, options, callback) {
	/**
	 * Default options for SearchAddress
	 * @method
	 * @namespace Mapifies.SearchAddress
	 * @id Mapifies.SearchAddress.defaults
	 * @alias Mapifies.SearchAddress.defaults
	 * @param {String} query The Address or GLatLng to query in the geocoder
	 * @param {String} returnType The type of value you want to return from Google.  This is mapped to the function names available, the options are 'getLatLng' which returns coordinates, and 'getLocations' which returns points.
	 * @param {GGeoCache} cache The GGeoCache to store the results in if required
	 * @param {String} countryCode The country code to localise results
	 * @return {Object} The options for SearchAddress
	 */
	function defaults() {
		return {
			// Address to search for
			'query': null,
			// Return Type
			'returnType': 'getLatLng',
			// Country code for localisation (not implemented yet)
			'countryCode': 'us'
		};
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	GeocoderRequest = {
		'address': null,
		'bounds': null,
		'location': null,
		'region': null
	}
	// Check to see if the Geocoder already exists in the object
	// or create a temporary locally scoped one.
	if (typeof thisMap.Geocoder === 'undefined') {	
		var geoCoder = new google.maps.Geocoder();
		Mapifies.MapObjects.Append(element, 'Geocoder', geoCoder);
		// We need to get the map object again, now we have attached the geocoder
		thisMap = Mapifies.MapObjects.Get(element);
	}
	GeocoderRequest.address = options.query;
	thisMap.Geocoder.geocode(GeocoderRequest, function(result, status){
		if (status == google.maps.GeocoderStatus.OK) {
			if (typeof callback === 'function') {
				return callback(result, status, options); 
			}
		} else {
				result = "Address not found by Google - Please check";
				if (typeof callback === 'function') {
					return callback(result, status, options); 
				}
				}
	});
	return;
};

/**
 * The SearchDirections function allows you to search for directions between two or more points and return it to a map and a directions panel
 * @method
 * @namespace Mapifies
 * @id Mapifies.SearchDirections
 * @param {jQuery} element The jQuery object containing the map element.
 * @param {Object} options An object of options
 * @param {Function} callback The callback function that returns the result
 * @return {Function} Returns a passed callback function or true if no callback specified
 */
Mapifies.SearchDirections = function( element, options, callback) {
	/**
	 * Default options for SearchDirections
	 * @method
	 * @namespace Mapifies.SearchDirections
	 * @id Mapifies.SearchDirections.defaults
	 * @alias Mapifies.SearchDirections.defaults
	 * @param {String} query The directions query to parse.  Must contain one 'from:' and one 'to:' query, but can contain multiple 'to:' queries.
	 * @param {String} panel The ID of the panel that the directions will be sent to.
	 * @param {String} local The local for the directions.
	 * @param {String} travelMode Allows you to specify the travel mode, either 'driving' or 'walking'.  Driving is the default.
	 * @param {Boolean} avoidHighways Allows you to avoid Highways/Motorway's on trips.  Please note this may not always be possible depending on the route.
	 * @param {Boolean} getPolyline Decides if the returned result will draw a polyline on the map on the journey.  Default is True.
	 * @param {Boolean} getSteps Decides if the textual directions are returned to the directions panel.
	 * @param {Boolean} preserveViewport Decides if the map will zoom and center in on the directions results.
	 * @param {Boolean} clearLastSearch Clears the last direction search if you do not want to have multiple points.
	 * @return {Object} The options for SearchDirections
	 */
	function defaults() {
		return {
			// Option to avoid highways
			'avoidHighways': false,
			// Option to avoid highways
			'avoidTolls': false,
			// From address
			'destination': null,
			// Get polyline
			'optimizeWaypoints': false,
			// Get directions
			'origin': null,
			// Preserve Viewport
			'provideRouteAlternatives' : false,
			//The region to use for the directions result.
			'region': 'US',
			//The mode of travel, such as driving (default) or walking
			'travelMode': google.maps.DirectionsTravelMode.DRIVING,
			// Optional panel to show text directions
			'panel': null,
			// Option to avoid highways
			'unitSystem': google.maps.UnitSystem.IMPERIAL,
			// clear last search
			'waypoints' : null
		};
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	
	var DirectionsRequest = {
		'avoidHighways': options.avoidHighways,
		'avoidTolls': options.avoidTolls,
		'destination': options.destination,
		'optimizeWaypoints': options.optimizeWaypoints,
		'origin': options.origin,
		'provideRouteAlternatives': options.provideRouteAlternatives,
		'region': options.region,
		'travelMode': options.travelMode,
		'unitSystem': options.unitSystem,
		'waypoints' : options.waypoints
	};
	
	if (typeof thisMap.Directions === 'undefined') {
  		Mapifies.MapObjects.Append(element, 'Directions', new google.maps.DirectionsService());
  		Mapifies.MapObjects.Append(element, 'DirectionsDisplay', new google.maps.DirectionsRenderer());
		// We need to get the map object again, now we have attached the directions objects
		thisMap = Mapifies.MapObjects.Get(element);
	}	
	
	thisMap.DirectionsDisplay.setMap(thisMap);
	if (options.panel) {
        thisMap.DirectionsDisplay.setPanel(document.getElementById(options.panel));
	}
	thisMap.Directions.route(DirectionsRequest, function(result, status){
		if (status == google.maps.DirectionsStatus.OK) {
			thisMap.DirectionsDisplay.setDirections(result);
			if (typeof callback === 'function') {
				return callback(result, status, options); 
			}
		}
	});
	
	return;
};

/**
 * Create an adsense ads manager for the map.  The Adsense manager will parse your page and show adverts on the map that relate to this.  Requires your adsense publisher id and channel
 * @method
 * @namespace Mapifies
 * @id Mapifies.CreateAdsManager
 * @param {jQuery} element The jQuery object containing the map element.
 * @param {Object} options An object of options
 * @param {Function} callback The callback function that returns the result
 * @return {Function} Returns a passed callback function or true if no callback specified

Mapifies.CreateAdsManager = function( element, options, callback) {
 */
	/**
	 * Default options for CreateAdsManager
	 * @method
	 * @namespace Mapifies.CreateAdsManager
	 * @id Mapifies.CreateAdsManager.defaults
	 * @alias Mapifies.CreateAdsManager.defaults
	 * @param {String} publisherId Your Adsense publisher ID
	 * @param {Number} maxAdsOnMap The maximum number of ads to show on the map at one time
	 * @param {Number} channel The AdSense channel this belongs to
	 * @param {Number} minZoomLevel The minimum zoom level to begin showing ads at
	 * @return {Object} The options for CreateAdsManager
	function defaults() {
		return {
			'publisherId':'',
			'maxAdsOnMap':3,
			'channel':0,
			'minZoomLevel':6
		}
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	
	var adsOptions = {
		'maxAdsOnMap':options.maxAdsOnMap,
		'channel':options.channel,
		'minZoomLevel':options.minZoomLevel
	}
	
	if (typeof thisMap.AdsManager == 'undefined') {
  	Mapifies.MapObjects.Append(element, 'AdsManager', new google.maps.AdsManager(thisMap, options.publisherId, adsOptions));
  }	
	
	if (typeof callback == 'function') return callback(thisMap.AdsManager, options);
};
	 */
/**
 * This function allows you to pass a GeoXML or KML feed to a Google map.
 * @method
 * @namespace Mapifies
 * @id Mapifies.AddFeed
 * @alias Mapifies.AddFeed
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Fucntion} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the feed object and options.
 */
Mapifies.AddFeed = function( element, options, callback ) {
	/**
	 * Default options for AddFeed
	 * @method
	 * @namespace Mapifies.AddFeed
	 * @id Mapifies.AddFeed.defaults
	 * @alias Mapifies.AddFeed.defaults
	 * @param {String} feedUrl The URL of the GeoXML or KML feed.
	 * @param {Object} mapCenter An array with a lat/lng position to center the map on
	 * @return {Object} The options for AddFeed
	 */
	function defaults() {
		return {
			// URL of the feed to pass (required)
			'feedUrl': null,
			// URL of the feed to pass (required)
			'blockInfoWindows': false,
			// Position to center the map on (optional)
			'mapCenter': []
		};
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);

	// Load feed
	var feed = new google.maps.KmlLayer(
		options.feedUrl,
		{
			suppressInfoWindows: options.blockInfoWindows,
			map: thisMap
		});
	
	// If the user has passed the optional mapCenter,
	// then center the map on that point
	if (options.mapCenter[0] && options.mapCenter[1])
		thisMap.setCenter(new google.maps.LatLng(options.mapCenter[0], options.mapCenter[1]));
		
	if (typeof callback == 'function') return callback( feed, options );
	return;
};

/**
 * This function allows you to remove a GeoXML or KML feed from a Google map.
 * @method
 * @namespace Mapifies
 * @id Mapifies.RemoveFeed
 * @alias Mapifies.RemoveFeed
 * @param {jQuery} element The element to initialise the map on.
 * @param {GGeoXML} feed The feed to remove from the map
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the feed object and options.
 */
Mapifies.RemoveFeed = function ( element, feed, callback ) {
	feed.setMap(null);
	if (typeof callback == 'function') return callback( feed );
	return;
};

/**
 * This function allows you to add markers to the map with several options
 * @method
 * @namespace Mapifies
 * @id Mapifies.AddMarker
 * @alias Mapifies.AddMarker
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the marker object and options.
 */
Mapifies.AddMarker = function ( element, options, callback ) {
	/**
	 * Default options for AddGroundOverlay
	 * @method
	 * @namespace Mapifies.AddGroundOverlay
	 * @id Mapifies.AddGroundOverlay.defaults
	 * @alias Mapifies.AddGroundOverlay.defaults
	 * @param {Object} pointLatLng The Lat/Lng coordinates of the marker.
	 * @param {String} pointHTML The HTML to appear in the markers info window.
	 * @param {String} pointOpenHTMLEvent The javascript event type to open the marker info window.  Default is 'click'.
	 * @param {Boolean} pointIsDraggable Defines if the point is draggable by the end user.  Default false.
	 * @param {Boolean} pointIsRemovable Defines if the point can be removed by the user.  Default false.
	 * @param {Boolean} pointRemoveEvent The event type to remove a marker.  Default 'dblclick'.
	 * @param {Number} pointMinZoom The minimum zoom level to display the marker if using a marker manager.
	 * @param {Number} pointMaxZoom The maximum zoom level to display the marker if using a marker manager.
	 * @param {GIcon} pointIcon A GIcon to display instead of the standard marker graphic.
	 * @param {Boolean} centerMap Automatically center the map on the new marker.  Default false.
	 * @param {String} centerMoveMethod The method in which to move to the marker.  Options are 'normal' (default) and 'pan'.  Added r64
	 * @return {Object} The options for AddGroundOverlay
	 */
	function defaults() {
		var values = {
			'pointAnimation': null,
			'pointClickable': true,
			'pointCursor': 'pointer',
			'pointIsDraggable': false,
			'pointIsFlat': false,
			'pointIcon': null,
			'pointIsOptimized': false,
			'pointLatLng': null,
			'pointRaiseOnDrag': false,
			'pointShadow': null,
			'pointShape': null,
			'pointTitle': null,
			'pointIsVisible': true,
			'zIndex': null,
			'pointHTML': null,
			'pointOpenHTMLEvent': null,
			'disableAutoPan': false,
			'maxWidth': null,
			'offsetWidth': null,
			'offsetHeight': null,
			'offsetUnit': null,
			'centerMap': false,
			'centerMoveMethod':'normal',
			'pointMinZoom': 4,
			'pointMaxZoom': 17
		};
		return values;
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend({}, defaults(), options);
	
	var markerOptions = {
		'animation': null,
		'clickable': true,
		'cursor': 'pointer',
		'draggable': false,
		'flat': false,
		'icon': null,
		'map': null,
		'optimized': true,
		'position': new google.maps.LatLng(38.898748, -77.037684),
		'raiseOnDrag': true,
		'shadow': null,
		'shape': null,
		'title': 'Click for Info',
		'visible': true,
		'zIndex': null
		}
	var infoWindowOptions = {
		'content': null,
		'disableAutoPan': false,
		'maxWidth': '200px',
		'pixelOffset': null,
		'position': markerOptions.position,
		'zIndex': null
		}
	
	if (options.pointAnimation && (options.pointAnimation == 'bounce' || options.pointAnimation == 'drop')) {
		if (options.pointAnimation == 'bounce') {
			markerOptions.animation = google.maps.Animation.BOUNCE;
		} else {
				markerOptions.animation = google.maps.Animation.DROP;
				}
	}
	if (options.pointClickable == false) {
		markerOptions.clickable = false;
	}
	if (options.pointCursor) {
		markerOptions.cusor = options.pointCursor;
	}
	if (options.pointIsDraggable) {
		markerOptions.draggable = options.pointIsDraggable;
	}
	if (options.pointIsFlat) {
		markerOptions.flat = options.pointIsFlat;
	}
	
	if (typeof options.pointIcon == 'object')
		jQuery.extend(markerOptions, {'icon': options.pointIcon});
		
	if (options.pointIsOptimized) {
		markerOptions.optimized = options.pointIsOptimized;
	}
	if (options.pointLatLng) {
		markerOptions.position = new google.maps.LatLng(options.pointLatLng[0],options.pointLatLng[1]);
	}
	if (options.pointRaiseOnDrag) {
		markerOptions.raiseOnDrag = options.pointRaiseOnDrag;
	}
	
	if (typeof options.pointShadow == 'object')
		jQuery.extend(markerOptions, {'shadow': options.pointShadow});
			
	if (typeof options.pointShape == 'object')
		jQuery.extend(markerOptions, {'shape': options.pointShape});
		
	if (options.pointTitle) {
		markerOptions.title = options.pointTitle;
	}
	if (options.pointIsVisible) {
		markerOptions.draggable = options.pointIsDraggable;
	}
	if (options.zIndex) {
		markerOptions.zIndex = options.zIndex;
		infoWindowOptions.zIndex = options.zIndex;
	}

//	console.log('zIndexProcess = ' + options.zIndexProcess);
	if (options.zIndexProcess) {
		jQuery.extend(markerOptions, {'zIndexProcess': function( m ) {return options.zIndexProcess}});		
	}
		
	// Create marker
	var marker = new google.maps.Marker(markerOptions);
	// Add marker to map's marker array
	var mapName = jQuery(element).attr('id');
	Mapifies.MarkerArrays[mapName].markers.push(marker); 

	// If it has HTML to pass in, add an event listner for a click
	if(options.pointHTML) {
		infoWindowOptions.content = options.pointHTML;
	}
	if (options.disableAutoPan == true) {
		infoWindowOptions.disableAutoPan = options.disableAutoPan;
	}
	if (options.maxWidth) {
		infoWindowOptions.maxWidth = options.maxWidth;
	}
	if (options.offsetWidth && options.offsetHeight && options.offsetUnits) {
		infoWindowOptions.pixelOffset = new google.maps.Size({'width':options.offsetWidth, 'height':options.offsetHeight, 'widthUnit':options.offsetUnits, 'heightUnit':options.offsetUnits});
	}
	marker.infoWindowOptions = infoWindowOptions;
	// Create infowindow
		
// Add event listener to open the infowindow
	eventType = 'click';
	if (options.pointOpenHTMLEvent) {
		eventType = options.pointOpenHTMLEvent;
	}
    google.maps.event.addListener(marker, eventType, function() {
		if (typeof infowindow != 'undefined') {
			infowindow.close();
		}
		infowindow = new google.maps.InfoWindow(marker.infoWindowOptions);
		infowindow.open(thisMap,marker);
    });
    google.maps.event.addListener(marker, 'mouseover', function() {
		marker.setAnimation(null);
    });
	
	// If the marker manager exists, add it
//	if(thisMap.MarkerManager) {
//		google.maps.event.addListener(thisMap.MarkerManager, 'loaded', function() {
//			thisMap.MarkerManager.addMarker(marker, options.pointMinZoom, options.pointMaxZoom);	
//		});
//	} else {
	marker.setMap(thisMap);
//			}
		
	// If the marker clusterer exists, add it
	if(thisMap.MarkerClusterer) {
		thisMap.MarkerClusterer.addMarker(marker);	
	}
		
	if (options.centerMap) {
		switch (options.centerMoveMethod) {
			case 'normal':
				thisMap.setCenter(new google.maps.LatLng(options.pointLatLng[0],options.pointLatLng[1]));
			break;
			case 'pan':
				thisMap.panTo(new google.maps.LatLng(options.pointLatLng[0],options.pointLatLng[1]));
			break;
		}
	}
	
	if (typeof callback == 'function') return callback(marker, options);
	return;
};


/**
 * This function allows you to remove markers from the map
 * @method
 * @namespace Mapifies
 * @id Mapifies.RemoveMarker
 * @alias Mapifies.RemoveMarker
 * @param {jQuery} element The element to initialise the map on.
 * @param {GMarker} options The marker to be removed
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the marker object.
 */
Mapifies.RemoveMarker = function ( element, marker, callback ) {
	marker.setMap(null);
	if (typeof callback === 'function') return callback(marker);
	return;
};

/**
 * This function removes all markers from the map
 * @method
 * @namespace Mapifies
 * @id Mapifies.RemoveMarker
 * @alias Mapifies.RemoveMarker
 * @param {jQuery} element The element to initialise the map on.
 * @param {GMarker} options The marker to be removed
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the marker object.
 */
Mapifies.RemoveMarkers = function ( element, callback ) {
	// Get map's marker array
	mapName = jQuery(element).attr('id');
	jQuery.each(Mapifies.MarkerArrays[mapName].markers, function(a,marker) {
		marker.setMap(null);
		marker = null;
	});
	if (typeof callback === 'function') return callback();
	return;
};

/**
 * This function allows you to create a marker manager to store and manage any markers created on the map.  Google recommends not using this marker manager and instead using the open source one.
 * @method
 * @deprecated
 * @namespace Mapifies
 * @id Mapifies.CreateMarkerManager
 * @alias Mapifies.CreateMarkerManager
 * @param {jQuery} element The element to initialise the map on.
 * @param {GMarker} options The marker to be removed
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the marker object and options.
Mapifies.CreateMarkerManager = function(element, options, callback) {
 */
	/**
	 * Default options for CreateMarkerManager
	 * @method
	 * @namespace Mapifies.CreateMarkerManager
	 * @id Mapifies.CreateMarkerManager.defaults
	 * @alias Mapifies.CreateMarkerManager.defaults
	 * @param {String} markerManager The type of marker manager to use.  Option is 'MarkerManager'.
	 * @param {Number} borderPadding Specifies, in pixels, the extra padding outside the map's current viewport monitored by a manager. Markers that fall within this padding are added to the map, even if they are not fully visible.
	 * @param {Number} maxZoom The maximum zoom level to show markers at
	 * @param {Boolean} trackMarkers Indicates whether or not a marker manager should track markers' movements.
	 * @return {Object} The options for CreateMarkerManager
	function defaults() {
		return {
			'markerManager': 'MarkerManager',
			// Border Padding in pixels
			'borderPadding': 100,
			// Max zoom level 
			'maxZoom': 17,
			// Track markers
			'trackMarkers': false
		}
	}
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	
	var markerManagerOptions = {
		'borderPadding': options.borderPadding,
		'maxZoom': options.maxZoom,
		'trackMarkers': options.trackMarkers
	}
	
	var markerManager = new window[options.markerManager](thisMap, markerManagerOptions);
	Mapifies.MapObjects.Append(element, 'MarkerManager',markerManager);

	// Return the callback
	if (typeof callback == 'function') return callback( markerManager, options );
};
	 */

/**
 * This function allows you to create a marker clusterer to cluster any markers created on the map.
 * @method
 * @namespace Mapifies
 * @id Mapifies.CreateMarkerClusterer
 * @alias Mapifies.CreateMarkerClusterer
 * @param {jQuery} element The element to initialise the map on.
 * @param {GMarker} options The marker to be removed
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the marker object and options.
 */
Mapifies.CreateMarkerClusterer = function(element, options, callback) {
	/**
	 * Default options for CreateMarkerClusterer
	 * @method
	 * @namespace Mapifies.CreateMarkerManager
	 * @id Mapifies.CreateMarkerManager.defaults
	 * @alias Mapifies.CreateMarkerManager.defaults
	 * @param {String} markerManager The type of marker manager to use.  Options are 'GMarkerManager' (default) and 'MarkerManager'.  (Added r72)
	 * @param {Number} borderPadding Specifies, in pixels, the extra padding outside the map's current viewport monitored by a manager. Markers that fall within this padding are added to the map, even if they are not fully visible.
	 * @param {Number} maxZoom The maximum zoom level to show markers at
	 * @param {Boolean} trackMarkers Indicates whether or not a marker manager should track markers' movements.
	 * @return {Object} The options for CreateMarkerManager
	 */

	function defaults() {
		return {
			'gridSize': 60,
			'minimumClusterSize': 2,
			'maxZoom': null,
			'styles': [],
			'title': "",
			'zoomOnClick': true,
			'averageCenter': false,
			'ignoreHidden': false,
			'printable': false,
			'imagePath': null,
			'imageExtension': null,
			'imageSizes': null,
			'calculator': null,
			'batchSizeIE': null
		}
	}
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	
	var markerClusterer = new MarkerClusterer(thisMap, null, options);
	Mapifies.MapObjects.Append(element, 'MarkerClusterer',markerClusterer);

	// Return the callback
	if (typeof callback == 'function') return callback( markerClusterer, options );
};

/**
 * This function allows you to add a Google StreetView
 * @method
 * @namespace Mapifies
 * @id Mapifies.CreateStreetViewPanorama
 * @alias Mapifies.CreateStreetViewPanorama
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the street view.
 */
Mapifies.CreateStreetViewPanorama = function( element, options, callback ) {
	/**
	 * Default options for CreateStreetViewPanorama
	 * @method
	 * @namespace Mapifies.CreateStreetViewPanorama
	 * @id Mapifies.CreateStreetViewPanorama.defaults
	 * @alias Mapifies.CreateStreetViewPanorama.defaults
	 * @param {String} overideContainer A ID of a div to put the street view into, otherwise it will default to the map.
	 * @param {Object} latlng The starting Lat/Lng of the streetview - this is required.
	 * @param {Object} pov The point of view to initialse the map on.  This is 3 values, X/Y/Z
	 * @return {Object} The options for CreateStreetViewPanorama
	 */
	function defaults() {
		return {
			'addressControl': true,
			'addressControlPosition': 'topRight',
			'disableDoubleClickZoom': true,
			'enableCloseButton': true,
			'linksControl': false,
			'panControl': true,
			'panControlPosition': 'topLeft',
			'pano': null,
			'panoProvider': null,
			'overideContainer':'',
			'latlng': [38.898748, -77.037684],
			'povControl': false,
			'povHeading': 0,
			'povPitch': 0,
			'povZoom': 1,
			'scrollwheel': true,
			'visible': true,
			'zoomControl': true,
			'zoomControlPosition': 'topLeft',
			'zoomControlStyle': 'small'
		}
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);
	// Create Street View Overlay
	var streetViewOptions = {
		'addressControl': true,
		'addressControlOptions': {position: google.maps.ControlPosition.TOP_RIGHT},
		'disableDoubleClickZoom': true,
		'enableCloseButton': false,
		'linksControl': false,
		'panControl': true,
		'panControlOptions': {position: google.maps.ControlPosition.TOP_LEFT},
		'pano': null,
		'panoProvider': null,
		'position': (google.maps) ? new google.maps.LatLng(38.898748, -77.037684) : null,
		'pov': {heading: 0, pitch: 0, zoom:1},
		'scrollwheel': true,
		'visible': true,
		'zoomControl': true,
		'zoomControlOptions': {position: google.maps.ControlPosition.TOP_LEFT, style: google.maps.ZoomControlStyle.SMALL}
		};
	var container = null;
	if (options.overideContainer !== '') {
		container = jQuery(options.overideContainer).get(0);
	} else {
			container = jQuery(element).get(0);
			}

//addressControl
	if (options.addressControl) {
		streetViewOptions.addressControl = options.addressControl;
		switch (options.addressControlPosition) {
			case "topLeft":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
				break;
			case "topCenter":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
				break;
			case "topRight":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
				break;
			case "rightTop":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
				break;
			case "rightCenter":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
				break;
			case "rightBottom":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
				break;
			case "bottomRight":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
				break;
			case "bottomCenter":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
				break;
			case "bottomLeft":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
				break;
			case "leftBottom":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
				break;
			case "leftCenter":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
				break;
			case "leftTop":
				streetViewOptions.addressControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
				break;
		}
	} else {
			streetViewOptions.addressControl = false;
			streetViewOptions.addressControlOptions = null;
			}
//Disable Double Click Zoom
	if (!options.disableDoubleClickZoom) {
		streetViewOptions.disableDoubleClickZoom = false;
	}
//Enable Close Button
	if (options.enableCloseButton) {
		streetViewOptions.enableCloseButton = true;
	}
//Enable Link Control
	if (options.linksControl) {
		streetViewOptions.linksControl = true;
	}
//panControl
	if (options.panControl) {
		streetViewOptions.panControl = options.panControl;
		switch (options.panControlPosition) {
			case "topLeft":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
				break;
			case "topCenter":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
				break;
			case "topRight":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
				break;
			case "rightTop":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
				break;
			case "rightCenter":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
				break;
			case "rightBottom":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
				break;
			case "bottomRight":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
				break;
			case "bottomCenter":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
				break;
			case "bottomLeft":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
				break;
			case "leftBottom":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
				break;
			case "leftCenter":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
				break;
			case "leftTop":
				streetViewOptions.panControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
				break;
		}
	} else {
			streetViewOptions.panControl = false;
			streetViewOptions.panControlOptions = null;
			}
//pano
//panoProvider
//position
	if (options.latlng) {
		streetViewOptions.position = new google.maps.LatLng(options.latlng[0],options.latlng[1]);
	}
//pov
	if (options.povControl) {
		streetViewOptions.pov.heading = options.povHeading;
		streetViewOptions.pov.pitch = options.povPitch;
		streetViewOptions.pov.zoom = options.povZoom;
	}
//Disable Scrollwheel
	if (!options.scrollwheel) {
		streetViewOptions.disableDoubleClickZoom = false;
	}
//Disable Double Click Zoom
	if (!options.disableDoubleClickZoom) {
		streetViewOptions.disableDoubleClickZoom = false;
	}
//zoomControl
	if (options.zoomControl) {
		streetViewOptions.zoomControl = options.zoomControl;
		switch (options.zoomControlPosition) {
			case "topLeft":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_LEFT;
				break;
			case "topCenter":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_CENTER;
				break;
			case "topRight":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.TOP_RIGHT;
				break;
			case "rightTop":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_TOP;
				break;
			case "rightCenter":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_CENTER;
				break;
			case "rightBottom":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.RIGHT_BOTTOM;
				break;
			case "bottomRight":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_RIGHT;
				break;
			case "bottomCenter":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_CENTER;
				break;
			case "bottomLeft":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
				break;
			case "leftBottom":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
				break;
			case "leftCenter":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_CENTER;
				break;
			case "leftTop":
				streetViewOptions.zoomControlOptions.position = google.maps.ControlPosition.LEFT_TOP;
				break;
		}
		switch (options.zoomControlStyle) {
			case "small":
				streetViewOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.SMALL;
				break;
			case "large":
				streetViewOptions.zoomControlOptions.style = google.maps.ZoomControlStyle.LARGE;
				break;
		}
	} else {
			streetViewOptions.zoomControl = false;
			streetViewOptions.zoomControlOptions = null;
			}
	
	
	var streetView = new google.maps.StreetViewPanorama(container, streetViewOptions);
	if (typeof callback == 'function') return callback(streetView, options);
	return;
};

/**
 * This function allows you to add a Google Traffic Layer
 * @method
 * @namespace Mapifies
 * @id Mapifies.AddTrafficInfo
 * @alias Mapifies.AddTrafficInfo
 * @param {jQuery} element The element to initialise the map on.
 * @param {Object} options The object that contains the options.
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the traffic layer.
 */
Mapifies.AddTrafficInfo = function( element, options, callback) {
	/**
	 * Default options for AddTrafficInfo
	 * @method
	 * @namespace Mapifies.AddTrafficInfo
	 * @id Mapifies.AddTrafficInfo.defaults
	 * @alias Mapifies.AddTrafficInfo.defaults
	 * @param {Object} mapCenter The Lat/Lng to center the map on
	 * @return {Object} The options for AddTrafficInfo
	 */
	function defaults() {
		return {
			// Center the map on this point (optional)
			'mapCenter': []
		};
	};
	var thisMap = Mapifies.MapObjects.Get(element);
	options = jQuery.extend(defaults(), options);

	// If the user has passed the optional mapCenter,
	// then center the map on that point
	if (options.mapCenter[0] && options.mapCenter[1]) {
		thisMap.setCenter(new google.maps.LatLng(options.mapCenter[0], options.mapCenter[1]));
	}
	var trafficLayer = new google.maps.TrafficLayer;
	// Set map for traffic layer
	trafficLayer.setMap(thisMap);
	if (typeof callback == 'function') return callback(trafficLayer, options);
};

/**
 * This function allows you to remove a traffic layer from the map
 * @method
 * @namespace Mapifies
 * @id Mapifies.RemoveTrafficInfo
 * @alias Mapifies.RemoveTrafficInfo
 * @param {jQuery} element The element to initialise the map on.
 * @param {GTrafficOverlay} trafficOverlay The traffic overlay to be removed
 * @param {Function} callback The callback function to pass out after initialising the map.
 * @return {Function} callback The callback option with the traffic overlay.
 */
Mapifies.RemoveTrafficInfo = function ( element, trafficLayer, callback ) {
	var thisMap = Mapifies.MapObjects.Get(element);
	trafficLayer.setMap(null);
	if (typeof callback === 'function') return callback(trafficLayer);
	return;
};

/**
 * A helper method that allows you to pass the status code of a search and get back a friendly oject
 * @method
 * @namespace Mapifies
 * @id Mapifies.SearchCode
 * @param {Number} code The status code of the query
 * @return {Object} Returns a friendly object that contains the 'code', a 'success' boolean and a helpful 'message'.
 */
Mapifies.SearchCode = function ( code ) {
	switch (code) {
		case google.maps.GeocoderStatus.OK:
			return {'code':google.maps.GeocoderStatus.OK,'success':true,'message':'Success'};
		case google.maps.GeocoderStatus.ERROR:
			return {'code' : google.maps.GeocoderStatus.ERROR, 'success' : false, 'message' : 'There was a problem contacting the Google servers.'};
			break;
		case google.maps.GeocoderStatus.INVALID_REQUEST:
			return {'code' : google.maps.GeocoderStatus.INVALID_REQUEST, 'success' : false, 'message' : 'The GeocoderRequest was invalid.'};
			break;
		case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
			return {'code' : google.maps.GeocoderStatus.OVER_QUERY_LIMIT, 'success' : false, 'message' : 'A geocoding or directions request could not be successfully processed because of processing limits being exceeded.'};
			break;
		case google.maps.GeocoderStatus.REQUEST_DENIED:
			return {'code' : google.maps.GeocoderStatus.REQUEST_DENIED, 'success' : false, 'message' : 'The webpage is not allowed to use the geocoder.'};
			break;
		case google.maps.GeocoderStatus.UNKNOWN_ERROR:
			return {'code' : google.maps.GeocoderStatus.UNKNOWN_ERROR, 'success' : false, 'message' : 'A geocoding request could not be processed due to a server error. The request may succeed if you try again.'};
			break;
		case google.maps.GeocoderStatus.ZERO_RESULTS:
			return {'code' : google.maps.GeocoderStatus.ZERO_RESULTS, 'success' : false, 'message' : 'No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect'};
			break;
		default:
			return {
				'code': null,
				'success': false,
				'message': 'An unknown error occurred.'
			};
		break;
	};
}

/**
 * A helper function to create a google GIcon
 * @method
 * @namespace Mapifies
 * @id Mapifies.createIcon
 * @alias Mapifies.createIcon
 * @param {Object} options The options to create the icon
 * @return {GIcon} A GIcon object
 */
Mapifies.createIcon = function (options) {
	/**
	 * Default options for createIcon
	 * @method
	 * @namespace Mapifies.createIcon
	 * @id Mapifies.createIcon.defaults
	 * @alias Mapifies.createIcon.defaults
	 * @param {String} iconImage The foreground image URL of the icon.
	 * @param {String} iconShadow The shadow image URL of the icon.
	 * @param {GSize} iconSize The pixel size of the foreground image of the icon.
	 * @param {GSize} iconShadowSize The pixel size of the shadow image.
	 * @param {GPoint} iconAnchor The pixel coordinate relative to the top left corner of the icon image at which this icon is anchored to the map.
	 * @param {GPoint} iconInfoWindowAnchor The pixel coordinate relative to the top left corner of the icon image at which the info window is anchored to this icon.
	 * @param {String} iconPrintImage The URL of the foreground icon image used for printed maps. It must be the same size as the main icon image given by image.
	 * @param {String} iconMozPrintImage The URL of the foreground icon image used for printed maps in Firefox/Mozilla. It must be the same size as the main icon image given by image.
	 * @param {String} iconPrintShadow The URL of the shadow image used for printed maps. It should be a GIF image since most browsers cannot print PNG images.
	 * @param {String} iconTransparent The URL of a virtually transparent version of the foreground icon image used to capture click events in Internet Explorer. This image should be a 24-bit PNG version of the main icon image with 1% opacity, but the same shape and size as the main icon.
	 * @return {Object} The options for createIcon
	 */
	function defaults() {
		return {
			'iconType': 'MapIconMaker',
			'iconImage': null,
			'iconShadow': null,
			'iconSize': null,
			'iconShadowSize': null,
			'iconAnchor': null,
			'iconInfoWindowAnchor': null,
			'iconPrintImage': null,
			'iconMozPrintImage': null,
			'iconPrintShadow': null,
			'iconTransparent': null
		};
	};
	
	options = jQuery.extend(defaults(), options);
	var icon = new google.maps.Icon(G_DEFAULT_ICON);
		
	if(options.iconImage)
		icon.image = options.iconImage;
	if(options.iconShadow)
		icon.shadow = options.iconShadow;
	if(options.iconSize)
		icon.iconSize = options.iconSize;
	if(options.iconShadowSize)
		icon.shadowSize = options.iconShadowSize;
	if(options.iconAnchor)
		icon.iconAnchor = options.iconAnchor;
	if(options.iconInfoWindowAnchor)
		icon.infoWindowAnchor = options.iconInfoWindowAnchor;
	return icon;
};

/**
 * A helper function to get the map center as a google.maps.LatLng
 * @method
 * @namespace Mapifies
 * @id Mapifies.getCenter
 * @alias Mapifies.getCenter
 * @param {jQuery} element The element that contains the map.
 * @return {GLatLng} A object containing the center of the map
 */
Mapifies.getCenter = function ( element ) {
	var thisMap = Mapifies.MapObjects.Get(element);
	return thisMap.getCenter();
};

/**
 * A helper function to get the bounds of the map
 * @method
 * @namespace Mapifies
 * @id Mapifies.getBounds
 * @alias Mapifies.getBounds
 * @param {jQuery} element The element that contains the map.
 * @return {GSize} The bounds of the map
 */
Mapifies.getBounds = function (element){
	var thisMap = Mapifies.MapObjects.Get(element);
	return thisMap.getBounds();
};

/**
 * A helper function to set the bounds of the map to fit a given bounds
 * @method
 * @namespace Mapifies
 * @id Mapifies.fitBounds
 * @alias Mapifies.fitBounds
 * @param {jQuery} element The element that contains the map.
 * @return none
 */
Mapifies.fitBounds = function (element, LatLngBounds){
	var thisMap = Mapifies.MapObjects.Get(element);
	return thisMap.fitBounds(LatLngBponds);
};

var Mapifies;

if (!Mapifies) Mapifies = {};

(function($){
	$.fn.jmap = function(method, options, callback) {
		return this.each(function(){
			if (method == 'init' && typeof options == 'undefined') {
				new Mapifies.Initialise(this, {}, null);
			} else if (method == 'init' && typeof options == 'object') {
				new Mapifies.Initialise(this, options, callback);
			} else if (method == 'init' && typeof options == 'function') {
				new Mapifies.Initialise(this, {}, options);
			} else if (typeof method == 'object' || method == null) {
				new Mapifies.Initialise(this, method, options);
			} else {
				try {
					new Mapifies[method](this, options, callback);
				} catch(err) {
					throw Error('Mapifies Function Does Not Exist');
				}
			}
		});
	}
})(jQuery);
