jQuery(document).ready(function () {
    if (typeof jQuery.cookie === 'undefined') {
        jQuery.getScript("modules/mod_sobipro_tree/assets/jquery.treeviewboth.js", function () {
            jQuery("#sptreebrowser").treeview({
                collapsed: true,
                animated: "fast",
                persist: true
            });
        });
    } else {
        jQuery("#sptreebrowser").treeview({
            collapsed: true,
            animated: "fast",
            persist: true
        });

    }
});