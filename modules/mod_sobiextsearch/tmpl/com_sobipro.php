<?php
/**
 * @package     Prieco.Modules
 * @subpackage  mod_sobiextsearch - This module will load the "Extended Search" as a module.
 * 
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2010 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.prieco.com http://www.extly.com http://support.extly.com 
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/* <input type="text" name="sp_search_for" value="search..." class="XTSPSearchBox" id="XTSPSearchBox" /> */
$sp_search_for = JRequest::getVar('sp_search_for', $text);
$searchword = '<input type="text" name="sp_search_for" size="' . $width
		. '" value="' . $sp_search_for . '"  onblur="if (this.value==\'\') this.value=\''
		. $text . '\';" onfocus="if (this.value==\'' . $text
		. '\') this.value=\'\';" class="XTSPSearchBox inputbox' . $moduleclass_sfx
		. '" id="XTSPSearchBox' . $moduleid . '" maxlength="' . $maxlength . '"/>';

/* <input type="submit" name="search" value="Search" id="XTtop_button" /> */
if ($button)
{
	if ($imagebutton)
	{
		$button = '<input type="image" id="XTtop_button" name="search" value="' . $button_text
				. '" class="button' . $moduleclass_sfx . ' button_img" src="' . $img
				. '" onclick="this.form.sp_search_for.focus();extSearchHelper' . $moduleid . '.extractFormValues();"/>';
	}
	else
	{
		$button = '<input type="submit" id="XTtop_button" name="search" value="' . $button_text
				. '" class="button' . $moduleclass_sfx . '" onclick="this.form.sp_search_for.focus();extSearchHelper' . $moduleid . '.extractFormValues();"/>';
	}
}

$output = ModSobiExtSearchHelper::getForm($sectionid, $searchword, $button, $loader, $mdebug);

if ($categorymode)
{
	$result = ModCategoryBrowserHelper::getCategoryMode(
					$moduleid, $sectionid, $categorystartlevel, $categorymode, $sorder, $catlist, $mdebug, $jchainedlib
	);
	if ($result['js'])
	{
		$toAddScriptDeclaration[] = $result['js'];
	}

	$selects = '<div class="XTSPSearchCell"><div class="XTSPSearchLabel"><strong>'
			. JTEXT::_('MOD_SOBIEXTSEARCH_CATEGORIES') . ':</strong></div><div class="XTSPSearchField">' .
			$result['body'] .
			'</div></div><div class="spspacer" style="clear:both; margin-bottom: 10px;"></div>';
	$pattern = "/(<div id=\"XTSPExtSearch\">)/si";
	$output = preg_replace($pattern, '<div id="XTSPExtSearch">' . $selects, $output, -1);
}

ModSobiExtSearchHelper::generateScriptDeclaration($document, $toAddScriptDeclaration);

$output = ModSobiExtSearchHelper::enumerateClass($output, 'XTSPSearchCell');

if ($mj_rs)
{
	// Case 1
	$output = str_replace(
			"<!-- Button 'mj_rs_cutom' Output -->",
			'<!-- Button \'mj_rs_cutom\' Output -->
				<button type="button" name="mj_rs_cutom"  id="mj_rs_cutom" class="inputbox" 
				onClick="mjRsHelper.userPos();" style="border: 1px solid silver;">Locate Me</button>',
			$output
		);

	// Case 2
	$output = str_replace('onClick="userPos();"', 'onClick="mjRsHelper.userPos();"', $output);
}
?>

<!-- mod_sobiextsearch BEGIN -->
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" 
	  id="<?php echo $searchformid; ?>" >
    <div class="XTsearch<?php echo $moduleclass_sfx; ?>">
		<?php
		echo $output;

		if ($categorymode > 1)
		{
			?>
			<input type="hidden" id="sid_list<?php echo $moduleid; ?>" name="sid_list" value=""/>
			<?php
		} else
		{
			if (count($catlist) > 0)
			{
				$sid_list = implode(',', $catlist);
				echo '<input type="hidden" id="sid_list' . $moduleid . '" name="sid_list" value="' . $sid_list . '"/>';
			}
		}
		?>
        <input type="hidden" id="XTSP_Itemid" name="Itemid" value="<?php echo $mitemid; ?>"/>
    </div></div>
</form>
<!-- mod_sobiextsearch END -->


