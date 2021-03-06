<?php
/**
 * @version: $Id: users.php 1599 2011-07-06 09:40:35Z Radek Suski $
 * @package: SobiPro Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-07-06 11:40:35 +0200 (Wed, 06 Jul 2011) $
 * $Revision: 1599 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/cms/joomla16/base/users.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 03-Feb-2009 5:14:11 PM
 */
class SPUsers
{

	public static function getGroupsField ()
	{
		$db = &JFactory::getDbo();
		$db->setQuery( '
				 SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level
				 FROM #__usergroups AS a
				 LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt
				 GROUP BY a.id
				 ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();
		// Check for a database error.
		if ( $db->getErrorNum() ) {
			JError::raiseNotice( 500, $db->getErrorMsg() );
			return null;
		}
		for ( $i = 0, $n = count( $options ); $i < $n; $i ++ ) {
			$options[ $i ]->text = str_repeat( '- ', $options[ $i ]->level ) . $options[ $i ]->text;
		}
		$gids = array();
		foreach( $options as $k => $v ) {
			$gids[] = get_object_vars( $v );
		}
		$gids[ 0 ] = array( 'value' => 0, 'text' => Sobi::Txt( 'ACL.REG_VISITOR' ), 'level' => 0 );
		return $gids;
	}
}
?>