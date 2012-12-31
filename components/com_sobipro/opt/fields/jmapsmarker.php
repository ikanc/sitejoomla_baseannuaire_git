<?php
/**
 * @version: $Id: jmapsmarker.php Cindy Johnson $
 * @package: Jmaps Marker Field Application
 * ===================================================
 * @author
 * Name: Cindy Johnson, websolutionware.com
 * Email: cindy[at]websolutionware.com
 * Url: http://www.websolutionware.com
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 websolutionware.com (http://www.websolutionware.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-06-17 11:59:04 +0200 (Sat, 09 June 2012) $
 * $Revision: 1100 $
 * $Author: Cindy Johnson $
 */
defined('SOBIPRO') || exit('Restricted access');
SPLoader::loadClass('opt.fields.inbox');
class SPField_JmapsMarker extends SPField_Inbox implements SPFieldInterface {
	/**
	 * @var string
	 */
 	protected $jmfaDefaultMarkerColor = '';
	/**
	 * @var string
	 */
 	protected $jmfaDefaultMarkerImage = '';
	/**
	 * @var int
	 */
	protected $jmfaMarkersPerCookie =  15;
	/**
	 * @var String
	 */
	protected $jmfaTitleField =  'field_title';
	/**
	 * @var String
	 */
	protected $jmfaStreetField =  'field_street';
	/**
	 * @var String
	 */
	protected $jmfaCityField =  'field_city';
	/**
	 * @var String
	 */
	protected $jmfaStateField =  'field_state';
	/**
	 * @var String
	 */
	protected $jmfaCountryField =  'field_country';
	/**
	 * @var String
	 */
	protected $jmfaPostcodeField =  'field_zip';
	/**
	 * @var String
	 */
	protected $jmfaFeaturedField =  'field_featured';
	/**
	 * @var String
	 */
	protected $jmfaCustomField1 =  'field_custom1';
	/**
	 * @var String
	 */
	protected $jmfaCustomField2 =  'field_custom2';
	/**
	 * @var String
	 */
	protected $jmfaCustomField3 =  'field_custom3';
	/**
	 * @var String
	 */
	protected $jmfaCustomField4 =  'field_custom4';
	/**
	 * @var String
	 */
	protected $jmfaCustomField5 =  'field_custom5';
	/**
	 * @var array
	 */
	protected $jmfaBubbleLayout = '';
	/**
	 * @var int
	 */
	protected $determineLocation = 1;
	/**
	 * @var boolean
	 */
	private $_firstsw = 1;
	/**
	 * @var string
	 */
	private $_data = array();

	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr() {
		$attr = get_class_vars(__CLASS__);
		unset( $attr['_attr']);
		unset( $attr['_selected']);
		return array_keys($attr);
	}

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field($return = false) {
		if(!($this->enabled)) {
			return false;
		}
		SPLang::load('SpApp.jmapsmarker');
		if($this->determineLocation) {
			SPFactory::header()->addJsFile('jquery');
			SPFactory::header()->addJsFile('jmapsmarker');
			SPFactory::header()->addJsUrl('http://maps.google.com/maps/api/js?sensor=true');
			//LGW
			//SPFactory::header()->addJsUrl('http://code.google.com/apis/gears/gears_init.js');
		} else {
				SPFactory::header()->addJsFile('jquery');
				SPFactory::header()->addJsFile('jmapsmarker');
				SPFactory::header()->addJsUrl('http://maps.google.com/maps/api/js?sensor=true');
				}

		$data = $this->getData(true);
		$jmlatitude = null;
		$jmlongitude = null;
		$jmcolor = null;
		$jmimage = null;
		if(isset($data['jmlatitude']) && isset($data['jmlongitude'])) {
			$jmlatitude = $data['jmlatitude'];
			$jmlongitude = $data['jmlongitude'];
		} else {
				$sp = array('Latitude'=>0, 'Longitude'=>0, 'Marker'=>false);
				}
		if(isset($data['jmcolor'])) {
			$jmcolor = $data['jmcolor'];
		}
		if(isset($data['jmimage'])) {
			$jmimage = $data['jmimage'];
		}

		if ($jmimage == null && $jmcolor == null) {
			if (isset($this->jmfaDefaultMarkerImage)) {
				$jmimage = $this->jmfaDefaultMarkerImage;
			} elseif (isset($this->jmfaDefaultMarkerColor)) {
						$jmcolor = $this->jmfaDefaultMarkerColor;
					}
		}
		
		$options = array();
		$options['Id'] = $this->nid;
		$options['Sensor'] = $this->determineLocation;
		$options['Fields'] = array();
		
		$i = 0;
		if(isset($this->jmfaStreetField)) {
			$options['Fields'][$i] = trim($this->jmfaStreetField);
			$i++;
		}
		if(isset($this->jmfaCityField)) {
			$options['Fields'][$i] = trim($this->jmfaCityField);
			$i++;
		}
		if(isset($this->jmfaStateField)) {
			$options['Fields'][$i] = trim($this->jmfaStateField);
			$i++;
		}
		if(isset($this->jmfaPostcodeField)) {
			$options['Fields'][$i] = trim($this->jmfaPostcodeField);
			$i++;
		}
		if(isset($this->jmfaCountryField)) {
			$options['Fields'][$i] = trim($this->jmfaCountryField);
			$i++;
		}
		$options['ChngMsg'] = Sobi::Txt('JMFA_JS_REWRITE_ADJ_CONFIRM');
		$options = json_encode($options);
		if($this->isFree || !($this->fee)) {
			SPFactory::header()->addJsCode("SPJmapsMarkerGeoCoder({$options}, false);");
		} else {
				SPFactory::header()->addJsCode("SPJmapsMarkerGeoCoder({$options}, '{$this->nid}Payment');");
				}
        if (!DS) {
			define( 'DS', DIRECTORY_SEPARATOR );
		}
        $markerDirectory = '..' . DS . 'media' . DS . 'markers';
		if (is_dir($markerDirectory)) {
			$directoryWork = scandir($markerDirectory, 1);
		} else {
				$markerDirectory = 'media' . DS . 'markers';
				if (is_dir($markerDirectory)) {
					$directoryWork = scandir($markerDirectory, 1);
				}
				}
		$markerList = array();
		$markerOptions = "NONE=translate:[JMFA_SELECT_MARKER]";
		for ($i=0; $i<count($directoryWork); $i++) {
			if ($directoryWork[$i] == '.' || $directoryWork[$i] == '..') {
				continue;
			}
			$directoryName = $markerDirectory. DS .$directoryWork[$i];
			if (!is_dir($directoryName)) {
				continue;
			}
		//	echo '<br /><br />$directoryWork[$i] = ' . $directoryWork[$i];
			$markerList[] = $directoryWork[$i];
		}
		//echo '<br /><br />';
		//print_r($markerList);
		for ($i=0; $i<count($markerList); $i++) {
			$markerOptions .= ', ' . $markerList[$i] . '=' . $markerList[$i];
		}
		//$markerOptions .= "'";
		$class = $this->required ? $this->cssClass.' required' : $this->cssClass;
		$field = null;
		
		//LGW : todo : set as field form parameters!
		$this->formWidth = 90;//%
		$this->formHeight = 350;
		
		$field .= "\n<div style=\"width:{$this->formWidth}%; height:{$this->formHeight}px;\" id=\"{$this->nid}_canvas\" class=\"{$class}\">\n";
		$field .= "</div>\n";
		
		$field .= '<span class="SPJmapsmarkerLabel SPJmapsmarkerLatitude">'.Sobi::Txt('JMFA_FORM_JMLATITUDE').'</span>';
		$field .= SPHtml_Input::text($this->nid.'_jmlatitude', $jmlatitude, array('id' => $this->nid.'_jmlatitude', 'class' => $class));
		$field .= '<span class="SPJmapsmarkerLabel SPJmapsmarkerLongitude">'.Sobi::Txt('JMFA_FORM_JMLONGITUDE').'</span>';
		$field .= SPHtml_Input::text($this->nid.'_jmlongitude', $jmlongitude, array('id' => $this->nid.'_jmlongitude', 'class' => $class));
		//LGW
		$field .= '<div style="display:none">';
		
		$field .= '<br /><span id="SPJmapsmarkerLabel"><input name="SPJmapsmarkerSetLatLong" type="button" id="SPJmapsmarkerSetLatLong" value="'.Sobi::Txt('JMFA_FORM_JMBUTTON').'"></span>';
		$field .= '<br /><span class="SPJmapsmarkerLabel SPJmapsmarkerColor">'.Sobi::Txt('JMFA_FORM_JMCOLOR').'</span>';
		$field .= SPHtml_Input::text($this->nid.'_jmcolor', $jmcolor, array('id' => $this->nid.'_jmcolor', 'class' => $class));
		

/*
		Code to build dropdown select for custom marker images from com_sobipro\opt\fields\url.php
*/
		$field .= '<br /><span class="SPJmapsmarkerLabel SPJmapsmarkerJimage">'.Sobi::Txt('JMFA_FORM_JMIMAGE').'</span>';
		$params = array( 'id' => $this->nid.'_jmimage', 'size' => 1, 'class' => $class.'jmimage' );
		$field .= SPHtml_Input::select($this->nid.'_jmimage', $markerOptions, $jmimage, false, $params);
		
		//LGW
		$field .= '</div>';
		
		$field = "\n<div id=\"{$this->nid}\">\n{$field}\n</div>\n";
		if(!$return) {
			echo $field;
		} else {
				return $field;
				}
	}

	private function getData($copy = false) {
		$data = array();
		if(count($this->_data)) {
			foreach ($this->_data as $entry) {
				$data[$entry->copy] = get_object_vars($entry);
			}
		}
		if($copy && isset($data[1])) {
			return $data[1];
		}
		return isset($data[0]) ? $data[0] : array();
	}

	/**
	 * Get field specific values if these are in another table
	 * @param $sid - id of the entry
	 * @param $fullData - the database row form the spdb_field_data table
	 * @param $rawData - raw data of the field content
	 * @param $fData - full formated data of the field content
	 * @return void
	 */
	public function loadData($sid) {
		$this->_data = SPFactory::db()->select('*', 'spdb_field_jmapsmarker', array('fid' => $this->fid, 'sid' => $sid), 'copy')->loadObjectList();
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $request
	 * @return void
	 */
	public function submit(&$entry, $tsid = null, $request = 'POST') {
		SPLang::load('SpApp.jmapsmarker');
		if(count($this->verify($entry, $request))) {
			return SPRequest::search($this->nid, $request);
		} else {
			return array();
		}
	}

	/**
	 * Returns meta keys
	 */
	public function metaKeys() {
		$data = $this->getData(false);
		if(isset($data['latitude']) && isset($data['longitude'])) {
			SPFactory::header()->add("<meta name=\"ICBM\" content=\"{$data['latitude']}, {$data['longitude']}\" />");
			SPFactory::header()->add("<meta name=\"jmGeo.position\" content=\"{$data['latitude']};{$data['longitude']}\" />");
		}
		return null;
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 */
	private function verify($entry, $request) {
		SPLang::load('SpApp.jmapsmarker');
		$save = array();
		$save['jmlatitude'] = floatval(SPRequest::raw($this->nid.'_jmlatitude', null, $request));
		$save['jmlongitude'] = floatval(SPRequest::raw($this->nid.'_jmlongitude', null, $request));
		$save['jmcolor'] = SPRequest::raw($this->nid.'_jmcolor', null, $request);
		$save['jmimage'] = SPRequest::raw($this->nid.'_jmimage', null, $request);
		if ($save['jmimage'] == 'none' || $save['jmimage'] == 'NONE') {
			$save['jmimage'] = null;
		}
		$dexs = ($save['jmlatitude'] && $save['jmlongitude']) ? true : false;
		$dexsNiether = (!$save['jmcolor'] && !$save['jmimage']) ? true : false;
		$dexsBoth = ($save['jmcolor'] && $save['jmimage']) ? true : false;
		$dexsColor = ($save['jmcolor'] && !$save['jmimage']) ? true : false;
		$dexsImage = (!$save['jmcolor'] && $save['jmimage']) ? true : false;
		/* required? */
		if($this->required && !($dexs)) {
			throw new SPException(SPLang::e('FIELD_REQUIRED_ERR', $this->name));
		}
		if($this->required && $dexsNiether) {
			throw new SPException(SPLang::e('FIELD_REQUIRED_ERR', $this->name));
		}
		/* Invalid Data? */
		if(($dexBoth)) {
			throw new SPException(Sobi::Txt('JMFA_INVALID_MARKER_DATA'));
		}
		/* adminField? */
		if($this->adminField && $dexs) {
			if(!(Sobi:: Can('adm_fields.edit'))) {
				throw new SPException(SPLang::e('FIELD_NOT_AUTH', $this->name));
			}
		}
		/* free? */
		if(!($this->isFree) && $this->fee && $dexs) {
			SPFactory::payment()->add($this->fee, $this->name, $entry->get('id'), $this->fid);
		}
		/* editLimit? */
		if($this->editLimit == 0 && !(Sobi::Can('entry.adm_fields.edit')) && $dexs) {
			throw new SPException(SPLang::e('FIELD_NOT_AUTH_EXP', $this->name));
		}
		/* editable? */
		if(!($this->editable) && !(Sobi::Can('entry.adm_fields.edit')) && $dexs && $entry->get('version') > 1) {
			throw new SPException(SPLang::e('FIELD_NOT_AUTH_NOT_ED', $this->name));
		}
		return $save;
	}

	/**
	 * Get the data for a field and save it to the database
	 * @param SPEntry $entry
	 * @return bool
	 */
	public function saveData(&$entry, $request = 'POST') {
		if(!($this->enabled)) {
			return false;
		}
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$data = $this->verify($entry, $request);
		$time = SPRequest::now();
		$IP = SPRequest::ip('REMOTE_ADDR', 0, 'SERVER');
		$uid = Sobi::My('id');

		$params = array();
		/* collect required params */
		$params['publishUp'] = $entry->get('publishUp');
		$params['publishDown'] = $entry->get('publishDown');
		$params['fid'] = $data['fid'] = $this->fid;
		$params['sid'] = $data['sid']= $entry->get('id');
		$params['section'] = $data['section'] = Sobi::Reg('current_section');
		$params['lang'] = Sobi::Lang();
		$params['enabled'] = $entry->get('state');
		$params['params'] = null;
		$params['options'] = null;
		$params['baseData'] = null;
		$params['approved'] = $entry->get('approved');
		$params['confirmed'] = $entry->get('confirmed');
		/* if it is the first version, it is new entry */
		if($entry->get('version') == 1) {
			$params['createdTime'] = $time;
			$params['createdBy'] = $uid;
			$params['createdIP'] = $IP;
		}
		$params['updatedTime'] = $time;
		$params['updatedBy'] = $uid;
		$params['updatedIP'] = $IP;
		$params['copy'] = $data['copy'] = !($entry->get('approved'));
		if(Sobi::My('id') == $entry->get('owner')) {
			--$this->editLimit;
		}
		$params['editLimit'] = $this->editLimit;

		/* save it */
		try {
			$params['baseData'] = SPConfig::serialize(array('jmlatitude' => $data['jmlatitude'], 'jmlongitude' => $data['jmlongitude'],'jmcolor' => $data['jmcolor'], 'jmimage' => $data['jmimage']));
			$db->insertUpdate('spdb_field_data', $params);
			$db->insertUpdate('spdb_field_jmapsmarker', $data);
		}
		catch (SPException $x) {
			Sobi::Error(__CLASS__, SPLang::e('CANNOT_SAVE_DATA', $x->getMessage()), SPC::WARNING, 0, __LINE__, __FILE__);
		}
	}

	public function approve($sid) {
		$db =& SPFactory::db();
        try {
        	$db->select('COUNT(fid)', 'spdb_field_jmapsmarker', array('sid' => $sid, 'copy' => '1', 'fid' => $this->fid));
        	$copy = $db->loadResult();
        	if($copy) {
        		$db->delete('spdb_field_jmapsmarker', array('sid' => $sid, 'copy' => '0', 'fid' => $this->fid));
        		$db->update('spdb_field_jmapsmarker', array('copy' => '0'), array('sid' => $sid, 'copy' => '1', 'fid' => $this->fid), 1);
        	}
        } catch (SPException $x) {
        	Sobi::Error($this->name(), SPLang::e('CANNOT_GET_FIELDS_DATA_DB_ERR', $x->getMessage()), SPC::ERROR, 500, __LINE__, __FILE__);
        }
        parent::approve($sid);
	}

	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPFieldType#deleteData($sid)
	 */
	public function deleteData($sid) {
		SPFactory::db()->delete('spdb_field_jmapsmarker', array('fid' => $this->fid, 'sid' => $sid));
	}

	public function delete() {
		SPFactory::db()->delete('spdb_field_jmapsmarker', array('fid' => $this->fid));
	}

	/**
	 * @return array
	 */
	public function struct() {
		SPLang::load('SpApp.jmapsmarker');
		$data = $this->getData(false);
		if ($this->_firstsw == 1) {
//		if (empty($GLOBALS['app']['jmFirst'])) {
//			$GLOBALS['app']['jmFirst'] = true;
			$this->_firstsw = 0;
			SPFactory::header()->addJsFile('jquery');
			SPFactory::header()->addJsFile('jmapsmarker');
//			SPFactory::header()->addJsCode("var jmapsMarkersBuilt = 0; var jmapsMarkerArray = new Array();");
			SPFactory::header()->addJsCode("SPJmapsMarkerAddMarkers();");
		}
		if(count($data) && isset($data['jmlatitude']) && isset($data['jmlongitude']) && ($data['jmlatitude'] + $data['jmlongitude']) != 0) {
			$db =& SPFactory::db();
			$fieldData = array();
			$fieldNames = $db->select('*', 'spdb_field', array('section' => $data['section'], 'nid' => array($this->jmfaTitleField, $this->jmfaStreetField, $this->jmfaCityField, $this->jmfaStateField, $this->jmfaPostcodeField, $this->jmfaFeaturedField, $this->jmfaCountryField, $this->jmfaCustomField1, $this->jmfaCustomField2, $this->jmfaCustomField3, $this->jmfaCustomField4, $this->jmfaCustomField5), 'enabled' => 1))->loadObjectList();
			foreach($fieldNames as $fieldName) {
				$fieldDataObj = $db->select('*', 'spdb_field_data', array('sid' => $data['sid'], 'fid' => $fieldName->fid))->loadObject();
				if (isset($fieldDataObj->baseData)) {
					$fieldData[$fieldName->nid] = $fieldDataObj->baseData;
					if ($fieldName->fieldType == 'radio') {
						$fieldNumber[$fieldName->nid] = $fieldDataObj->fid;
						$fieldOptionObj = $db->select('*', 'spdb_field_option_selected', array('sid' => $data['sid'], 'fid' => $fieldName->fid))->loadObject();
						if (isset($fieldOptionObj->optValue)) {
							$fieldData[$fieldName->nid] = $fieldOptionObj->optValue;
						}
					}
				}
			}
//			$entry = SPFactory::Entry($data['sid']);
			$obj = SPFactory::object($data['sid']);
			try {
				$c = array('id'=>$data['sid'], 'oType'=>'entry') ;
				$db->select(array('pid', 'position', 'validSince', 'validUntil'), 'spdb_relations', $c, 'position');
				$categories = $db->loadAssocList('pid');
				/* validate categories - case some of them have been deleted */
				$cats = array_keys($categories);
				if(count($cats)) {
					$cats = $db->select('id', 'spdb_object', array('id'=>$cats))->loadResultArray();
				}
				if(count($categories)) {
					foreach ($categories as $i=>$c) {
						if(!(in_array($i, $cats))) {
							unset($categories[$i]);
						}
					}
				}
				/* push the main category to the top of this array */
				if(isset($categories [$obj->parent])) {
					$main = $categories [$obj->parent];
					unset($categories[$obj->parent]);
					$work_categories[$obj->parent] = $main;
				}
				foreach ($categories as $cid=>$cat ) {
					$work_categories[$cid] = $cat;
				}
				if($work_categories) {
					$labels = SPLang::translateObject(array_keys($work_categories), 'name', 'category' );
					foreach ($labels as $t) {
						$work_categories[$t['id']]['name'] = $t['value'];
					}
				}
			}
			catch (SPException $x) {
				Sobi::Error($this->name(), SPLang::e('CANNOT_GET_RELATIONS_DB_ERR', $x->getMessage()), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
// Modify the next 2 lines to use parameters instead of hard coding
			//LGW
			$jmfaLink = JURI::base() . 'index.php?option=com_sobipro&sid=' . $data['sid'] . ':' . $fieldData[$this->jmfaTitleField];
			//.'&tmpl=component';
			
			$jmfaTitle = '<b>' . $fieldData[$this->jmfaTitleField] . '</b><br/>';
			$jmfaDetails = '<div class="SOBIproDetailsDIV"><a href="' . $jmfaLink . '" class="SOBIproDetails" onclick="if(!parent.jQuery.browser.opera) {var linkData = \'' . $jmfaLink . '\'; var returnData = openColorbox(linkData); return returnData;}" target="_blank">' . Sobi::Txt('JMFA_DETAILS_LABEL') . '</a></div>';
			$XMLname = $fieldData[$this->jmfaTitleField];
			$XMLID = $data['sid'];
			$XMLstreet = $fieldData[$this->jmfaStreetField];
			$XMLcity = $fieldData[$this->jmfaCityField];
			$XMLstate = $fieldData[$this->jmfaStateField];
			$XMLpostal = $fieldData[$this->jmfaPostcodeField];
			$XMLcountry = $fieldData[$this->jmfaCountryField];
			$XMLfeatured = '';
			$XMLcustom1 = '';
			$XMLcustom2 = '';
			$XMLcustom3 = '';
			$XMLcustom4 = '';
			$XMLcustom5 = '';
			if ($this->jmfaFeaturedField && !empty($fieldData[$this->jmfaFeaturedField])) {
				$XMLfeatured = 'No';
				if ($fieldData[$this->jmfaFeaturedField] == '1') {
					$XMLfeatured = '1';
				}
			}
			if ($this->jmfaCustomField1 && !empty($fieldData[$this->jmfaCustomField1])) {
				$XMLcustom1 = $fieldData[$this->jmfaCustomField1];
			}
			if ($this->jmfaCustomField2 && !empty($fieldData[$this->jmfaCustomField2])) {
				$XMLcustom2 = $fieldData[$this->jmfaCustomField2];
			}
			if ($this->jmfaCustomField3 && !empty($fieldData[$this->jmfaCustomField3])) {
				$XMLcustom3 = $fieldData[$this->jmfaCustomField3];
			}
			if ($this->jmfaCustomField4 && !empty($fieldData[$this->jmfaCustomField4])) {
				$XMLcustom4 = $fieldData[$this->jmfaCustomField4];
			}
			if ($this->jmfaCustomField5 && !empty($fieldData[$this->jmfaCustomField5])) {
				$XMLcustom5 = $fieldData[$this->jmfaCustomField5];
			}

			$addressHTML = $XMLstreet . ',<br />';
			$addressHTML .= $XMLcity;
			$addressHTML .= ', ' . $XMLstate;
			$addressHTML .= '. ' . $XMLpostal . ' ';
			$XMLhtml = '';
			
			$HTMLwork = nl2br($this->jmfaBubbleLayout);
			$titleStart = strpos($HTMLwork, 'title');
			if ($titleStart === false) {
				$titleStart = 999;
			}
			$detailsStart = strpos($HTMLwork, 'details');
			if ($detailsStart === false) {
				$detailsStart = 999;
			}
			$streetStart = strpos($HTMLwork, 'street');
			if ($streetStart === false) {
				$streetStart = 999;
			}
			$cityStart = strpos($HTMLwork, 'city');
			if ($cityStart === false) {
				$cityStart = 999;
			}
			$stateStart = strpos($HTMLwork, 'state');
			if ($stateStart === false) {
				$stateStart = 999;
			}
			$postalStart = strpos($HTMLwork, 'postal');
			if ($postalStart === false) {
				$postalStart = 999;
			}
			$countryStart = strpos($HTMLwork, 'country');
			if ($countryStart === false) {
				$countryStart = 999;
			}
			$featuredStart = strpos($HTMLwork, 'featured');
			if ($featuredStart === false) {
				$featuredStart = 999;
			}
			$custom1Start = strpos($HTMLwork, 'custom1');
			if ($custom1Start === false) {
				$custom1Start = 999;
			}
			$custom2Start = strpos($HTMLwork, 'custom2');
			if ($custom2Start === false) {
				$custom2Start = 999;
			}
			$custom3Start = strpos($HTMLwork, 'custom3');
			if ($custom3Start === false) {
				$custom3Start = 999;
			}
			$custom4Start = strpos($HTMLwork, 'custom4');
			if ($custom4Start === false) {
				$custom4Start = 999;
			}
			$custom5Start = strpos($HTMLwork, 'custom5');
			if ($custom5Start === false) {
				$custom5Start = 999;
			}
			for ($bl=0;$bl<500;$bl++) {
				if ($bl >= $titleStart) {
					$HTMLarray = explode('title', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $jmfaTitle . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $jmfaTitle . $HTMLarray[1];
							}
					$titleStart = 998;
					continue;
				}
				if ($bl >= $detailsStart) {
					$HTMLarray = explode('details', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $jmfaDetails . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $jmfaDetails . $HTMLarray[1];
							}
					$detailsStart = 998;
					continue;
				}
				if ($bl >= $streetStart) {
					$HTMLarray = explode('street', $HTMLwork);
					$workCount = count($HTMLarray);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLstreet . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLstreet . $HTMLarray[1];
							}
					$streetStart = 998;
					continue;
				}
				if ($bl >= $cityStart) {
					$HTMLarray = explode('city', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcity . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcity . $HTMLarray[1];
							}
					$cityStart = 998;
					continue;
				}
				if ($bl >= $stateStart) {
					$HTMLarray = explode('state', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLstate . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLstate . $HTMLarray[1];
							}
					$stateStart = 998;
					continue;
				}
				if ($bl >= $postalStart) {
					$HTMLarray = explode('postal', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLpostal . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLpostal . $HTMLarray[1];
							}
					$postalStart = 998;
					continue;
				}
				if ($bl >= $countryStart) {
					$HTMLarray = explode('country', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcountry . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcountry . $HTMLarray[1];
							}
					$countryStart = 998;
					continue;
				}
				if ($bl >= $featuredStart) {
					$HTMLarray = explode('featured', $HTMLwork);
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLfeatured . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLfeatured . $HTMLarray[1];
							}
					$featuredStart = 998;
					continue;
				}
				if ($bl >= $custom1Start) {
					$HTMLarray = explode('custom1', $HTMLwork);
					$XMLcustom = $XMLcustom1;
					if ($this->jmfaCustomField1 == 'field_email') {
						$XMLcustom = '<a target="_blank" title="' . urlencode($XMLcustom1) . '" href="mailto:' . $XMLcustom1 . '">Email</a>' . ' ';
					}
					if ($this->jmfaCustomField1 == 'field_website') {
						$urlArray = SPConfig::unserialize($XMLcustom1);
						$websiteLabel = $urlArray['label'];
						$websiteURL = $urlArray['url'];
						$XMLcustom = '<a target="_blank" title="' . urlencode($websiteLabel) . '" href="http://' . $websiteURL . '">' . addslashes($websiteLabel) . '</a>' . ' ';
					}
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcustom . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcustom . $HTMLarray[1];
							}
					$custom1Start = 998;
					continue;
				}
				if ($bl >= $custom2Start) {
					$HTMLarray = explode('custom2', $HTMLwork);
					$XMLcustom = $XMLcustom2;
					if ($this->jmfaCustomField2 == 'field_email') {
						$XMLcustom = '<a target="_blank" title="' . urlencode($XMLcustom2) . '" href="mailto:' . $XMLcustom2 . '">Email</a>' . ' ';
					}
					if ($this->jmfaCustomField2 == 'field_website') {
						$urlArray = SPConfig::unserialize($XMLcustom2);
						$websiteLabel = $urlArray['label'];
						$websiteURL = $urlArray['url'];
						$XMLcustom = '<a target="_blank" title="' . urlencode($websiteLabel) . '" href="http://' . $websiteURL . '">' . addslashes($websiteLabel) . '</a>' . ' ';
					}
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcustom . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcustom . $HTMLarray[1];
							}
					$custom2Start = 998;
					continue;
				}
				if ($bl >= $custom3Start) {
					$HTMLarray = explode('custom3', $HTMLwork);
					$XMLcustom = $XMLcustom3;
					if ($this->jmfaCustomField3 == 'field_email') {
						$XMLcustom = '<a target="_blank" title="' . urlencode($XMLcustom3) . '" href="mailto:' . $XMLcustom3 . '">Email</a>' . ' ';
					}
					if ($this->jmfaCustomField3 == 'field_website') {
						$urlArray = SPConfig::unserialize($XMLcustom3);
						$websiteLabel = $urlArray['label'];
						$websiteURL = $urlArray['url'];
						$XMLcustom = '<a target="_blank" title="' . urlencode($websiteLabel) . '" href="http://' . $websiteURL . '">' . addslashes($websiteLabel) . '</a>' . ' ';
					}
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcustom . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcustom . $HTMLarray[1];
							}
					$custom1Start = 998;
					continue;
				}
				if ($bl >= $custom4Start) {
					$HTMLarray = explode('custom4', $HTMLwork);
					$XMLcustom = $XMLcustom4;
					if ($this->jmfaCustomField4 == 'field_email') {
						$XMLcustom = '<a target="_blank" title="' . urlencode($XMLcustom4) . '" href="mailto:' . $XMLcustom4 . '">Email</a>' . ' ';
					}
					if ($this->jmfaCustomField4 == 'field_website') {
						$urlArray = SPConfig::unserialize($XMLcustom4);
						$websiteLabel = $urlArray['label'];
						$websiteURL = $urlArray['url'];
						$XMLcustom = '<a target="_blank" title="' . urlencode($websiteLabel) . '" href="http://' . $websiteURL . '">' . addslashes($websiteLabel) . '</a>' . ' ';
					}
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcustom . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcustom . $HTMLarray[1];
							}
					$custom4Start = 998;
					continue;
				}
				if ($bl >= $custom5Start) {
					$HTMLarray = explode('custom5', $HTMLwork);
					$XMLcustom = $XMLcustom5;
					if ($this->jmfaCustomField5 == 'field_email') {
						$XMLcustom = '<a target="_blank" title="' . urlencode($XMLcustom5) . '" href="mailto:' . $XMLcustom5 . '">Email</a>' . ' ';
					}
					if ($this->jmfaCustomField5 == 'field_website') {
						$urlArray = SPConfig::unserialize($XMLcustom5);
						$websiteLabel = $urlArray['label'];
						$websiteURL = $urlArray['url'];
						$XMLcustom = '<a target="_blank" title="' . urlencode($websiteLabel) . '" href="http://' . $websiteURL . '">' . addslashes($websiteLabel) . '</a>' . ' ';
					}
					if (count($HTMLarray) == 1) {
						$HTMLwork = $XMLcustom . $HTMLarray[0];
					} else {
							$HTMLwork = $HTMLarray[0] . $XMLcustom . $HTMLarray[1];
							}
					$custom5Start = 998;
					continue;
				}
			}
			$XMLhtml .= $HTMLwork;			
			$XMLcatID = 0;
			if (!empty($main)) {
				$XMLcatID = $main['pid'];
			}
			$XMLcatOrdering = 0;
			$XMLlatitude = 0;
			$XMLminZoom = 0;
			$XMLmaxZoom = 20;
			if (!empty($data['jmlatitude'])) {
				$XMLlatitude = $data['jmlatitude'];
			}
			$XMLlongitude = 0;
			if (!empty($data['jmlongitude'])) {
				$XMLlongitude = $data['jmlongitude'];
			}
			$XMLiconImage = '';
			if (!empty($data['jmcolor'])) {
				$XMLiconImage = $data['jmcolor'];
			}
			if (!empty($data['jmimage']) && $data['jmimage'] != 'none') {
				$XMLiconImage = $data['jmimage'];
			}

//			$addressHTML = '<br />';				
			$addressHTML = $XMLstreet . ',<br />';
			$addressHTML .= $XMLcity;
			$addressHTML .= ', ' . $XMLstate;
			$addressHTML .= '. ' . $XMLpostal . ' ';
			$XMLoutAddress = $XMLstreet.', '.$XMLcity.', '.$XMLstate.'. '.$XMLpostal;
			
			$jmapsMarkerJSCode = '';
			$jmapsMarkerJSCode .= 'jmapsMarkersBuilt++;' . "\n";
			$jmapsMarkerJSCode .= 'var jmapsMarkerObj = new Object;' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.Name = "' . $XMLname . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.Address = "' . $XMLoutAddress . '"' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.CatID = "' . $XMLcatID . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.CatOrdering = "' . $XMLcatOrdering . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.HTML = "' . addslashes($XMLhtml) . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.Latitude = ' . $XMLlatitude . ';' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.Longitude = ' . $XMLlongitude . ';' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.Featured = "' . $XMLfeatured . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.IconLabel = jmapsMarkersBuilt.toString();' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.iconImage = "' . addslashes($XMLiconImage) . '";' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.minZoom = ' . intval($XMLminZoom) . ';' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerObj.maxZoom = ' . intval($XMLmaxZoom) . ';' . "\n";
			$jmapsMarkerJSCode .= 'jmapsIndex = jmapsMarkersBuilt - 1;' . "\n";
			$jmapsMarkerJSCode .= 'jmapsMarkerArray[jmapsIndex] = jmapsMarkerObj;' . "\n";
			SPFactory::header()->addJsCode($jmapsMarkerJSCode);
						
			return;
		}
	}
}