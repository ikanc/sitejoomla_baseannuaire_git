/* ------------------------------------------------------------------------
  # mod_sobiextsearch - This module will load the "Extended Search" as a module.
  # ------------------------------------------------------------------------
  # author    Prieco S.A.
  # copyright Copyright (C) 2011 Prieco.com. All Rights Reserved.
  # @license - http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
  # Websites: http://www.prieco.com
  # Technical Support:  Forum - http://www.prieco.com/en/contact.html
  ------------------------------------------------------------------------- */

/*jslint plusplus: true, browser: true, devel: true, sloppy: true */
/*global jQuery, SobiProUrl, google*/

var MjRsHelper = function () {
        //var spSearchDefStr = "search...";
        var input, options, ac;
        input = document.getElementById("XTmj_rs_center_selector");
        options = {
            types: []
        };
        ac = new google.maps.places.Autocomplete(input, options);

        google.maps.event.addListener(ac, "place_changed", function () {
            var pl = ac.getPlace();
            jQuery("#XTmj_rs_ref_lat").val(pl.geometry.location.lat());
            jQuery("#XTmj_rs_ref_lng").val(pl.geometry.location.lng());
        });

        jQuery('#XTmj_rs_center_selector').keypress(function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        });
    };

MjRsHelper.prototype.userPos = function () {
    var gc = new google.maps.Geocoder();
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (po) {
            gc.geocode({
                "latLng": new google.maps.LatLng(po.coords.latitude, po.coords.longitude)
            }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    jQuery("#XTmj_rs_ref_lat").val(po.coords.latitude);
                    jQuery("#XTmj_rs_ref_lng").val(po.coords.longitude);
                    jQuery("#XTmj_rs_center_selector").val(results[0].formatted_address);
                } else {
                    alert("Geolocation did not work. Reason :" + status);
                }
            });
        });
    } else {
        alert("In order to use this function you must allow your browser to share your location.");
    }
};