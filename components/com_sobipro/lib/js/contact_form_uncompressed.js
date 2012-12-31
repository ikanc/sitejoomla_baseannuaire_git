/**
* @version: $Id: contact_form_uncompressed.js 2557 2012-07-06 16:46:16Z Sigrid Suski $
* @package: SobiPro - Contact Form Field
* ===================================================
* @author
* Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
* Email: sobi[at]sigsiu.net
* Url: http://www.Sigsiu.NET
* ===================================================
* @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
* @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
* You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
* ===================================================
* $Date: 2012-07-06 18:46:16 +0200 (Fr, 06 Jul 2012) $
* $Revision: 2557 $
* $Author: Sigrid Suski $
*/
function SPContactForm( bid )
{
	jQuery( document ).ready(
		function () {
			var form = jQuery( '#' + bid.replace( '_button', '_form' ) );
			var container = jQuery( '#' + bid.replace( '_button', '_container' ) );

            jQuery( '#' + bid.replace( '_button', '_send_button' ) ).bind( 'click', function ( e ) {
                e.stopPropagation();
                e.preventDefault();
                SPCSendMessage( form );
         	} );
            if( jQuery( '#' + bid ).val() ) {
                var size = {
                    'height': container.height(),
                    'width': container.width()
                };
                container.hide();
                container.find( '.spCfCloseBt, .spCfSendBt' ).hide();
                var buttons = {};
                buttons[ form.find( '.spCfCloseBt' ).text() ] = function () {
                    jQuery( this ).dialog( 'close' );
                };
                buttons[ form.find( '.spCfSendBt' ).text() ] = function () {
                    SPCSendMessage( form );
                };

                jQuery( '#' + bid ).bind( 'click', function () {
                    form.spDialogId = '#' + bid.replace( '_button', '_container' );
                    jQuery( form.spDialogId ).dialog( {
                        minHeight: size.height,
                        width: size.width,
                        modal: true,
                        show: 'slide',
                        hide: 'slide',
                        buttons: buttons
                    } );
                } );
            }
            else {
                container.find( '.spCfCloseBt' ).hide();
            }
		}
	);
}

function SPCSendMessage( form )
{
	jQuery.ajax( {
		url:SobiProUrl.replace( '%task%', 'contact.send' ),
		data:form.serialize(),
		type:'POST',
		dataType:'json',
		success:function ( data ) {
			alert( data.message );
			if ( data.status == 'error' ) {
				if ( data.require ) {
					jQuery( '[name="' + data.require + '"]' )
						.css( 'border-color', 'red' )
						.css( 'border-style', 'solid' );
				}
			}
			else {
				form.find( 'input:text, input:password, input:file, select, textarea' ).val( '' );
				form.find( 'input:radio, input:checkbox' ).removeAttr( 'checked' ).removeAttr( 'selected' );
				try { jQuery( form.spDialogId ).dialog( 'close' ); } catch ( x ) {}
			}
		}
	} );
}
