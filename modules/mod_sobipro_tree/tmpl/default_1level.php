<?php

/**
 * @package     Extly.Modules
 * @subpackage  mod_sobipro_tree - SobiPro Tree of Categories
 * 
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.prieco.com http://www.extly.com http://support.extly.com 
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

$list = ModSobiproTreeHelper::getList($params);
if (!count($list))
{
	echo 'Empty Section';
	return;
}


?>
<ul id="sptreebrowser<?php echo $moduleid; ?>" 
	class="sptree sobiptree<?php echo $params->get('moduleclass_sfx'); ?>"><?php
	$outpcount = '';
	$i = 0;
	foreach ($list as $item)
	{
		$evenoddd = ($i % 2 == 0 ? 'even' : 'odd');
		$iid = ModSobiproTreeHelper::getItemId($scitemid, $subItemsid, $item->id, $defaultItemid);
		$url = "index.php?option=com_sobipro&sid=" . $item->id . ":" . str_replace('_', '-', $item->alias) . ($iid ? "&Itemid=" . $iid : null);

		$total = (property_exists($item, 'total') ? $item->total : 0 );
		$counter = $item->counter;
		$c = ModSobiproTreeHelper::getCounter($scounter, $total, $counter);
		if ($c)
		{
			$outpcount = ' <span class="counter"> (' . $c . ')</span>';
		}

		$class = ($actual_sid == $item->id ? ' active sobiprocateg_active' : null);

		if ((!$hide_empty) || ($total > 0))
		{
			?>
			<li class="<?php echo $evenoddd . $class; ?>">
				<span class="file spt<?php echo $item->id; ?>">
					<a href="<?php echo JRoute::_($url); ?>" 
					   class="<?php echo $class; ?>">
			<?php echo $item->name; ?></a>
				<?php echo $outpcount; ?></span>
			</li>
			<?php
		}
		$i++;
	}
	?>
</ul>
