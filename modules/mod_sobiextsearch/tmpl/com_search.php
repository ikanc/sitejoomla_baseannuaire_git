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

$toAddScriptDeclaration[] = "extSearchHelper{$moduleid}.assignFields();";

$searchword = JRequest::getVar('searchword', $text);
if (strcmp($searchword, '----------') == 0)
{
	$searchword = $text;
}
$searchword = '<input name="searchword" id="modsearchsearchword' . $moduleid
		. '" maxlength="' . $maxlength . '"  class="inputbox' . $moduleclass_sfx
		. '" type="text" size="' . $width . '" value="' . $searchword
		. '"  onblur="if (this.value==\'\') this.value=\'' . $text
		. '\';" onfocus="if (this.value==\'' . $text . '\') this.value=\'\';" />';

if ($button)
{
	if ($imagebutton)
	{
		$button = '<input type="image" value="' . $button_text . '" class="button'
				. $moduleclass_sfx . ' button_img" src="' . $img
				. '" onclick="this.form.searchword.focus();extSearchHelper' . $moduleid . '.extractFormValues();"/>';
	}
	else
	{
		$button = '<input type="submit" value="' . $button_text . '" class="button'
				. $moduleclass_sfx . '" onclick="this.form.searchword.focus();extSearchHelper' . $moduleid . '.extractFormValues();"/>';
	}
}

$output = ModSobiExtSearchHelper::getForm($sectionid, $searchword, $button, $loader, $mdebug);
$output = ModSobiExtSearchHelper::fixComSearch($output);

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
<form action="<?php echo JRoute::_('index.php'); ?>" method="get" 
	  id="<?php echo $searchformid; ?>">
    <div class="XTsearch<?php echo $moduleclass_sfx ?>">
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

		/*         <!-- input type="hidden" name="task" value="search" / --> */
		?>
        <input type="hidden" name="option" value="com_search" />
        <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
    </div></div>
</form>
<!-- mod_sobiextsearch END -->
