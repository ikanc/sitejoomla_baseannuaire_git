/**
 * @name MapIconMaker
 * @version 1.1
 * @author Pamela Fox
 * @copyright (c) 2008 Pamela Fox
 * @fileoverview This gives you static functions for creating dynamically
 *     sized and colored marker icons using the Charts API marker output.
 */

/*
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License. 
 */

/**
 * @name MarkerIconOptions
 * @class This class represents optional arguments to {@link createMarkerIcon}, 
 *     {@link createFlatIcon}, or {@link createLabeledMarkerIcon}. Each of the
 *     functions use a subset of these arguments. See the function descriptions
 *     for the list of supported options.
 * @property {Number} [width=32] Specifies, in pixels, the width of the icon.
 *     The width may include some blank space on the side, depending on the
 *     height of the icon, as the icon will scale its shape proportionately.
 * @property {Number} [height=32] Specifies, in pixels, the height of the icon.
 * @property {String} [primaryColor="#ff0000"] Specifies, as a hexadecimal
 *     string, the color used for the majority of the icon body.
 * @property {String} [cornerColor="#ffffff"] Specifies, as a hexadecimal
 *     string, the color used for the top corner of the icon. If you'd like the
 *     icon to have a consistent color, make the this the same as the
 *     {@link primaryColor}.
 * @property {String} [strokeColor="#000000"] Specifies, as a hexadecimal
 *     string, the color used for the outside line (stroke) of the icon.
 * @property {String} [shadowColor="#000000"] Specifies, as a hexadecimal
 *     string, the color used for the shadow of the icon. 
 * @property {String} [label=""] Specifies a character or string to display
 *     inside the body of the icon. Generally, one or two characters looks best.
 * @property {String} [labelColor="#000000"] Specifies, as a hexadecimal 
 *     string, the color used for the label text.
 * @property {Number} [labelSize=0] Specifies, in pixels, the size of the label
 *     text. If set to 0, the text auto-sizes to fit the icon body.
 * @property {String} [shape="circle"] Specifies shape of the icon. Current
 *     options are "circle" for a circle or "roundrect" for a rounded rectangle.
 * @property {Boolean} [addStar = false] Specifies whether to add a star to the
 *     edge of the icon.
 * @property {String} [starPrimaryColor="#FFFF00"] Specifies, as a hexadecimal
 *     string, the color used for the star body.
 * @property {String} [starStrokeColor="#0000FF"] Specifies, as a hexadecimal
 *     string, the color used for the outside line (stroke) of the star.
 */

/**
 * This namespace contains functions that you can use to easily create
 *     dynamically sized, colored, and labeled icons.
 * @namespace
 */
var MapIconMaker = {};

/**
 * Creates an icon based on the specified options in the 
 *   {@link MarkerIconOptions} argument.
 *   Supported options are: width, height, primaryColor, 
 *   strokeColor, and cornerColor.
 * @param {MarkerIconOptions} [opts]
 * @return {GIcon}
 */
MapIconMaker.createMarkerIcon = function (opts) {
	var width = opts.width || 32;
	var height = opts.height || 32;
	var primaryColor = opts.primaryColor || "#ff0000";
	var strokeColor = opts.strokeColor || "#000000";
	var cornerColor = opts.cornerColor || "#ffffff";
	
	var baseUrl = "http://chart.apis.google.com/chart?cht=mm";
	var iconUrl = baseUrl + "&chs=" + width + "x" + height + 
		"&chco=" + cornerColor.replace("#", "") + "," + 
		primaryColor.replace("#", "") + "," + 
		strokeColor.replace("#", "") + "&ext=.png";
	var icon = {};
	icon.image = iconUrl;
	icon.iconSize = new google.maps.Size(width, height);
	icon.iconAnchor = new google.maps.Point(width / 2, height);
	icon.infoWindowAnchor = new google.maps.Point(width / 2, Math.floor(height / 12));
	icon.origin = new google.maps.Point(0,0);  
	var shadowUrl = "http://chart.apis.google.com/chart?chst=d_map_pin_shadow";
	icon.shadowImage = shadowUrl;
	icon.shadowSize = new google.maps.Size(Math.floor(width * 1.6), height);
	
	icon.imageMap = [
		width / 2, height,
		(7 / 16) * width, (5 / 8) * height,
		(5 / 16) * width, (7 / 16) * height,
		(7 / 32) * width, (5 / 16) * height,
		(5 / 16) * width, (1 / 8) * height,
		(1 / 2) * width, 0,
		(11 / 16) * width, (1 / 8) * height,
		(25 / 32) * width, (5 / 16) * height,
		(11 / 16) * width, (7 / 16) * height,
		(9 / 16) * width, (5 / 8) * height
	];
	for (var i = 0; i < icon.imageMap.length; i++) {
		icon.imageMap[i] = parseInt(icon.imageMap[i]);
	}
	icon.markerImage = new google.maps.MarkerImage(icon.image, icon.iconSize, icon.origin, icon.iconAnchor);
	icon.shadowOrigin = new google.maps.Point(0, 0);
	icon.shadowAnchor = new google.maps.Point(icon.iconSize.width/2, icon.shadowSize.height);
	icon.markerShadow = new google.maps.MarkerImage(icon.shadowImage, icon.shadowSize, icon.shadowOrigin, icon.shadowAnchor);
	icon.shape = {};
	icon.shape.coords = icon.imageMap;
	icon.shape.type = 'poly';
	
	return icon;
};


/**
 * Creates a flat icon based on the specified options in the 
 *     {@link MarkerIconOptions} argument.
 *     Supported options are: width, height, primaryColor,
 *     shadowColor, label, labelColor, labelSize, and shape..
 * @param {MarkerIconOptions} [opts]
 * @return {GIcon}
 */
MapIconMaker.createFlatIcon = function (opts) {
	var width = opts.width || 32;
	var height = opts.height || 32;
	var primaryColor = opts.primaryColor || "#ff0000";
	var shadowColor = opts.shadowColor || "#000000";
	var label = MapIconMaker.escapeUserText_(opts.label) || "";
	var labelColor = opts.labelColor || "#000000";
	var labelSize = opts.labelSize || 0;
	var shape = opts.shape ||  "circle";
	var shapeCode = (shape === "circle") ? "it" : "itr";
	
	var baseUrl = "http://chart.apis.google.com/chart?cht=" + shapeCode;
	var iconUrl = baseUrl + "&chs=" + width + "x" + height + 
		"&chco=" + primaryColor.replace("#", "") + "," + 
		shadowColor.replace("#", "") + "ff,ffffff01" +
		"&chl=" + label + "&chx=" + labelColor.replace("#", "") + 
		"," + labelSize;
	var icon = {};
	icon.image = iconUrl + "&chf=bg,s,00000000" + "&ext=.png";
	icon.iconSize = new google.maps.Size(width, height);
	icon.iconAnchor = new google.maps.Point(width / 2, height / 2);
	icon.infoWindowAnchor = new google.maps.Point(width / 2, height / 2);
	icon.imageMap = []; 
	if (shapeCode === "itr") {
		icon.imageMap = [0, 0, width, 0, width, height, 0, height];
	} else {
			var polyNumSides = 8;
			var polySideLength = 360 / polyNumSides;
			var polyRadius = Math.min(width, height) / 2;
			for (var a = 0; a < (polyNumSides + 1); a++) {
				var aRad = polySideLength * a * (Math.PI / 180);
				var pixelX = polyRadius + polyRadius * Math.cos(aRad);
				var pixelY = polyRadius + polyRadius * Math.sin(aRad);
				icon.imageMap.push(parseInt(pixelX), parseInt(pixelY));
			}
			}
	icon.origin = new google.maps.Point(0, 0);
	icon.markerImage = new google.maps.MarkerImage(icon.image, icon.iconSize, icon.origin, icon.iconAnchor);
	icon.shadowSize = new google.maps.Size(Math.floor(width * 1.6), height);
	icon.shadowOrigin = new google.maps.Point(0, 0);
	icon.shadowAnchor = icon.iconAnchor;
	icon.markerShadow = new google.maps.MarkerImage(icon.shadowImage, icon.shadowSize, icon.shadowOrigin, icon.shadowAnchor);
	icon.shape = {};
	icon.shape.coords = icon.imageMap;
	icon.shape.type = 'poly';
	
	return icon;
};


/**
 * Creates a labeled marker icon based on the specified options in the 
 *     {@link MarkerIconOptions} argument.
 *     Supported options are: primaryColor, strokeColor, 
 *     starPrimaryColor, starStrokeColor, label, labelColor, and addStar.
 * @param {MarkerIconOptions} [opts]
 * @return {GIcon}
 */
MapIconMaker.createLabeledMarkerIcon = function (opts) {
	var primaryColor = opts.primaryColor || "#DA7187";
	var strokeColor = opts.strokeColor || "#000000";
	var starPrimaryColor = opts.starPrimaryColor || "#FFFF00";
	var starStrokeColor = opts.starStrokeColor || "#0000FF";
	var label = MapIconMaker.escapeUserText_(opts.label) || "";
	var labelColor = opts.labelColor || "#000000";
	var addStar = opts.addStar || false;
	
	var pinProgram = (addStar) ? "pin_star" : "pin";
	var baseUrl = "http://chart.apis.google.com/chart?cht=d&chdp=mapsapi&chl=";
	var iconUrl = baseUrl + pinProgram + "'i\\" + "'[" + label + 
				"'-2'f\\"  + "hv'a\\]" + "h\\]o\\" + 
				primaryColor.replace("#", "")  + "'fC\\" + 
				labelColor.replace("#", "")  + "'tC\\" + 
				strokeColor.replace("#", "")  + "'eC\\";
	if (addStar) {
		iconUrl += starPrimaryColor.replace("#", "") + "'1C\\" + 
		starStrokeColor.replace("#", "") + "'0C\\";
	}
	iconUrl += "Lauto'f\\";
	
	var icon = {};
	icon.image = iconUrl + "&ext=.png";
	icon.iconSize = (addStar) ? new google.maps.Size(23, 39) : new google.maps.Size(21, 34);
	var width = (addStar) ? 23 : 21;
	var height = (addStar) ? 39 : 34;
	icon.iconAnchor = (addStar) ? new google.maps.Point(12, 39) : new google.maps.Point(11, 34);
	icon.shadowImage = "http://chart.apis.google.com/chart?chst=d_map_pin_shadow";
	if (addStar) {
		icon.shadowImage = "http://chart.apis.google.com/chart?chst=d_map_xpin_shadow&chld=pin_star";
	}
	var shadowWidth = (addStar) ? 47 : 41;
	var shadowHeight = (addStar) ? 39 : 34;
	icon.shadowSize = new google.maps.Size(shadowWidth, shadowHeight);
	
	icon.origin = new google.maps.Point(0, 0);
	icon.markerImage = new google.maps.MarkerImage(icon.image, icon.iconSize, icon.origin, icon.iconAnchor);
	icon.shadowAnchor = (addStar) ? new google.maps.Point(14, 39) : new google.maps.Point(13, 34);
	icon.markerShadow = new google.maps.MarkerImage(icon.shadowImage, icon.shadowSize, icon.origin, icon.shadowAnchor);
	var nostarImageMap = [13,0,15,1,17,2,18,3,18,4,19,5,19,6,19,7,20,8,20,9,20,10,20,11,19,12,19,13,19,14,18,15,17,16,17,17,16,18,15,19,14,20,14,21,13,22,13,23,13,24,12,25,12,26,12,27,12,28,11,29,11,30,11,31,11,32,11,33,9,33,9,32,9,31,9,30,9,29,9,28,8,27,8,26,8,25,8,24,7,23,7,22,6,21,6,20,5,19,4,18,3,17,3,16,2,15,1,14,1,13,1,12,0,11,0,10,0,9,0,8,1,7,1,6,1,5,2,4,2,3,3,2,5,1,7,0,13,0];
	var starImageMap = [16,0,17,1,17,2,17,3,22,4,22,5,22,6,21,7,19,8,19,9,20,10,20,11,20,12,20,13,19,14,19,15,19,16,19,17,19,18,19,19,18,20,17,21,16,22,16,23,15,24,14,25,14,26,13,27,13,28,13,29,12,30,12,31,12,32,12,33,11,34,11,35,11,36,11,37,11,38,9,38,9,37,9,36,9,35,9,34,8,33,8,32,8,31,8,30,7,29,7,28,7,27,6,26,6,25,5,24,4,23,3,22,3,21,2,20,1,19,1,18,1,17,0,16,0,15,0,14,0,13,1,12,1,11,1,10,2,9,2,8,3,7,5,6,7,5,9,4,14,3,14,2,14,1,15,0,16,0];
	icon.imageMap = (addStar) ? starImageMap : nostarImageMap;
	icon.shape = {};
	icon.shape.coords = icon.imageMap;
	icon.shape.type = 'poly';
	return icon;
};


/**
 * Utility function for doing special chart API escaping first,
 *  and then typical URL escaping. Must be applied to user-supplied text.
 * @private
 */
MapIconMaker.escapeUserText_ = function (text) {
  if (text === undefined) {
    return null;
  }
  text = text.replace(/@/, "@@");
  text = text.replace(/\\/, "@\\");
  text = text.replace(/'/, "@'");
  text = text.replace(/\[/, "@[");
  text = text.replace(/\]/, "@]");
  return encodeURIComponent(text);
};

