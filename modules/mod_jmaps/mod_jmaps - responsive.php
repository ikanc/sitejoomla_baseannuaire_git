<?php 
/**
* @package: jMaps Module for SOBI2
* ===================================================
* @author
* Name: Jerry Johnson, www.websolutionware.com
* Email: jerry@websolutionware.com
* Url: http://www.websolutionware.com
* ===================================================
* @copyright Copyright (C) 2011 WebSolutionWare.com (http://www.websolutionware.com). All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* jMaps module is open source software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/*
*----------------------------------------------------------------------
* This code creates a Google Map of Markers from an input file
* It displays in a module position
*----------------------------------------------------------------------
*/

global $mosConfig_absolute_path, $sess, $mainframe;

//$sobiTask = '';
//if (!empty($_GET['sobi2Task'])) {
//	$sobiTask = $_GET['sobi2Task'];
//}
////echo '$sobiTask = '.$sobiTask.'<br />';
//$jmapExcludeTasks = $params->get('JmapExcludeTasks', 'sobi2Details, addNew, editSobi');
//$workArray1 = explode(',', $jmapExcludeTasks);
//for ($i=0;$i<count($workArray1);++$i) {
//	$workArray1[$i] = trim($workArray1[$i]);
//}
//if(in_array($sobiTask, $workArray1)) {
//	return false;
//}
$cookieName = 'jmapState';
setcookie($cookieName,"", time()-3600, '/');
//Map
$jmapWidth = $params->get('JmapWidth', '600px');
$jmapHeight = $params->get('JmapHeight', '400px');
$jmapDivID = $params->get('JmapDivID', 'Jmaps');
$jmapKeepInView = $params->get('JmapKeepInView', '0');
$jmapBackgroundColor = $params->get('JmapBackgroundColor', '#00FF00');
$jmapCenterLatitude = $params->get('JmapCenterLatitude', '38.898748');
$jmapCenterLongitude = $params->get('JmapCenterLongitude', '-77.037684');
$jmapDisableDoubleClickZoom = $params->get('JmapDisableDoubleClickZoom', 0);
$jmapDraggable = $params->get('JmapDraggable', 1);
$jmapMapTypeControl = $params->get('JmapMapTypeControl', 1);
$jmapMapTypeControlPosition = $params->get('JmapMapTypeControlPosition', 'topRight');
$jmapMapTypeControlStyle = $params->get('JmapMapTypeControlStyle', 'bar');
$jmapMapTypeId = $params->get('JmapMapTypeId', 'roadmap');
$jmapMaxZoom = $params->get('JmapMaxZoom', '18');
$jmapMinZoom = $params->get('JmapMinZoom', '2');
$jmapOverviewMapControl = $params->get('JmapOverviewMapControl', 0);
$jmapOverviewMapControlOpened = $params->get('JmapOverviewMapControlOpened', 0);
$jmapPanControl = $params->get('JmapPanControl', 1);
$jmapPanControlPosition = $params->get('JmapPanControlPosition', 'topLeft');
$jmapScaleControl = $params->get('JmapScaleControl', 1);
$jmapScaleControlPosition = $params->get('JmapScaleControlPosition', 'bottomLeft');
$jmapScrollWheel = $params->get('JmapScrollWheel', 1);
$jmapStreetViewControl = $params->get('JmapScaleControl', 1);
$jmapStreetViewControlPosition = $params->get('JmapStreetViewControlPosition', 'topLeft');
$jmapZoom = $params->get('JmapZoom', '5');
$jmapZoomControl = $params->get('JmapZoomControl', 1);
$jmapZoomControlPosition = $params->get('JmapZoomControlPosition', 'topLeft');
$jmapZoomControlStyle = $params->get('JmapZoomControlStyle', 'small');
//Streetview
$jmapShowStreetView = $params->get('JmapShowStreetView', 1);
$jmapSVAddressControl = $params->get('JmapSVAddressControl', 1);
$jmapSVAddressControlPosition = $params->get('JmapSVAddressControlPosition', 'leftTop');
$jmapSVDisableDoubleClickZoom = $params->get('JmapSVDisableDoubleClickZoom', 0);
$jmapSVPanControl = $params->get('JmapSVPanControl', 1);
$jmapSVPanControlPosition = $params->get('JmapSVPanControlPosition', 'topLeft');
$jmapSVOverrideContainer = $params->get('JmapSVOverrideContainer', '');
$jmapSVScrollWheel = $params->get('JmapSVScrollWheel', 1);
$jmapSVZoomControl = $params->get('JmapSVZoomControl', 1);
$jmapSVZoomControlPosition = $params->get('JmapSVZoomControlPosition', 'topLeft');
$jmapSVZoomControlStyle = $params->get('JmapSVZoomControlStyle', 'small');
//Marker
$jmapMarkerColor = $params->get('JmapMarkerColor', '#00FF00');
$jmapMarkerStroke = $params->get('JmapMarkerStroke', '#000000');
$jmapMarkerWidth = $params->get('JmapMarkerWidth', '32');
$jmapMarkerHeight = $params->get('JmapMarkerHeight', '32');
$jmapMarkerLabel = $params->get('JmapMarkerLabel', '#000000');
$jmapShowMarkerLabel = $params->get('JmapShowMarkerLabel', '1');
$jmapCustomMarker = $params->get('JmapCustomMarker', '');
$jmapMarkerClass = $params->get('JmapMarkerClass', '');
$jmapFeaturedMarkerColor = $params->get('JmapFeaturedMarkerColor', '#00FF00');
$jmapFeaturedMarkerStroke = $params->get('JmapFeaturedMarkerStroke', '#000000');
$jmapFeaturedMarkerLabel = $params->get('JmapFeaturedMarkerLabel', '#000000');
$jmapShowStar = $params->get('JmapShowStar', '0');
$jmapStarColor = $params->get('JmapStarColor', '#E2B61D');
$jmapStarStroke = $params->get('JmapStarStroke', '#E2B61D');
$jmapCustomFeaturedMarker = $params->get('JmapCustomFeaturedMarker', '');
$jmapMMMinZoom = $params->get('JmapMMMinZoom', '2');
$jmapMMMaxZoom = $params->get('JmapMMMaxZoom', '18');
$jmapSOBIcatMarkerColors = $params->get('JmapSOBIcatMarkerColors', '');
$jmapMarkerPointAnimation = $params->get('$JmapMarkerPointAnimation', null);
$jmapMarkerPointClickable = $params->get('$JmapMarkerPointClickable', 1);
$jmapMarkerPointRaiseOnDrag = $params->get('$JmapMarkerPointRaiseOnDrag', 0);
$jmapMarkerPointOpenHTMLEvent = $params->get('$JmapMarkerPointOpenHTMLEvent', 'click');
$jmapMarkerDisableAutoPan = $params->get('$JmapMarkerDisableAutoPan', 0);
$jmapMarkerMaxWidth = $params->get('$JmapMarkerMaxWidth', 250);
//Clusterer
$jmapMarkerClusterer = $params->get('JmapMarkerClusterer', 0);
$jmapClustererGridSize = $params->get('JmapClustererGridSize', 60);
$jmapClustererMinimumClusterSize = $params->get('JmapClustererMinimumClusterSize', 2);
$jmapClustererZoomOnClick = $params->get('JmapClustererZoomOnClick', 1);
$jmapClustererAverageCenter = $params->get('JmapClustererAverageCenter', 0);
$jmapClustererMaxZoom = $params->get('JmapClustererMaxZoom', 0);
$jmapClustererStyle = $params->get('JmapClustererStyle', 'default');
//Directions
$jmapShowToDirections = $params->get('JmapShowToDirections', '1');
$jmapShowFromDirections = $params->get('JmapShowFromDirections', '1');
$jmapDirectionsAvoidHighways = $params->get('JmapDirectionsAvoidHighways', '0');
$jmapDirectionsAvoidTolls = $params->get('JmapDirectionsAvoidTolls', '0');
$jmapDirectionsAlternativeRoutes = $params->get('JmapDirectionsAlternativeRoutes', '0');
$jmapDirectionsContainer = $params->get('JmapDirectionsContainer', 'directionsDiv');
$jmapDirWidth = $params->get('JmapDirWidth', '250');
//Misc
$jmapSource = $params->get('JmapSource', 'other');
$jmapCBlist = $params->get('JmapCBlist', '2');
$jmapCBAll = $params->get('JmapCBAll', '1');
$jmapSOBIcatList = $params->get('JmapSOBIcat', '0');
$jmapSOBIcatAll = $params->get('JmapSOBIcatAll', '1');
$jmapCatState = $params->get('JmapCatState', '');
$jmapSOBIdetailsBox = $params->get('JmapSOBIdetailsBox', '1');
$jmapSOBIdetailsBoxTmpl = $params->get('JmapSOBIdetailsBoxTmpl', '1');
$jmapSOBIdetailsBoxTmplSEF = $params->get('JmapSOBIdetailsBoxTmplSEF', '1');
$jmapShadowBoxWidthPCT = $params->get('JmapShadowBoxWidthPCT', '95');
$jmapShadowBoxWidthPX = $params->get('JmapShadowBoxWidthPX', '0');
$jmapShadowBoxHeightPCT = $params->get('JmapShadowBoxHeightPCT', '95');
$jmapShadowBoxHeightPX = $params->get('JmapShadowBoxHeightPX', '0');
$jmapCategoryPriority = $params->get('JmapCategoryPriority', '0');
$jmapInitZoom = $params->get('JmapInitZoom', '5');
$jmapOverrideCenter = $params->get('JmapOverrideCenter', '0');
$jmapOther = $params->get('JmapOther', 'Jmaps.xml');
$jmapFeed = $params->get('JmapFeed', 'none');
$jmapSOBIoneLiner = $params->get('JmapSOBIoneLiner', '0');

if ($jmapShadowBoxWidthPCT > 0) {
	$shadowboxWidth = $jmapShadowBoxWidthPCT.'%';
} else {
		$shadowboxWidth = $jmapShadowBoxWidthPX.'px';
		}
if ($jmapShadowBoxHeightPCT > 0) {
	$shadowboxHeight = $jmapShadowBoxHeightPCT.'%';
} else {
		$shadowboxHeight = $jmapShadowBoxHeightPX.'px';
		}
	
//echo 'URL = '.JURI::base().'<br />';
$markerColorsArrayCatID = array();
$markerColorsArrayColor = array();
$categoryMarkerJSCodeArray = array();
$jmapXMLname = array();
$jmapSOBIcatSuffix = '';
$jmapCBSuffix = '';
if($jmapSource == 'SOBI2ajax' || $jmapSource == 'SOBIproSearch') {
//	if($sobiTask != 'search' && $sobiTask != 'axSearch') {
//		return false;
//	}
	$cookieName = 'jmapDivID';
	setcookie($cookieName,$jmapDivID, time()+(2635200*12), '/');
	if (!empty($jmapSOBIcatMarkerColors)) {
		$workArray1 = explode(';', $jmapSOBIcatMarkerColors);
		for ($i=0;$i<count($workArray1);++$i) {
			$workArray2 = explode(',', $workArray1[$i]);
			$markerColorsArrayCatID[] = $workArray2[0];
			$markerColorsArrayColor[] = $workArray2[1];
		}
	}
}
if ($jmapCBAll == '1') {
	$jmapCBSuffix = 'All';
}
if($jmapSource == 'cb') {
	$workArray1 = explode(',', $jmapCBlist);
	for ($i=0;$i<count($workArray1);++$i) {
		$jmapXMLname[] = JURI::base().'cb_list'.$workArray1[$i].'File'.$jmapCBSuffix.'.xml';
	}
}
if ($jmapSOBIcatAll == '1') {
	$jmapSOBIcatSuffix = 'All';
}
if($jmapSource == 'SOBI2cat' || $jmapSource == 'SOBIproCat') {
	$workArray1 = explode(',', $jmapSOBIcatList);
	for ($i=0;$i<count($workArray1);++$i) {
		$XMLFileName = JURI::base().'sobi2_cat'.$workArray1[$i];
		if (!empty($jmapCatState) && $jmapSOBIcatSuffix == 'All') {
			$XMLFileName .= $jmapCatState;
		}
		$XMLFileName .= 'File'.$jmapSOBIcatSuffix.'.xml';
		//echo '$jmapXMLname = '.$XMLFileName.'<br />';
		$jmapXMLname[] = $XMLFileName;
	}
	if (!empty($jmapCatState) && $jmapSOBIcatSuffix == 'All') {
		$cookieName = 'jmapState';
		setcookie($cookieName,$jmapCatState, time()+(2635200*12), '/');
	}
}
if($jmapSource == 'other') {
	$workArray1 = explode('||', $jmapOther);
	for ($i=0;$i<count($workArray1);++$i) {
		$jmapXMLname[] = JURI::base().$workArray1[$i];
	}
}
$markerDirectory = 'media' . DS . 'markers';
$directoryWork = scandir($markerDirectory, 1);
$markerList = array();
for ($i=0; $i<count($directoryWork); $i++) {
	if ($directoryWork[$i] == '.' || $directoryWork[$i] == '..') {
		continue;
	}
	$directoryName = $markerDirectory.DS.$directoryWork[$i];
	if (!is_dir($directoryName)) {
		continue;
	}
//	echo '<br /><br />$directoryWork[$i] = ' . $directoryWork[$i];
	$markerList[] = $directoryWork[$i];
}
//echo '<br /><br />';
//print_r($markerList);
$customMarkerJSCodeArray[] = 'customMarkers = [{\'markerImage\': null, \'markerShadow\': null, \'shape\': null}];' . "\n";
$customMarkerJSCodeArray[] = 'var baseMarker = "' . $jmapMarkerColor . '";' . "\n";
for ($i=0; $i<count($markerList); $i++) {
	$iconName = $markerList[$i];
	$iconFileName = 'media' . DS . 'markers' . DS . $iconName . DS . 'sample-code.text';
//	echo '<br /><br />$iconFileName = '.$iconFileName.'<br />';
	if (file_exists($iconFileName)) {
		$iconFileHandle = fopen($iconFileName, 'r') or die('Can not open file for icon in Jmaps module!');
		$outputSW = false;
		while (!feof($iconFileHandle)) {
			$outputLine = fgets($iconFileHandle);
			$workPos = strpos($outputLine, 'MarkerImage');
			if ($workPos > 0) {
				$outputSW = true;
			}
			$workPos = strpos($outputLine, 'google.maps.Marker(');
			if ($workPos > 0) {
				$outputSW = false;
			}
			if ($outputSW) {
				$workPos = strpos($outputLine, 'marker-images');
				if ($workPos > 0) {
					$workField = "'".JURI::base()."/media/markers/".$iconName."/marker-images";
					$outputLine = str_replace("'marker-images", $workField, $outputLine);
				}
				$customMarkerJSCodeArray[] = $outputLine;
				continue;
			}
		}
		fclose($iconFileHandle);			
		$customMarkerJSCodeArray[] = "customMarkers['" . $iconName . "'] = {};" . "\n";
		$customMarkerJSCodeArray[] = "customMarkers['" . $iconName . "'].markerImage = image;\n";
		$customMarkerJSCodeArray[] = "customMarkers['" . $iconName . "'].markerShadow = shadow;\n";
		$customMarkerJSCodeArray[] = "customMarkers['" . $iconName . "'].shape = shape;\n";
	}

//Custom Marker load
	$customMarkerJSCodeArray[] = 'var baseMarker = "' . $jmapMarkerColor . '";' . "\n";
	if (!empty($jmapCustomMarker)) {
		$iconFileName = 'media' . DS . 'markers' . DS . $jmapCustomMarker . DS . 'sample-code.text';
		if (file_exists($iconFileName)) {
			$customMarkerJSCodeArray[] = 'baseMarker = "' . $jmapCustomMarker . '";' . "\n";
		}
	}
//Custom Featured Marker load
	$customMarkerJSCodeArray[] = 'var featuredMarker = "' . $jmapFeaturedMarkerColor . '";' . "\n";
	if (!empty($jmapCustomFeaturedMarker)) {
		$iconFileName = 'media' . DS . 'markers' . DS . $jmapCustomFeaturedMarker. DS . 'sample-code.text';
		if (file_exists($iconFileName)) {
			$customMarkerJSCodeArray[] = 'featuredMarker = "' . $jmapCustomFeaturedMarker . '";' . "\n";
		}
	}

}

//Category Marker Arrays load
$markerArrayCatID = array();
if (!empty($jmapSOBIcatMarkerColors)) {
	$workArray1 = explode(';', $jmapSOBIcatMarkerColors);
	for ($ci=0;$ci<count($workArray1);++$ci) {
		$workArray2 = explode(',', $workArray1[$ci]);
		if (preg_match("/\#/", $workArray2[1])) {
			$markerArrayCatID[] = $workArray2[0];
			$markerArrayMarker[] = $workArray2[1];
		} else {
				$iconCatID = $workArray2[0];
				$iconName = $workArray2[1];
				$iconFileName = 'media' . DS . 'markers' . DS . $iconName . DS . 'sample-code.text';
				if (file_exists($iconFileName)) {
					$markerArrayCatID[] = $workArray2[0];
					$markerArrayMarker[] = $workArray2[1];
				}
				}
	}
}
$customMarkerJSCodeArray[] = 'var categoryMarker = [];' . "\n";
for ($j=0; $j<count($markerArrayCatID); $j++) {
	$customMarkerJSCodeArray[] = 'categoryMarker["' . $markerArrayCatID[$j] . '"] = "' . $markerArrayMarker[$j] . '";' . "\n";
}

?>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>

<script>


//Globals
	cookieNumber = 0;
<?php
for ($i=0; $i<count($customMarkerJSCodeArray); $i++) {
	echo $customMarkerJSCodeArray[$i];
}
?>
</script>
<style>
<?php if($jmapKeepInView == 1 || (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer))) { ?> 
	#jmapRelativeDiv { /* required to avoid jumping */
	  position: relative;
	  width:<?php echo $jmapWidth; ?>;
	  min-height:<?php echo $jmapHeight; ?>;
	}
	#jmapKeepInView { /* required to avoid jumping */
	  left: 0px; /* Must be set by jQuery!*/
	  position: absolute;
/*	  margin-left: 35px; */
	  width:<?php echo $jmapWidth; ?>;
	  min-height:<?php echo $jmapHeight; ?>;
	}

	#<?php echo $jmapDivID; ?> {
	  position: absolute;
	  top: 0px;
	}
	
	#<?php echo $jmapDivID; ?>.fixed {
	  position: fixed;
	  top: 0px;
	}
	<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
	#<?php echo $jmapDirectionsContainer; ?> { /* required to avoid jumping */
	  position: absolute;
	  top: 0px;
	  left: <?php echo $jmapWidth; ?>;
	}
	#<?php echo $jmapDirectionsContainer; ?>.fixed {
	  position: fixed;
	  top: 0px;
	  left: <?php echo $jmapWidth; ?>;
	}
	<?php }?>
	<?php if (!empty($jmapSVOverrideContainer)) { ?>
	#<?php echo $jmapSVOverrideContainer; ?> { /* required to avoid jumping */
	  position: absolute;
	  top: <?php echo $jmapHeight; ?>;
	}
	#<?php echo $jmapSVOverrideContainer; ?>.fixed {
	  position: fixed;
	  top: <?php echo $jmapHeight; ?>;
	}
	<?php }?>
<?php } else {?>
			<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
			#<?php echo $jmapDivID; ?> {
				float:left;
			}
			#<?php echo $jmapDirectionsContainer; ?> { /* required to avoid jumping */
			  float: right;
			}	
		<?php }?>
		<?php }?>

	#loadmessagehtml div {
		float:none;
		background:url(modules/mod_jmaps/js/images/loading_background.png) repeat;
		width:<?php echo $jmapWidth; ?>;
		margin: 0 auto; /* the auto value on the sides, coupled with the width, centers the layout */
	}
	div#loadmessagehtml {
		text-align:center;
	}
</style>
<?php if($jmapKeepInView == 1 || (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer))) { ?> 
	<div id="jmapRelativeDiv">
		<div id="jmapKeepInView">
<?php }?>
<div id="<?php echo $jmapDivID; ?>" style="width:<?php echo $jmapWidth; ?>; height:<?php echo $jmapHeight; ?>;"></div>
<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
<div id="<?php echo $jmapDirectionsContainer; ?>" style="width:<?php echo $jmapDirWidth; ?>; height:<?php echo $jmapHeight; ?>;"></div>
<?php }?>
<div style="display: none;" id="loadmessagehtml" class="messagehtml"><img src="modules/mod_jmaps/js/images/loading_large.gif" alt="Loading..."/></div>
<?php if (!empty($jmapSVOverrideContainer)) { ?>
<div id="<?php echo $jmapSVOverrideContainer; ?>" style="width:<?php echo $jmapWidth; ?>; height:<?php echo $jmapHeight; ?>;"></div>
<?php }?>
<?php if($jmapKeepInView == 1 || (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer))) { ?> 
		</div>
    </div>
<?php }?>
<!--  jQuery library  -->
<?php $iconNum = 0; ?>

    <link type="text/css" media="screen" rel="stylesheet" href="modules/mod_jmaps/js/colorbox.css" />
	<script>if(typeof jQuery=="undefined") {document.write("\u003cscript \src=\u0022http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js\u0022>\u003c/script>");}</script>
	<script type="text/javascript" src="modules/mod_jmaps/js/jquery.jmap.min.js"></script>
	<script type="text/javascript" src="modules/mod_jmaps/js/markerclusterer_packed.js"></script>
	<script type="text/javascript" src="modules/mod_jmaps/js/mapiconmaker.min.js"></script>
	<script type="text/javascript" src="modules/mod_jmaps/js/jquery.colorbox-min.js"></script>
<?php if($jmapSOBIoneLiner == 1) { ?> 
	<script src="modules/mod_sobionelinesearch/js/jquery.hint.js"></script>
	<script src="modules/mod_sobionelinesearch/js/jquery.qtip-1.0.0.min.js"></script>
<?php }?>
   <script type="text/javascript">
		jQuery.noConflict();
	<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
		jQuery('#<?php echo $jmapDirectionsContainer; ?>').hide();
	<?php }?>
	<?php if (!empty($jmapSVOverrideContainer)) { ?>
		jQuery('#<?php echo $jmapSVOverrideContainer; ?>').hide();
	<?php }?>
		jQuery(document).ready(function(){
			jQuery('form[name~=sobiSearchFormContainer]').submit(function() {
				jQuery('#loadmessagehtml').show();
				return false;
			});
		<?php if($jmapKeepInView == 1) { ?>
			var jmapKIVstart = jQuery('#<?php echo $jmapDivID; ?>').scrollTop();
			jQuery('#jmapKeepInView').css('left', jmapKIVstart + 'px');
			var msie6 = jQuery.browser == 'msie' && jQuery.browser.version < 7;
			if (!msie6) {
				var top = jQuery('#<?php echo $jmapDivID; ?>').offset().top - parseFloat(jQuery('#<?php echo $jmapDivID; ?>').css('margin-top').replace(/auto/, 0));
				jQuery(window).scroll(function (event) {
					// what the y position of the scroll is
					var y = jQuery(this).scrollTop();
					
					// whether that's below the form
					if (y >= top) {
						// if so, add the fixed class
						jQuery('#<?php echo $jmapDivID; ?>').addClass('fixed');
					<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
						jQuery('#<?php echo $jmapDirectionsContainer; ?>').addClass('fixed');
					<?php }?>
					<?php if (!empty($jmapSVOverrideContainer)) { ?>
						jQuery('#<?php echo $jmapSVOverrideContainer; ?>').addClass('fixed');
					<?php }?>
					} else {
							// otherwise remove it
							jQuery('#<?php echo $jmapDivID; ?>').removeClass('fixed');
						<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
							jQuery('#<?php echo $jmapDirectionsContainer; ?>').removeClass('fixed');
						<?php }?>
						<?php if (!empty($jmapSVOverrideContainer)) { ?>
							jQuery('#<?php echo $jmapSVOverrideContainer; ?>').removeClass('fixed');
						<?php }?>
							}
				});
			}  
		<?php }?>
		});
		function jmaps_Initialize () {
			latLongArray = new Array();
			llIndex = 0;
        	var mapDiv = document.getElementById('<?php echo $jmapDivID; ?>');
			if(mapDiv == null) {
				return;
			}
//			console.log('JMaps Init Function - ' + mapDiv);
			jQuery(mapDiv).jmap('init', {
				'backgroundColor': '<?php echo $jmapBackgroundColor; ?>', 
				'center':[<?php echo $jmapCenterLatitude; ?>, <?php echo $jmapCenterLongitude; ?>], 
				'disableDefaultUI': false,
				'disableDoubleClickZoom': <?php echo $jmapDisableDoubleClickZoom; ?>, 
				'draggable': <?php echo $jmapDraggable; ?>, 
				'draggableCursor': null,
				'draggingCursor': null,
				'keyboardShortcuts': true,
				'mapTypeControl': <?php echo $jmapMapTypeControl; ?>, 
				'mapTypeControlTypes':['hybrid','roadmap', 'satellite', 'terrain'], 
				'mapTypeControlPosition': '<?php echo $jmapMapTypeControlPosition; ?>', 
				'mapTypeControlStyle': '<?php echo $jmapMapTypeControlStyle; ?>', 
				'mapTypeId': '<?php echo $jmapMapTypeId; ?>', 
		<?php if ($jmapMarkerClusterer) { ?>
				'maxZoom': <?php echo $jmapMMMaxZoom; ?>,
				'minZoom': <?php echo $jmapMMMinZoom; ?>,
		<?php }?>
				'noClear': false,
				'overviewMapControl': <?php echo $jmapOverviewMapControl; ?>, 
				'overviewMapControlOpened': <?php echo $jmapOverviewMapControlOpened; ?>, 
				'panControl': <?php echo $jmapPanControl; ?>, 
				'panControlPosition': '<?php echo $jmapPanControlPosition; ?>', 
				'scaleControl': <?php echo $jmapScaleControl; ?>, 
				'scaleControlPosition': '<?php echo $jmapScaleControlPosition; ?>', 
				'scrollwheel': <?php echo $jmapScrollWheel; ?>, 
				'streetViewControl': <?php echo $jmapStreetViewControl; ?>, 
				'streetViewControlPosition': '<?php echo $jmapStreetViewControlPosition; ?>', 
				'zoom':<?php echo $jmapInitZoom; ?>, 
				'zoomControl': <?php echo $jmapZoomControl; ?>, 
				'zoomControlPosition': '<?php echo $jmapZoomControlPosition; ?>', 
				'zoomControlStyle': '<?php echo $jmapZoomControlStyle; ?>', 
				'debugMode': false						
			});
		<?php if ($jmapMarkerClusterer == 1) { ?>
			var mcStyles = Array();
			mcStyles [0] = [
						{
							url: 'modules/mod_jmaps/js/images/people35.png',
							height: 35,
							width: 35,
							anchor: [16, 0],
							textColor: '#ff00ff',
							textSize: 10
						}, {
							url: 'modules/mod_jmaps/js/images/people45.png',
							height: 45,
							width: 45,
							anchor: [24, 0],
							textColor: '#ff0000',
							textSize: 11
						}, {
							url: 'modules/mod_jmaps/js/images/people55.png',
							height: 55,
							width: 55,
							anchor: [32, 0],
							textColor: '#ffffff',
							textSize: 12
						}]; 
			mcStyles [1] = [
						{
							url: 'modules/mod_jmaps/js/images/conv30.png',
							height: 27,
							width: 30,
							anchor: [3, 0],
							textColor: '#ff00ff',
							textSize: 10
						}, {
							url: 'modules/mod_jmaps/js/images/conv40.png',
							height: 36,
							width: 40,
							anchor: [6, 0],
							textColor: '#ff0000',
							textSize: 11
						}, {
							url: 'modules/mod_jmaps/js/images/conv50.png',
							width: 50,
							height: 45,
							anchor: [8, 0],
							textSize: 12
						}]; 
			mcStyles [2] = [
						{
							url: 'modules/mod_jmaps/js/images/heart30.png',
							height: 26,
							width: 30,
							anchor: [4, 0],
							textColor: '#ff00ff',
							textSize: 10
						}, {
							url: 'modules/mod_jmaps/js/images/heart40.png',
							height: 35,
							width: 40,
							anchor: [8, 0],
							textColor: '#ff0000',
							textSize: 11
						}, {
							url: 'modules/mod_jmaps/js/images/heart50.png',
							width: 50,
							height: 44,
							anchor: [12, 0],
							textSize: 12
						}];
			jQuery(mapDiv).jmap('CreateMarkerClusterer', {
				'gridSize': <?php echo $jmapClustererGridSize; ?>,
				'minimumClusterSize': <?php echo $jmapClustererMinimumClusterSize; ?>,
				'zoomOnClick': <?php echo $jmapClustererZoomOnClick; ?>,
				'averageCenter': <?php echo $jmapClustererAverageCenter; ?>,
			<?php if ($jmapClustererStyle != 'default') { ?>
				'styles': mcStyles[<?php echo $jmapClustererStyle; ?>],
			<?php } ?>
				'maxZoom': <?php echo $jmapClustererMaxZoom; ?>
			});
		<?php } ?>
            jQuery(mapDiv).animate({'width': '<?php echo $jmapWidth; ?>', 'height': '<?php echo $jmapHeight; ?>'}, function(){
            });
		}
		jmaps_Initialize ();
		function buildJmapMarkers(jmapsMarkerArray, cookieCount, iconOptions) {
			var mapDiv = parent.document.getElementById('<?php echo $jmapDivID; ?>');
			if(mapDiv == null) {
				return;
			}
//			cookieNumber = 0;
			numberOfCookies = jmapsMarkerArray.length;
			if (numberOfCookies == 0) {
				jQuery('#loadmessagehtml').hide();
			}
			var iconNumber = 0;
		  //find every Marker and build an array of the markers attributes
			jQuery(jmapsMarkerArray).each(function(index,value){
				var MapMarkerObject = jQuery(this);
//            	console.log('Jmaps AJAX markers');	
//            	console.log('Name - ' + MapMarkerObject.attr("Name"));	
//            	console.log('Addr - ' + MapMarkerObject.attr("Address"));	
//            	console.log('CatID- ' + MapMarkerObject.attr("CatID"));	
//            	console.log('CatO - ' + MapMarkerObject.attr("CatOrdering"));	
//            	console.log('HTML - ' + MapMarkerObject.attr("HTML"));	
           	console.log('Lati - ' + MapMarkerObject.attr("Latitude"));	
            	console.log('Long - ' + MapMarkerObject.attr("Longitude"));	
//            	console.log('Feat - ' + MapMarkerObject.attr("Featured"));	
//            	console.log('ILab - ' + MapMarkerObject.attr("IconLabel"));	
//            	console.log('iImg - ' + MapMarkerObject.attr("icomImage"));	
//            	console.log('minZ - ' + MapMarkerObject.attr("minZoom"));	
//            	console.log('maxZ - ' + MapMarkerObject.attr("maxZoom"));	

				var mapCatID = MapMarkerObject.attr("CatID");
				var showMarkerLabel = <?php echo $jmapShowMarkerLabel; ?>;
				var featuredListing = MapMarkerObject.attr("Featured");
				
				var centerNow = 0;
				var markerIcon = baseMarker;
				if (categoryMarker[mapCatID]) {
					markerIcon = categoryMarker[mapCatID];
				}
				if (MapMarkerObject.attr("iconImage")) {
					if (MapMarkerObject.attr("iconImage").indexOf("#") != -1) {
						markerIcon = MapMarkerObject.attr("iconImage");
					} else {
							testIcon = MapMarkerObject.attr("iconImage");
							if (customMarkers[testIcon]) {
								markerIcon = MapMarkerObject.attr("iconImage");
							}
							}
				}
				if (featuredListing == 1) {
//					alert('featuredMarkerName = ' + featuredMarkerName);
					if (featuredMarker) {
						markerIcon = featuredMarker;
					}
				}
				if (markerIcon.indexOf("#") == -1) {
					myIcon = {};
					myIcon.markerImage = customMarkers[markerIcon].markerImage
					myIcon.markerShadow = customMarkers[markerIcon].markerShadow
					myIcon.shape = customMarkers[markerIcon].shape
				} else {
						iconOptions.primaryColor = markerIcon;
						iconOptions.strokeColor = "<?php echo $jmapMarkerStroke; ?>";
						iconOptions.labelColor = "<?php echo $jmapMarkerLabel; ?>";
						iconOptions.width = <?php echo $jmapMarkerWidth; ?>;
						iconOptions.height = <?php echo $jmapMarkerHeight; ?>;
						iconOptions.addStar = false;
						var featuredListing = MapMarkerObject.attr("Featured");
						if (featuredListing == 1) {
							iconOptions.strokeColor = "<?php echo $jmapFeaturedMarkerStroke; ?>";
							iconOptions.labelColor = "<?php echo $jmapFeaturedMarkerLabel; ?>";
							iconOptions.addStar = <?php echo $jmapShowStar; ?>;
							iconOptions.starPrimaryColor = "<?php echo $jmapStarColor; ?>";
							iconOptions.starStrokeColor = "<?php echo $jmapStarStroke; ?>";
						}
						var showMarkerLabel = <?php echo $jmapShowMarkerLabel; ?>;
						if (showMarkerLabel == 1 && MapMarkerObject.attr("IconLabel") != 0) {
							iconOptions.label = MapMarkerObject.attr("IconLabel");
						}
//       				console.log("$iconNum = " + MapMarkerObject.attr("IconLabel"));
						var centerOnMarker1 = <?php echo $jmapOverrideCenter; ?>;
					<?php if ($jmapZoom != 0) { ?>
						if (MapMarkerObject.attr("IconLabel") == "1" && centerOnMarker1 == 1) {
//	            						console.log('Centering');	
							centerNow = 1;
						}
					<?php }?>
						if (showMarkerLabel == 1 || (iconOptions.width == 32 && iconOptions.height == 32)) {
							var myIcon = MapIconMaker.createLabeledMarkerIcon(iconOptions);            
			 //         	iconOptions.label = "1";
						} else {
								var myIcon = MapIconMaker.createMarkerIcon(iconOptions);
								}
						}
//Place associated listing marker
				if (MapMarkerObject.attr("IconLabel") != 0) {
					var iconElement = null;
					var entryIndex = MapMarkerObject.attr("IconLabel") - 1;
//					console.log('Index = ' + entryIndex + 'iconNumber = ' + iconNumber);
				<?php if (!empty($jmapMarkerClass)) { ?>
					iconElement = jQuery('.<?php echo $jmapMarkerClass; ?>:eq(' + entryIndex + ')');
				<?php } ?>
				<?php if (($jmapSource == 'SOBI2ajax' || $jmapSource == 'SOBI2cat') && empty($jmapMarkerClass)) { ?>
					iconElement = jQuery('table.sobi2Listing:last').find('td:eq(' + entryIndex + ')');
				<?php } ?>				
				<?php if (($jmapSource == 'SOBIproSearch' || $jmapSource == 'SOBIproCat') && empty($jmapMarkerClass)) { ?>
					iconElement = jQuery('.spEntriesListContainer:last').find('.spEntriesListTitle:eq(' + entryIndex + ')');
				<?php } ?>
				<?php if ($jmapSource == 'cb' && empty($jmapMarkerClass)) { ?>
					iconElement = jQuery('.cbUserListCol1:eq(' + entryIndex + ')');
				<?php } ?>
					if (iconElement != null) {
						jQuery(iconElement).prepend('<img src="' + myIcon.markerImage.url + '" />');
					}
				}
//Set category priority
			<?php if ($jmapCategoryPriority == 1) { ?>
				var catZindex = 0 - (mapCatID * 1100);
				if (featuredListing == 1) {
					catZindex = catZindex + 100;
				}
			<?php }?>
//Check for latitude and longitude and place the map marker
				if (MapMarkerObject.attr("Latitude") == 0 || MapMarkerObject.attr("Longitude") == 0) {
//					console.log('cookie = ' + cookieNumber + ' Count = ' + cookieCount);
//					console.log('Address');
					jQuery(mapDiv).jmap('SearchAddress', {
						'query': jQuery(this).attr('Address'),
						'returnType': 'getLocations'
					}, 	function(result, status, options) {
							var valid = Mapifies.SearchCode(status);
							cookieNumber++;
							if (valid.success) {
								jQuery.each(result, function(i, point){
//Build map directions
									mapDirections = '';
									toDirections = '';
									fromDirections = '';
									<?php if ($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) { ?> 
										<?php if ($jmapShowToDirections == 1) { ?>
									toDirections = '<div class="toHereDiv" style="display:none;"><a href="#" class="toHereLink"><?php echo JText::_('JMAPS_DIRECTIONS'); ?>: <strong><?php echo JText::_('JMAPS_TO_HERE'); ?></strong></a><div class="toHere" style="display:none;"><form action="#" onSubmit="showDirections(this.saddr.value, this.daddr.value); return false;"><span class="gmapDirHead" id="gmapDirHeadTo"><?php echo JText::_('JMAPS_ENTER_START_ADDRESS'); ?></span><p class="gmapDirItem" id="gmapDirItemTo"><label for="gmapDirSaddr" class="gmapDirLabel" id="gmapDirLabelTo"><?php echo JText::_('JMAPS_START_ADDRESS'); ?>: (<?php echo JText::_('JMAPS_ADDRESS'); ?>, <?php echo JText::_('JMAPS_CITY'); ?> <?php echo JText::_('JMAPS_STATE_REGION'); ?>)<br /></label><input type="text" size="30" maxlength="100" name="saddr" class="gmapTextBox" id="gmapDirSaddr" value=""<span class="gmapDirBtns" id="gmapDirBtnsTo"><br /><input value="<?php echo JText::_('JMAPS_GET_DIRECTIONS'); ?>" type="submit" class="gmapDirButton" id="gmapDirButtonTo"  style="font-size: 1.0em;"/></span></p><input type="hidden" name="daddr" value="' + MapMarkerObject.attr("Address") + '(' + MapMarkerObject.attr("Name") + ')" /></form></div></div>';
										<?php }?>
										<?php if ($jmapShowFromDirections == 1) { ?>
									fromDirections = '<div class="fromHereDiv" style="display:none;"><a href="#" class="fromHereLink"><?php echo JText::_('JMAPS_DIRECTIONS'); ?>: <strong><?php echo JText::_('JMAPS_FROM_HERE'); ?></strong></a><div class="fromHere" style="display:none;"><form action="#" onSubmit="showDirections(this.saddr.value, this.daddr.value); return false;"><span class="gmapDirHead" id="gmapDirHeadFrom"><?php echo JText::_('JMAPS_ENTER_DESTINATION_ADDRESS'); ?></span><p class="gmapDirItem" id="gmapDirItemFrom"><label for="gmapDirSaddr" class="gmapDirLabel" id="gmapDirLabelFrom"><?php echo JText::_('JMAPS_END_ADDRESS'); ?>: (<?php echo JText::_('JMAPS_ADDRESS'); ?>, <?php echo JText::_('JMAPS_CITY'); ?> <?php echo JText::_('JMAPS_STATE_REGION'); ?>)<br /></label><input type="text" size="30" maxlength="100" name="daddr" class="gmapTextBox" id="gmapDirSaddr" value=""/><span class="gmapDirBtns" id="gmapDirBtnsFrom"><br /><input value="<?php echo JText::_('JMAPS_GET_DIRECTIONS'); ?>" type="submit" class="gmapDirButton" id="gmapDirButtonFrom"  style="font-size: 1.0em;"/></span></p><input type="hidden" name="saddr" value="' + MapMarkerObject.attr("Address") + '(' + MapMarkerObject.attr("Name") + ')" /></form></div></div>';
										
										<?php }?>
									mapDirections = '<div class="directionsDiv" style="font-size: .75em; min-height:10px;"><a href="#" class="toFromDirections" onclick="toggleDirections();  return false;">Click For Directions | Click For Info</a>' + toDirections + fromDirections + '</div><br />';
									<?php }?>
					//				console.log('mapDirections = ' + mapDirections);
									iconNumber++;
									var latitude = point.geometry.location.lat();
									var longitude = point.geometry.location.lng();
//									console.log('Address - Lat = ' + latitude + ' Lng = ' + longitude + ' Address = ' + MapMarkerObject.attr('Address'));
									mapLatitude = latitude;
									mapLongitude = longitude;
									jQuery(mapDiv).jmap('AddMarker',{
										'pointAnimation': '<?php echo $jmapMarkerPointAnimation; ?>',
										'pointClickable': <?php echo $jmapMarkerPointClickable; ?>,
										'pointCursor': null,
										'pointIsDraggable': false,
										'pointIsFlat': false,
										'pointLatLng': [mapLatitude, mapLongitude],
										'pointRaiseOnDrag': <?php echo $jmapMarkerPointRaiseOnDrag; ?>,
										'pointOpenHTMLEvent': '<?php echo $jmapMarkerPointOpenHTMLEvent; ?>',
										'pointTitle' : '<?php echo JText::_('JMAPS_CLICK_MARKER'); ?>',
										'pointIsVisible': true,
									<?php if ($jmapCategoryPriority == 1) { ?>
										'zIndex': catZindex,
									<?php }?>
										'pointHTML': '<div class="markerHTMLdiv" style="font-size: 1.25em; min-height:100px;">' + MapMarkerObject.attr("HTML")<?php if ($jmapShowStreetView == 1) { ?> + '<p><a href="#" onClick="addStreetView(' + mapLatitude + ', ' + mapLongitude + '); return false"><?php echo JText::_('JMAPS_ADD_STREETVIEW'); ?></a></p>'<?php } ?> + '</div>' + mapDirections,
										'maxWidth': <?php echo $jmapMarkerMaxWidth; ?>,
										'offsetWidth': null,
										'offsetHeight': null,
										'offsetUnit': null,
										'centerMap': centerNow,
										'centerMoveMethod':'normal',
										'disableAutoPan': <?php echo $jmapMarkerDisableAutoPan; ?>,
										'pointIcon': myIcon.markerImage,
										'pointShadow': myIcon.markerShadow,
										'pointShape': myIcon.shape
									});
//									console.log('Marker Added - Address!');
									if (cookieNumber == numberOfCookies) {
								<?php if ($jmapZoom == 0) { ?>
										zoomCenterMap();
								<?php }?>
										jQuery('#loadmessagehtml').hide();
//										console.log('Zoom and Center Map for ' + iconNumber + ' markers!');
									}
								});
							};	
						});
				} else {
//Build map directions
						mapDirections = '';
						toDirections = '';
						fromDirections = '';
						<?php if ($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) { ?> 
							<?php if ($jmapShowToDirections == 1) { ?>
						toDirections = '<div class="toHereDiv" style="display:none;"><a href="#" class="toHereLink"><?php echo JText::_('JMAPS_DIRECTIONS'); ?>: <strong><?php echo JText::_('JMAPS_TO_HERE'); ?></strong></a><div class="toHere" style="display:none;"><form action="#" onSubmit="showDirections(this.saddr.value, this.daddr.value); return false;"><span class="gmapDirHead" id="gmapDirHeadTo"><?php echo JText::_('JMAPS_ENTER_START_ADDRESS'); ?></span><p class="gmapDirItem" id="gmapDirItemTo"><label for="gmapDirSaddr" class="gmapDirLabel" id="gmapDirLabelTo"><?php echo JText::_('JMAPS_START_ADDRESS'); ?>: (<?php echo JText::_('JMAPS_ADDRESS'); ?>, <?php echo JText::_('JMAPS_CITY'); ?> <?php echo JText::_('JMAPS_STATE_REGION'); ?>)<br /></label><input type="text" size="30" maxlength="100" name="saddr" class="gmapTextBox" id="gmapDirSaddr" value=""<span class="gmapDirBtns" id="gmapDirBtnsTo"><br /><input value="<?php echo JText::_('JMAPS_GET_DIRECTIONS'); ?>" type="submit" class="gmapDirButton" id="gmapDirButtonTo"  style="font-size: 1.0em;"/></span></p><input type="hidden" id="toEnd" name="daddr" value="' + MapMarkerObject.attr("Address") + '(' + MapMarkerObject.attr("Name") + ')" /></form></div></div>';
							<?php }?>
							<?php if ($jmapShowFromDirections == 1) { ?>
						fromDirections = '<div class="fromHereDiv" style="display:none;"><a href="#" class="fromHereLink"><?php echo JText::_('JMAPS_DIRECTIONS'); ?>: <strong><?php echo JText::_('JMAPS_FROM_HERE'); ?></strong></a><div class="fromHere" style="display:none;"><form action="#" onSubmit="showDirections(this.saddr.value, this.daddr.value); return false;"><span class="gmapDirHead" id="gmapDirHeadFrom"><?php echo JText::_('JMAPS_ENTER_DESTINATION_ADDRESS'); ?></span><p class="gmapDirItem" id="gmapDirItemFrom"><label for="gmapDirSaddr" class="gmapDirLabel" id="gmapDirLabelFrom"><?php echo JText::_('JMAPS_END_ADDRESS'); ?>: (<?php echo JText::_('JMAPS_ADDRESS'); ?>, <?php echo JText::_('JMAPS_CITY'); ?> <?php echo JText::_('JMAPS_STATE_REGION'); ?>)<br /></label><input type="text" size="30" maxlength="100" name="daddr" class="gmapTextBox" id="gmapDirSaddr" value=""/><span class="gmapDirBtns" id="gmapDirBtnsFrom"><br /><input value="<?php echo JText::_('JMAPS_GET_DIRECTIONS'); ?>" type="submit" class="gmapDirButton" id="gmapDirButtonFrom"  style="font-size: 1.0em;"/></span></p><input type="hidden" id="fromEnd" name="saddr" value="' + MapMarkerObject.attr("Address") + '(' + MapMarkerObject.attr("Name") + ')" /></form></div></div>';
							
							<?php }?>
						mapDirections = '<div class="directionsDiv" style="font-size: .75em; min-height:10px;"><a href="#" class="toFromDirections" onclick="toggleDirections();  return false;">Click For Directions | Click For Info</a>' + toDirections + fromDirections + '</div><br />';
						<?php }?>
//						console.log('mapDirections = ' + mapDirections);
						mapLatitude = MapMarkerObject.attr("Latitude");
						mapLongitude = MapMarkerObject.attr("Longitude");
//						console.log('Object - Lat = ' + mapLatitude + ' Lng = ' + mapLongitude + ' Address = ' + MapMarkerObject.attr('Address'));
  						cookieNumber++;
						iconNumber++;
						jQuery(mapDiv).jmap('AddMarker',{
							'pointAnimation': '<?php echo $jmapMarkerPointAnimation; ?>',
							'pointClickable': <?php echo $jmapMarkerPointClickable; ?>,
							'pointCursor': null,
							'pointIsDraggable': false,
							'pointIsFlat': false,
							'pointLatLng': [mapLatitude, mapLongitude],
							'pointRaiseOnDrag': <?php echo $jmapMarkerPointRaiseOnDrag; ?>,
							'pointOpenHTMLEvent': '<?php echo $jmapMarkerPointOpenHTMLEvent; ?>',
							'pointTitle' : '<?php echo JText::_('JMAPS_CLICK_MARKER'); ?>',
							'pointIsVisible': true,
						<?php if ($jmapCategoryPriority == 1) { ?>
							'zIndex': catZindex,
						<?php }?>
							'pointHTML': '<div class="markerHTMLdiv" style="font-size: 1.25em; min-height:100px; overflow:hidden;">' + MapMarkerObject.attr("HTML")<?php if ($jmapShowStreetView == 1) { ?> + '<p><a href="#" onClick="addStreetView(' + mapLatitude + ', ' + mapLongitude + '); return false"><?php echo JText::_('JMAPS_ADD_STREETVIEW'); ?></a></p>'<?php } ?> + '</div>' + mapDirections,
							'maxWidth': <?php echo $jmapMarkerMaxWidth; ?>,
							'offsetWidth': null,
							'offsetHeight': null,
							'offsetUnit': null,
							'centerMap': centerNow,
							'centerMoveMethod':'normal',
							'disableAutoPan': <?php echo $jmapMarkerDisableAutoPan; ?>,
							'pointIcon': myIcon.markerImage,
							'pointShadow': myIcon.markerShadow,
							'pointShape': myIcon.shape
						});
//						console.log('Marker Added - Lat/Long!');
						if (cookieNumber == numberOfCookies) {
					<?php if ($jmapZoom == 0) { ?>
							zoomCenterMap();
					<?php }?>
							jQuery('#loadmessagehtml').hide();
//							console.log('Zoom and Center Map for ' + iconNumber + ' markers!');
						}
						}
//				console.log('cookie = ' + cookieNumber + ' Count = ' + cookieCount);
//				console.log('Lat/Long');						iconNumber++;
			});
	<?php if ($jmapFeed != 'none') { ?>
			jQuery(mapDiv).jmap('AddFeed', {
				'feedUrl':'<?php echo $jmapFeed; ?>',
				'mapCenter': []
			});               
	<?php }?>
	<?php if ($jmapSOBIdetailsBox == 1) { ?>
			parent.jQuery(document).ready(function(){
				if(!parent.jQuery.browser.opera) {
					parent.jQuery(".sobi2ItemTitle a").colorbox({width:"<?php echo $shadowboxWidth; ?>", height:"<?php echo $shadowboxHeight; ?>", iframe:true});
					parent.jQuery(".spEntriesListTitle a").colorbox({width:"<?php echo $shadowboxWidth; ?>", height:"<?php echo $shadowboxHeight; ?>", iframe:true});
			<?php if ($jmapSOBIdetailsBoxTmpl == 1) { ?>
						alterDetailLink();
			<?php }?>
					return false;
				}
			});
	<?php }?>
		};

		function zoomCenterMap() {
			var mapDiv = parent.document.getElementById('<?php echo $jmapDivID; ?>');
			var element = jQuery(mapDiv);
			var thisMap = Mapifies.MapObjects.Get(element);
			var thisMarkers = Mapifies.MapObjects.GetMarkers(element);
			var latlngbounds = new google.maps.LatLngBounds();
			var thisMarkers = Mapifies.MapObjects.GetMarkers(element);
			jQuery.each(thisMarkers, function (index, thisMarker) {
				latlngbounds.extend(thisMarker.position);
			});
			thisMap.fitBounds(latlngbounds); 
			var zoomLevel = thisMap.getZoom();
			if (zoomLevel > <?php echo $jmapMinZoom; ?>) {
				zoomLevel = <?php echo $jmapMinZoom; ?>;
				thisMap.setZoom(zoomLevel); 
			}
//			console.log('New Center = ' + thisMap.getCenter() + ' zoomLevel = ' + zoomLevel);
		};
		function addStreetView(jmapsLat, jmapsLong) {
			var mapDiv = parent.document.getElementById('<?php echo $jmapDivID; ?>');
		<?php if (!empty($jmapSVOverrideContainer)) { ?>
			var streetDiv = parent.document.getElementById('<?php echo $jmapSVOverrideContainer; ?>');
		<?php }?>
			var element = jQuery(mapDiv);
			var thisMap = Mapifies.MapObjects.Get(element);
			var jmapsMarkerLatLong = [jmapsLat, jmapsLong];
//			console.log('addStreetView - 1');
			jQuery(mapDiv).jmap('CreateStreetViewPanorama', {
				'addressControl': <?php echo $jmapSVAddressControl; ?>,
				'addressControlPosition': '<?php echo $jmapSVAddressControlPosition; ?>',
				'disableDoubleClickZoom': <?php echo $jmapSVDisableDoubleClickZoom; ?>,
				'enableCloseButton': true,
				'linksControl': true,
				'panControl': <?php echo $jmapSVPanControl; ?>,
				'panControlPosition': '<?php echo $jmapSVPanControlPosition; ?>',
				'pano': null,
				'panoProvider': null,
				'overideContainer': '<?php echo $jmapSVOverrideContainer; ?>',
				'latlng':jmapsMarkerLatLong,
				'povControl': false,
				'povHeading': 0,
				'povPitch': 0,
				'povZoom': 1,
				'scrollwheel': <?php echo $jmapSVScrollWheel; ?>,
				'visible': true,
				'zoomControl': <?php echo $jmapSVScrollWheel; ?>,
				'zoomControlPosition': '<?php echo $jmapSVZoomControlPosition; ?>',
				'zoomControlStyle': '<?php echo $jmapSVZoomControlStyle; ?>'
			});
		<?php if (!empty($jmapSVOverrideContainer)) { ?>
			jQuery(streetDiv).show();
		<?php }?>
			return false;
		}
	<?php if (!empty($jmapSVOverrideContainer)) { ?>
		function removeStreetView() {
			var mapDiv = parent.document.getElementById('<?php echo $jmapDivID; ?>');
			var streetDiv = parent.document.getElementById('<?php echo $jmapSVOverrideContainer; ?>');
//			console.log('removeStreetView - 1');
			jQuery(streetDiv).hide();
			return false;
		}
	<?php }?>
		function openColorbox(linkData) {
//			console.log('openColorbox');
			<?php if ($jmapSOBIdetailsBox == 1) { ?>
				if (linkData.indexOf("tmpl=")==false) {
				<?php if ($jmapSOBIdetailsBoxTmpl == 1) { ?>
					<?php if ($jmapSOBIdetailsBoxTmplSEF == 1) { ?>
						linkData = linkData + '?tmpl=component';
					<?php } else { ?>
								linkData = linkData + '&tmpl=component';
							<?php }?>
				}
				<?php }?>
			if(!parent.jQuery.browser.opera) {
				jQuery.fn.colorbox({href:linkData, width:"<?php echo $shadowboxWidth; ?>", height:"<?php echo $shadowboxHeight; ?>", iframe:true, open:true});
				return false;
			}
			<?php }?>
			return true;
		}
		function alterDetailLink() {
			jQuery(".sobi2ItemTitle a").each(function() { 
			<?php if ($jmapSOBIdetailsBoxTmplSEF == 1) { ?>
				this.href = this.href + '?tmpl=component';
			<?php } else { ?>
						this.href = this.href + '&tmpl=component';
					<?php } ?>
				this.target = '_blank';
			   });
			parent.jQuery(".spEntriesListTitle a").each(function() { 
//				console.log('Found link');
				if (this.href.indexOf("tmpl=")>0) {
//					console.log('Not updating link');
					this.target = '_blank';
				} else {
//						console.log('Updating link');
			<?php if ($jmapSOBIdetailsBoxTmplSEF == 1) { ?>
//						console.log('Adding with ?');
						this.href = this.href + '?tmpl=component';
			<?php } else { ?>
//						console.log('Adding with &');
						this.href = this.href + '&tmpl=component';
					<?php } ?>
						this.target = '_blank';
						}
			   });
			return false;
		}
		function toggleDirections() {
//			console.log('toggleDirections');
	<?php if ($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) { ?> 
			parent.jQuery('.markerHTMLdiv').toggle();
		<?php if ($jmapShowToDirections == 1) { ?>
			parent.jQuery('.toHereDiv').toggle();
			parent.jQuery(".toHereLink").click(function() {
				parent.jQuery('.toHere').toggle();
			<?php if ($jmapShowFromDirections == 1) { ?>
				parent.jQuery('.fromHere').hide();			
			<?php }?>
				return false;
			});
		<?php }?>
		<?php if ($jmapShowFromDirections == 1) { ?>
			parent.jQuery('.fromHereDiv').toggle();
			parent.jQuery(".fromHereLink").click(function() {
				parent.jQuery('.fromHere').toggle();
			<?php if ($jmapShowToDirections == 1) { ?>
				parent.jQuery('.toHere').hide();			
			<?php }?>
				return false;
			});
		<?php }?>
	<?php }?>
			return false;
		}
		
		function showDirections(start, end) {
			var mapDiv = parent.document.getElementById('<?php echo $jmapDivID; ?>');
			var element = jQuery(mapDiv);
			var thisMap = Mapifies.MapObjects.Get(element);
		<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
			var directionsDiv = parent.document.getElementById('<?php echo $jmapDirectionsContainer; ?>');
		<?php }?>
			var element = jQuery(mapDiv);
			var request = {
				origin:start, 
				destination:end,
				travelMode: google.maps.DirectionsTravelMode.DRIVING
			};
//			directionsService.route(request, function(response, status) {
//				if (status == google.maps.DirectionsStatus.OK) {
//					directionsDisplay.setDirections(response);
//				}
//			});
			jQuery(mapDiv).jmap('SearchDirections', {
		<?php if($jmapDirectionsAvoidHighways == '1') { ?>
				'avoidHighways': true,
		<?php }?>
		<?php if($jmapDirectionsAvoidTolls == '1') { ?>
				'avoidTolls': true,
		<?php }?>
				'destination': end,
				'origin': start,
		<?php if($jmapDirectionsAlternativeRoutes == '1') { ?>
				'provideRouteAlternatives': true,
		<?php }?>
				'panel': '<?php echo $jmapDirectionsContainer; ?>'
				}, 	function(result, status, options) {
					var valid = Mapifies.SearchCode(status);
					if (valid.success) {
						jQuery.each(result, function(i, directions){
//							var latitude = point.geometry.location.Ka;
//							var longitude = point.geometry.location.La;
//							console.log('Address - Lat = ' + latitude + ' Lng = ' + longitude + ' Address = ' + '128 Eastern Fork, Longwood, FL. 32750');
//							console.log('Directions Copyright = ' + directions.copyrights);
						});
					};	
				});
		<?php if (($jmapShowToDirections == 1 || $jmapShowFromDirections == 1) && !empty($jmapDirectionsContainer)) { ?>
			jQuery(directionsDiv).show();
		<?php }?>
				return false;
		}

<?php if($jmapSource != 'SOBI2ajax' && $jmapSource != 'SOBIproSearch') { ?> 
		var MapMarkerCustomArray = new Array();
		<?php 
			if (!empty($markerCustomArrayCatID)) {
				for ($i=0;$i<count($markerCustomArrayCatID);++$i) { ?>
					MapMarkerCustomArray['<?php echo $markerCustomArrayCatID[$i] ?>'] = '<?php echo $markerCustomArrayCatID[$i]; ?>';
		<?php 
				}
			}
		?>
        jQuery(document).ready(function(){
//			console.log('About to INIT');	
//			jmaps_Initialize();
			console.log('About to get XML');	
			var xmlCount = 0;
			var jmapsMarkersBuilt = 0;
			var jmapsMarkerArray = Array();
			<?php for ($j=0;$j<count($jmapXMLname);++$j) { ?>
	
			jQuery.ajax({
				type: "GET",
				url: "<?php echo $jmapXMLname[$j]; ?>",
				dataType: "xml",
				success: parseXML,
				error:function (xhr, ajaxOptions, thrownError){
				<?php if ($jmapFeed != 'none') { ?>
					jQuery('#<?php echo $jmapDivID; ?>').jmap('AddFeed', {
						'feedUrl':'<?php echo $jmapFeed; ?>',
						'mapCenter': []
					});
				<?php }?>
				}
			});
            <?php } ?>
            function parseXML(xml) {
//find every Marker and build an array of the markers attributes
				console.log('Parse');
                xmlCount++;
                jQuery(xml).find('sgmMarker').each(function() {
                    var MapMarkerObject = jQuery(this);
					jmapsMarkerArray[jmapsMarkersBuilt] = MapMarkerObject;
					jmapsMarkersBuilt++;
				});
				if(xmlCount == <?php echo count($jmapXMLname); ?>) {
					if(jmapsMarkersBuilt != 0) {
						var iconOptions = new Object;
						buildJmapMarkers(jmapsMarkerArray, jmapsMarkersBuilt, iconOptions);
					} else {
						<?php if ($jmapFeed != 'none') { ?>
							jQuery('#<?php echo $jmapDivID; ?>').jmap('AddFeed', {
								'feedUrl':'<?php echo $jmapFeed; ?>',
								'mapCenter': []
							});
						<?php }?>
//							console.log('Hide');
							jQuery('#loadmessagehtml').hide();
							}
				}
			}
		});
<?php } ?>

	</script>