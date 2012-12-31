/* ------------------------------------------------------------------------
  # mod_sobiextsearch - This module will load the "Extended Search" as a module.
  # ------------------------------------------------------------------------
  # author    Prieco S.A.
  # copyright Copyright (C) 2011 Prieco.com. All Rights Reserved.
  # @license - http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
  # Websites: http://www.prieco.com
  # Technical Support:  Forum - http://www.prieco.com/en/contact.html
  ------------------------------------------------------------------------- 
  
Autocomplete based on:
http://demo.sobi.pro/components/com_sobipro/usr/templates/sobirestara/js/search.js

 * @package: SobiPro Template SobiRestara
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license Sigsiu.NET Template License V1.
 * ===================================================
   
*/



/*jslint plusplus: true, browser: true, sloppy: true */
/*global jQuery, SobiProUrl*/

var ExtSearchHelper = function (sectionid, searchformid, searchsearchwordid, sidlistid, no_search_value_const, toAddField, toFillSearchBox) {
        this.sectionid = sectionid;
        this.searchsearchwordid = searchsearchwordid;
        this.searchsearchword = jQuery(searchsearchwordid);
        this.searchformid = searchformid;
        this.search_form = this.getForm();
		this.sidlistid = sidlistid;
		this.sidlist = jQuery(sidlistid);

        this.no_search_value_const = no_search_value_const;

        this.new_values = [];
        this.sid_list = [];

        this.toAddField = toAddField;
        this.toFillSearchBox = toFillSearchBox;

        this.cache = {};
        this.lastXhr = null;
    };

ExtSearchHelper.prototype.getForm = function () {
    return jQuery(this.searchformid);
};

ExtSearchHelper.prototype.bind = function (jqueryui) {
    var that = this;
    if (typeof that.searchsearchword.autocomplete === 'function') {
        that.searchsearchword.autocomplete({
            minLength: 3,
            source: function (request, response) {
                that.queryAutocomplete(request, response);
            }
        });
    } else {
        jQuery.getScript(jqueryui, function () {
            /* SAME CODE, BUT FORCED jquery-ui.js */
            that.searchsearchword.autocomplete({
                minLength: 3,
                source: function (request, response) {
                    that.queryAutocomplete(request, response);
                }
            });
        });
        /* SAME CODE, BUT FORCED jquery-ui.js */
    }
};

ExtSearchHelper.prototype.queryAutocomplete = function (request, response) {
    var that = this,
        term = request.term;
    if (this.cache.hasOwnProperty(term)) {
        response(this.cache.term);
        return;
    }
    this.lastXhr = jQuery.ajax({
        url: SobiProUrl.replace('%task%', 'search.suggest'),
        data: {
            term: request.term,
            sid: this.sectionid,
            tmpl: 'component',
            format: 'raw'
        },
        success: function (data) {
            that.cache.term = data;
            response(data);
        }
    });
};

ExtSearchHelper.prototype.decode = function (str) {
    str = decodeURIComponent(str);
    return str.replace(/\+/g, " ");
};

// WTF with JS Maps
ExtSearchHelper.prototype.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
};

ExtSearchHelper.prototype.get = function (url) {
    var results = [], k, v;
    url = url.split("?");
    if (url.length > 1) {
        url = url[1].split("#");
        if (url.length > 1) {
            results.hash = url[1];
        }
        url[0].split("&").forEach(function (item, index) {
            item = item.split("=");
			k = ExtSearchHelper.prototype.decode(item[0]);
			v = ExtSearchHelper.prototype.decode(item[1]);
            results[k] = v;
        });
    }
    return results;
};

ExtSearchHelper.prototype.assignFields = function (fields) {
    var field_selector, field, modsearchsearchword, key, i, newvalue, newkey, n;
    modsearchsearchword = this.searchsearchword.val();

    if (arguments.length === 0) {
        fields = this.get(window.location.toString());
    }
    n = ExtSearchHelper.prototype.size(fields);
    for (key in fields) {
        if (fields.hasOwnProperty(key)) {

            if (key.search(/^field_/) === 0) {
                newvalue = fields[key];
                if (key.search(/\[\]$/) !== -1) {
                    newkey = key.replace(/\[\]/, "");
                    field_selector = this.searchformid + " #XT" + newkey + "_" + newvalue;
                    field = jQuery(field_selector);
                    if (field.size() === 1) {
                        field.attr('checked', 'checked');
                        modsearchsearchword = modsearchsearchword.replace(newvalue, "");
                    } else {
                        field_selector = this.searchformid + " #XT" + newkey;
                        field = jQuery(field_selector);
                        field.val(newvalue);
                        modsearchsearchword = modsearchsearchword.replace(newvalue, "");
                    }
                } else {
                    field_selector = this.searchformid + " [name=" + key + "]";
                    field = jQuery(field_selector);
                    field.val(newvalue);
                    modsearchsearchword = modsearchsearchword.replace(newvalue, "");
                }
            }
        }
    }
    if ((modsearchsearchword) && (jQuery.trim(modsearchsearchword).length > 0)) {
        this.searchsearchword.val(modsearchsearchword.replace(/^\s*|\s*$/g, ""));
    } else {
        this.searchsearchword.val(this.no_search_value_const);
    }

    return modsearchsearchword;
};

ExtSearchHelper.prototype.addField = function (sname, svalue) {
    if (sname.match("^field_") && svalue && svalue.length > 0) {
        this.new_values.push(svalue);
    }
    return this.new_values;
};

ExtSearchHelper.prototype.addCategory = function (sname, svalue) {
    if (sname.match(/^to_sid_list/) && svalue && svalue.length > 0) {
        var strOut = svalue.replace(/\D/g, '');
        this.sid_list.push(strOut);
    }
};

ExtSearchHelper.prototype.fillSidList = function () {
    var v = null;
    if (this.sid_list.length > 0) {
        v = this.sid_list.join(",");
        this.sidlist.val(v);
    }
    return v;
};

ExtSearchHelper.prototype.checkEmptyCondition = function (no_search_value) {
    if (no_search_value) {
        this.searchsearchword.val('----------');
    }
};

ExtSearchHelper.prototype.checkSearchValue = function () {
    var v = this.searchsearchword.val();
    return ((v === this.no_search_value_const) || (v.length === 0));
};

ExtSearchHelper.prototype.fillSearchBox = function () {
    var no_search_value, f, v;
    no_search_value = this.checkSearchValue();

    if (this.new_values.length > 0) {
        if (no_search_value) {
            this.searchsearchword.val(this.new_values.join(" "));
        } else {
            this.searchsearchword.val(this.searchsearchword.val() + " " + this.new_values.join(" "));
        }

        no_search_value = false;
    }
    this.checkEmptyCondition(no_search_value);
};

ExtSearchHelper.prototype.extractFormValues = function () {
    var aparms, i, avalue, sname, svalue;
    this.new_values = [];
    this.sid_list = [];

    aparms = this.search_form.serialize().split("&");
    for (i = 0; i < aparms.length; i++) {
        avalue = aparms[i].split("=");
        sname = this.decode(avalue[0]);
        svalue = this.decode(avalue[1]);

        if (this.toAddField) {
            this.toAddField(sname, svalue);
        }
        this.addCategory(sname, svalue);
    }
    if (this.toFillSearchBox) {
        this.toFillSearchBox();
    }
    this.fillSidList();

    return this.sid_list;
};