/*
 * SimpleModal OSX Style Modal Dialog
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2010 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: download.js 836 2011-02-23 13:49:58Z Radek Suski $
 */

var OSX;
var $JQ;
jQuery(function ($) { $JQ = $ });

function SPEnableDwnl( id, check ) 
{
	SP_id( id + '_license_dwnl' ).disabled = !( check.checked );
}

function SPFinishDwnl( id, txt )
{
	SP_id( id ).value = txt;
}

function SPConfirmLicense( id )
{
	$ = $JQ;
	OSX = {
			container: null,
			init: function () {
					$( '#' + id + '_license' ).modal({
						overlayId: 'osx-overlay',
						containerId: 'osx-container',
						closeHTML: null,
						minHeight: 80,
						width: 1600,
						opacity: 65, 
						position: ['0',],
						overlayClose: true,
						onOpen: OSX.open,
						onClose: OSX.close,
						persist: true,
						containerCss:{ width:850 }
					});
			},
			open: function (d) {
				var self = this;
				self.container = d.container[0];
				d.overlay.fadeIn('slow', function () {
					$( '#' + id + '_license', self.container ).show();
					var title = $( '#' + id + '_license_title', self.container );
					title.show();
					d.container.slideDown('slow', function () {
						setTimeout(function () {
							var h = $( '#' + id + '_license_data', self.container).height()
								+ title.height()
								+ 20; // padding
							d.container.animate(
								{height: h}, 
								100,
								function () {
									$( '#' + id + '_license_close', self.container).show();
									$( '#' + id + '_license_data', self.container).show();
								}
							);
						}, 300);
					});
				});
			},
			close: function (d) {
				var self = this;
				d.container.animate(
					{top:"-" + (d.container.height() + 200)},
					500,
					function () {
						self.close();					
					}
				);
			}
		};

		OSX.init();	
}