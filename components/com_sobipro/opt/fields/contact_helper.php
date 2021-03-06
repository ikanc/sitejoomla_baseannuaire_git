<?php
/**
 * @version: $Id: contact_helper.php 2557 2012-07-06 16:46:16Z Sigrid Suski $
 * @package: SobiPro - Contact Form Field
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-07-06 18:46:16 +0200 (Fr, 06 Jul 2012) $
 * $Revision: 2557 $
 * $Author: Sigrid Suski $
 */

class SPContactHelper
{
    public function __construct()
    {

    }

    public function prepareMessageSettings( $action, &$settings, &$args )
    {
        $settings = $args[ 'settings' ];
        unset( $args[ 'settings' ] );
    }
}
