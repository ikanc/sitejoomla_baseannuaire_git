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

$list = ModSobiproTreeHelper::getList2Level($params);
if (!count($list))
{
	echo 'Empty Section';
	return;
}

?>
<ul id="sptreebrowser<?php echo $moduleid; ?>" 
	class="sptree sobiptree<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php
	$outpcount = '';
	$i = 0;
	$j = 0;
	while ($i < count($list))
	{
		$catid = $list[$i]->c1id;
		$evenoddd = ($j % 2 == 0 ? 'even' : 'odd');
		$j++;
		$iid = ModSobiproTreeHelper::getItemId($scitemid, $subItemsid, $catid, $defaultItemid);
		$url = "index.php?option=com_sobipro&sid=" . $catid . ":" . str_replace('_', '-', $list[$i]->c1alias) . ($iid ? "&Itemid=" . $iid : null);
		$url = JRoute::_($url);

		$counter_l1 = $list[$i]->c1counter;
		$total_l1 = (property_exists($list[$i], 'c1total') ? $list[$i]->c1total : 0 );
		$c = ModSobiproTreeHelper::getCounter($scounter, $total_l1, $counter_l1);
		if ($c)
		{
			$outpcount = ' <span class="counter"> (' . $c . ')</span>';
		}

		$class = ($actual_sid == $catid ? ' active sobiprocateg_active' : null);

		if ((!$hide_empty) || ($total_l1 > 0))
		{
			echo "\t<li class=\"sobiptree_l1 {$evenoddd}{$class} spt{$catid}\">
				<span class=\"folder\">
				<a href=\"{$url}\" class=\"{$class}\">{$list[$i]->c1name}</a>{$outpcount}</span>\n";
			echo "\t<ul class=\"sobiptree_l1 {$evenoddd}{$class}\">\n";
		}

		while (($i < count($list)) && ($catid == $list[$i]->c1id))
		{
			$catid = $list[$i]->c1id;

			if ((!$hide_empty) || ($total_l1 > 0))
			{
				$myId = $list[$i]->id;
				$evenoddd = ($i % 2 == 0 ? 'even' : 'odd');
				$iid = ModSobiproTreeHelper::getItemId($scitemid, $subItemsid, $myId, $defaultItemid);
				$url = "index.php?option=com_sobipro&sid=" . $myId . ":" . str_replace('_', '-', $list[$i]->alias) . ($iid ? "&Itemid=" . $iid : null);
				$url = JRoute::_($url);

				$total = (property_exists($list[$i], 'total') ? $list[$i]->total : 0 );
				$counter = $list[$i]->counter;
				$c = ModSobiproTreeHelper::getCounter($scounter, $total, $counter);
				if ($c)
				{
					$outpcount = ' <span class="counter"> (' . $c . ')</span>';
				}

				$class = ($actual_sid == $myId ? ' active sobiprocateg_active' : null);

				if ((!$hide_empty) || ($total > 0))
				{
					echo "\t\t<li class=\"sobiptree_l2 {$evenoddd}{$class} spt{$myId}\">
						<span class=\"file\">
						<a href=\"{$url}\" class=\"{$class}\">{$list[$i]->name}</a>{$outpcount}</span></li>\n";
				}
			}
			$i++;
		}
		if ((!$hide_empty) || ($total_l1 > 0))
		{
			echo "\t</ul>\n";
			echo "\t</li>\n";
		}
	}
	?>
</ul>
