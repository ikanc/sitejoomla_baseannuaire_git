<?php
/**
 * @name		Radius Search Application
 * @package		mjradius
 * @copyright	Copyright © 2011 - All rights reserved.
 * @license		GNU/GPL
 * @author		Cédric Pelloquin
 * @author mail	info@myJoom.com
 * @website		www.myJoom.com
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
//ini_set("display_errors","on");	error_reporting(E_ALL);
class MJRadius extends SPPlugin{
	private static $methods = array('ListEntry', 'SearchDisplay', 'AfterExtendedSearch', 'OnFormStartSearch','OnRequestSearch');
	private $m_enabled 				= false;
	private $m_unit 				= 1 ;
	private $m_distances 			= array(10,25,50,100,250,500);
	private $m_orderresult			= true ;
	private $m_googleicon 			= false ;
	private $m_uselocateme			= true ;
	private $m_sprequest 			= false ;
	private $m_label				= "" ;
	private $m_inputText			= "" ;
	private $m_restricpt1			= "255,255" ;
	private $m_restricpt2			= "255,255" ;
	private $m_geocodeMode 			= 0 ;	//0=default autocomplete, 1=geocode in search, 2=hybrid
	private $m_acTypes				= "[]" ;
	private $m_acCountry			= "{}" ;
	private $m_mapVariable			= "" ;
	private $m_locateStart			= false ;
	private $m_inputwidth			= 0 ;
	private $m_defaultcenter		= "" ;
	private $m_custDistText			= "" ;
	
	public function __construct(){
		SPFactory::registry()->loadDBSection( 'mjradius' );
		$this->m_enabled 		= Sobi::Reg( 'mjradius.m_mjrslic.value' )<12345?0:Sobi::Reg( 'mjradius.m_enabled.value' );
		$this->m_unit 			= Sobi::Reg( 'mjradius.m_unit.value' );
		$this->m_distances 		= Sobi::Reg( 'mjradius.m_distances.value' );
		$this->m_googleicon 	= Sobi::Reg( 'mjradius.m_googleicon.value' );
		$this->m_uselocateme	= Sobi::Reg( 'mjradius.m_uselocateme.value' );
		$this->m_orderresult	= Sobi::Reg( 'mjradius.m_orderresult.value' );
		$this->m_label			= Sobi::Reg( 'mjradius.m_label.value' );
		$this->m_inputText		= Sobi::Reg( 'mjradius.m_inputText.value' );
		$this->m_raddec			= Sobi::Reg( 'mjradius.m_raddec.value' );
		$this->m_radmil			= Sobi::Reg( 'mjradius.m_radmil.value' );
		$this->m_radvir			= Sobi::Reg( 'mjradius.m_radvir.value' );
		$this->m_restricpt1		= trim(Sobi::Reg( 'mjradius.m_restricpt1.value' ));
		$this->m_restricpt2		= trim(Sobi::Reg( 'mjradius.m_restricpt2.value' ));
		$this->m_geocodeMode	= Sobi::Reg( 'mjradius.m_geocodeMode.value' );
		$this->m_acTypes		= Sobi::Reg( 'mjradius.m_acTypes.value' );
		$this->m_acCountry		= Sobi::Reg( 'mjradius.m_acCountry.value' );
		$this->m_mapVariable	= Sobi::Reg( 'mjradius.m_mapVariable.value' );
		$this->m_locateStart	= Sobi::Reg( 'mjradius.m_locateStart.value' );
		$this->m_inputwidth		= Sobi::Reg( 'mjradius.m_inputwidth.value' );
		$this->m_defaultcenter	= Sobi::Reg( 'mjradius.m_defaultcenter.value' );
		$this->m_custDistText	= Sobi::Reg( 'mjradius.m_custDistText.value' );

		$this->m_distances 		= explode(',',$this->m_distances);
		if (!count($this->m_distances))
			$this->m_distances = array(10,25,50,100,250,500);
			
		// pas possible de mettre de virgules dans la list deroulante de l'admin
		$this->m_acTypes = str_replace("#", ",", $this->m_acTypes) ;
	}

	public function provide( $action ){
		if( $this->m_enabled ) {
			return in_array( $action, self::$methods );
		}
		return false;
	}

	public function SearchDisplay( &$data ) {
		SPFactory::header()->addJsUrl('http://maps.googleapis.com/maps/api/js?sensor=true&libraries=places');
		$this->_setJs();
		SPLang::load( 'SpApp.mjradius' );

		$unit = $this->_getUnit();
		foreach ( $this->m_distances as $dist ) {
			// si on travaille en metres ...
			$distAff = ((count($unit)==2) AND ($dist<1))?($dist*1000):($dist*1) ;
			$unitAff = count($unit)==1?$unit[0]:($dist<1?$unit[1]:$unit[0]) ;
			$sout[ $dist ] = $distAff.$unitAff;
		}
		$input_param = array( 'id' => 'mj_rs_center_selector', 'class' => 'spFieldsData') ;
		if (strlen($this->m_inputText)>0) $input_param['placeholder'] = $this->m_inputText ; 
		if ($this->m_geocodeMode>0) $input_param['onblur'] = '_manGeocode();' ; 
		if ($this->m_inputwidth>0) $input_param['size'] = $this->m_inputwidth ; 

		$session	= JFactory::getSession();
		$ref_lat	= $session->get('mj_rs_ref_lat', null);
		$ref_lng	= $session->get('mj_rs_ref_lng', null);
		$ref_dist	= $session->get('mj_rs_ref_dist', null);
		$ref_loc	= $session->get('mj_rs_center_selector', null);

		$label 	= (strlen($this->m_label))?$this->m_label:Sobi::Txt( 'MJRS.CENTER' ) ;
		$radius	= SPHtml_Input::select( 'mj_rs_radius_selector', $sout, $ref_dist, false, array( 'id' => 'mj_rs_radius_selector', 'class' => 'text_area' ));
		$center	= SPHtml_Input::text( 'mj_rs_center_selector', $ref_loc, $input_param);
		$button1= $this->m_uselocateme==1?SPHtml_Input::button( 'mj_rs_cutom', Sobi::Txt( 'MJRS.USE_POSITION' ), array( 'id'=>'mj_rs_cutom', 'class'=>'inputbox btn', 'onClick'=>'userPos();', 'style'=>'border: 1px solid silver;')):'';
		//$button2= $this->m_uselocateme==2?' <img style="cursor : pointer;" src="'.Sobi::FixPath( Sobi::Cfg('img_folder_live').'/locateme.png').'" onClick="userPos();" alt="'.Sobi::Txt( 'MJRS.USE_POSITION' ).'" title="'.Sobi::Txt( 'MJRS.USE_POSITION' ).'" /> ':'';
		$button2= $this->m_uselocateme==2?' <i class="locateme icon-screenshot" onClick="userPos();"></i>':'';
		$imageW	= $this->m_googleicon==1?' <img src="http://code.google.com/intl/fr/apis/maps/documentation/places/images/powered-by-google-on-white.png" />':'' ;		
		$imageB	= $this->m_googleicon==2?' <img src="http://code.google.com/intl/fr/apis/maps/documentation/places/images/powered-by-google-on-black.png" />':'' ;		
		
		//SPFactory::header()->addJsCode('jQuery(document).ready(function() {jQuery("#mj_rs_center_selector").keypress(function(event){if(event.keyCode==13){event.preventDefault();}});});');
		$out 	= Sobi::Txt('<div class="SPSearchCell mjradius">
								<div class="SPSearchLabel"><strong>'.$label.' :</strong></div>
								<div class="SPSearchField center">'.$center.$button1.$button2.$imageW.$imageB.'</div>
								<div class="SPSearchLabel"><strong>'.Sobi::Txt( 'MJRS.RADIUS' ).' :</strong></div>
								<div class="SPSearchField radius">'.$radius.'</div> <input type="hidden" id="mj_rs_ref_lat" name="mj_rs_ref_lat" value="'.$ref_lat.'" /><input type="hidden" id="mj_rs_ref_lng" name="mj_rs_ref_lng" value="'.$ref_lng.'" /></div>');

		if(!$this->m_enabled)
			return ;

		SPLang::load( 'SpApp.mjradius' );
		$data[ 'mjradius' ] = $out ;
	}
	
	public function OnFormStartSearch(){
		// affiche le plugin unique dans la recherche
		$task = SPRequest::string('task', null );
		if (strtolower($task)=="search.results")
			return ;

		// ici je pete la session
		$session = JFactory::getSession();
		$session->clear('mj_rs_ref_lat') ;
		$session->clear('mj_rs_ref_lng') ;
		$session->clear('mj_rs_ref_dist') ;
		$session->clear('mj_rs_center_selector') ;
	}
 
	public function OnRequestSearch(&$req){
		// test qu'il recherche qqch
		if(! count($req))
			return ;

		$query = trim($req["search_for"]) ;
		if ((strlen($query)>0) AND ($query != Sobi::Txt( 'SH.SEARCH_FOR_BOX' ))){
			$this->m_sprequest = true ;
			return ;
		}
			
		foreach ( $req as $k=>$v ) {
			if (substr($k, 0, 6)!="field_")
				continue ;

			if( is_array( $v ) ) {
				foreach ( $v as $t ) {
					if (strlen($t) > 0){
						$this->m_sprequest = true ;
						return ;
					}
				}
			}
			else {
				if (strlen($v) > 0){
					$this->m_sprequest = true ;
					return ;
				}
			}
		}
	}
	
	public function AfterExtendedSearch(&$result){
		if (!$this->m_enabled)
			return ;
			
		// ici je pete la session
		$session = JFactory::getSession();
		$session->clear('mj_rs_ref_lat') ;
		$session->clear('mj_rs_ref_lng') ;
		$session->clear('mj_rs_ref_dist') ;
		//LGW ??? Ne faut-il pas peter aussi le centre ?
		$session->clear('mj_rs_center_selector') ;
		
		// récupère du form
		$dist		= SPRequest::string('mj_rs_radius_selector', 10 );		
		$ref_lat 	= SPRequest::string('mj_rs_ref_lat', null );
		$ref_lng 	= SPRequest::string('mj_rs_ref_lng', null );
		$ref_loc 	= SPRequest::string('mj_rs_center_selector', null );

		// si null on cherche dans le module
		if ((!$ref_loc) AND (!$ref_lat) AND (!$ref_lng)){
			$dist		= SPRequest::string('mj_rs_mod_radius_selector', 10 );		
			$ref_lat 	= SPRequest::string('mj_rs_mod_ref_lat', null );
			$ref_lng 	= SPRequest::string('mj_rs_mod_ref_lng', null );
			$ref_loc 	= SPRequest::string('mj_rs_mod_center_selector', null );
		}
		
		$dist = $dist*1 ; // on ne sais pas si c'est un 10 ou un 0.2 (pas de intval ou de floatval...)

		// si pas de lieu, on oublie
		if (strlen($ref_loc)<2)
			return ;

		if ((strlen($ref_lat)<1) OR (strlen($ref_lng)<1))
			return ;

		// nouveau centre
		$session->set('mj_rs_ref_lat', $ref_lat) ;
		$session->set('mj_rs_ref_lng', $ref_lng) ;
		$session->set('mj_rs_ref_dist', $dist) ;
		$session->set('mj_rs_center_selector', $ref_loc);
		
		$km = $this->_getKm() ;
		$db =& JFactory::getDBO();
		
		//LGW: on utilise jmapsmarkerfield 
		$query = " SELECT DISTINCT O.id, GEO.jmlatitude , GEO.jmlongitude , ";
		$query.= " ({$km}*acos(cos(radians({$ref_lat}))*cos(radians(GEO.jmlatitude))*cos(radians(GEO.jmlongitude)-radians({$ref_lng}))+sin(radians({$ref_lat}))*sin(radians(GEO.jmlatitude)))) AS distance ";
		$query.= " FROM `#__sobipro_object` AS O ";
		$query.= " LEFT JOIN `#__sobipro_field_jmapsmarker` AS GEO ON GEO.sid = O.id ";
		$query.= " WHERE O.oType='entry' AND O.state > 0 AND  GEO.section=".Sobi::Section()." ";
		$query.= (count($result)>0)?" AND O.id IN (".implode(',', $result).") ":" " ;
		$query.= " HAVING (distance < {$dist} ) ";
		$query.= ($this->m_orderresult)?" ORDER BY distance ":" ";

		$db->setQuery($query);
		$inRadius = $db->loadObjectList();
		if($db->getErrorNum()) {
			Sobi::Error( 'mjradius', "Radius search plugin query error : ".$db->stderr(), SPC::WARNING, 0, __LINE__, __CLASS__ );
			return ;
		}
			
		$resultOri	= $result ;
		$result		= array() ;
		if (!count($inRadius))
			return ;

		foreach($inRadius as $rad){
			// il y a une requete pure sp je déduit les recherches, sinon il veut juste une requete sur le rayon et je renvoie tout
			if ($this->m_sprequest){
				if (in_array($rad->id, $resultOri))
					$result[] = $rad->id ;
			}else {
				$result[] = $rad->id ;
			}
		}
	}

	public function ListEntry( &$data ){
		if (!$this->m_enabled)
			return ;

		// affiche le plugin unique dans la recherche
		$task = SPRequest::string('task', null );
		if (strtolower($task)!="search.results")
			return ;
			
		$session = JFactory::getSession();
		$ref_lat = $session->get('mj_rs_ref_lat', null);
		$ref_lng = $session->get('mj_rs_ref_lng', null);
		$ref_loc = $session->get('mj_rs_center_selector', null);

		if (!$ref_lat OR !$ref_lng)
			return ;

		$km = $this->_getKm() ;
		$id = $data['id'] ;
		$db	=& JFactory::getDBO();
		
		//LGW: on utilise jmapsmarkerfield
		$query = " SELECT  ";
		$query.= " ({$km}*acos(cos(radians({$ref_lat}))*cos(radians(GEO.jmlatitude))*cos(radians(GEO.jmlongitude)-radians({$ref_lng}))+sin(radians({$ref_lat}))*sin(radians(GEO.jmlatitude)))) AS distance ";
		$query.= " FROM `#__sobipro_object` AS O  ";
		$query.= " LEFT JOIN `#__sobipro_field_jmapsmarker` AS GEO ON GEO.sid = O.id ";
		$query.= " WHERE O.oType='entry' AND O.id = {$id} ";
		$query.= " LIMIT 1";
		
		$db->setQuery($query);
		$distance = $db->loadResult();

		if($db->getErrorNum()) {
			Sobi::Error( 'mjradius', "Radius search plugin query error : ".$db->stderr(), SPC::WARNING, 0, __LINE__, __CLASS__ );
			return ;
		}

		SPLang::load( 'SpApp.mjradius' );
		$unit = $this->_getUnit() ;
		$unitAff = count($unit)==1?$unit[0]:($distance<1?$unit[1]:$unit[0]) ;
		$distance = $this->_getHumanNumber($distance) ;
		$result = strlen($this->m_custDistText)>2?sprintf($this->m_custDistText, $distance):Sobi::Txt( 'MJRS.DISTANCE', $distance.' '.$unitAff) ;
		
		$data[ 'mjradius' ] = $result ;
	}

	public static function admMenu( &$links ){
		JHTML::_('behavior.mootools');
		SPLang::load( 'SpApp.mjradius' );
		$links[ 'Radius Search - myJoom' ] = 'mjradius';
	}

	private function _getKm() {
		if 		($this->m_unit==2)	return 3959 ; // miles
		else if ($this->m_unit==3)	return 3440 ; // miles marin
		return 6371 ; // default km
	}

	private function _getUnit() {
		SPLang::load( 'SpApp.mjradius' );
		if 		($this->m_unit == 2)	return array(Sobi::Txt( 'MJRS.UNIT_MILE')) ;
		else if	($this->m_unit == 3)	return array(Sobi::Txt( 'MJRS.UNIT_NAUTIC_MILE')) ;
		else if	($this->m_unit == 4)	return array(Sobi::Txt( 'MJRS.UNIT_KILOMETER'), Sobi::Txt( 'MJRS.UNIT_METER')) ;
		return array(Sobi::Txt( 'MJRS.UNIT_KILOMETER')) ;
	}
	
	private function _getHumanNumber($distance) {
		if (($this->m_unit==4) AND ($distance<1))
			return number_format($distance,3)*1000 ;//m
	
		$vir = ($this->m_radvir==0)?'.':',';
		$mil = '' ;
		if 		($this->m_radmil==1)	$mil = ' ' ;
		else if ($this->m_radmil==2)	$mil = '.' ;
		else if ($this->m_radmil==3)	$mil = "'" ;
		else if ($this->m_radmil==4)	$mil = "," ;

		return number_format($distance, $this->m_raddec, $vir, $mil);
	}
	
	private function _setJs(){
		$bound = "" ;
		$param = "" ;
		$container = "" ;
		SPLang::load( 'SpApp.mjradius' );
		if ((strlen($this->m_restricpt1)>2)&&(strlen($this->m_restricpt2)>2)){
			$bound = "var restricted = new google.maps.LatLngBounds(
			new google.maps.LatLng({$this->m_restricpt1}),
			new google.maps.LatLng({$this->m_restricpt2}));";
			$param = ",bounds:restricted";
		}

		$country = "" ;
		if (strlen($this->m_acCountry)==2)
			$country = ",componentRestrictions: {country: '{$this->m_acCountry}'} ";

		if (strlen($this->m_mapVariable)>2){
			$container = 'if ('.$this->m_mapVariable.'){
				ac.bindTo("bounds", '.$this->m_mapVariable.');
			} else {
				alert("there is no map named '.$this->m_mapVariable.'!");
			}' ;
		}
		
		// uniquement si pas manuel 
		$js = "";
		if ($this->m_geocodeMode!=1){
			$js = 'function initRSA() {'.$bound.'
						var input = document.getElementById("mj_rs_center_selector");
						var options = {types:'.$this->m_acTypes.$param.$country.'};
						var ac = new google.maps.places.Autocomplete(input, options);
						'.$container.'
						google.maps.event.addListener(ac, "place_changed", function() {
							var pl = ac.getPlace();
							jQuery("#mj_rs_ref_lat").val(pl.geometry.location.lat()) ;
							jQuery("#mj_rs_ref_lng").val(pl.geometry.location.lng()) ;
						});
					}
					google.maps.event.addDomListener(window, "load", initRSA);';
			$js = str_replace("\n",'', str_replace("\t",'', str_replace("  ",'', $js))) ;
			SPFactory::header()->addJsCode($js);
		}
		if ($this->m_geocodeMode>0){
			$js = 'function _manGeocode(){

				jQuery("#top_button").fadeOut("fast");
				var entry = jQuery("#mj_rs_center_selector").val();
				if (entry.length<3){return ;}
				geocoder = new google.maps.Geocoder();
				geocoder.geocode( { address:entry}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						jQuery("#mj_rs_ref_lat").val(results[0].geometry.location.lat()) ;
						jQuery("#mj_rs_ref_lng").val(results[0].geometry.location.lng()) ;
						jQuery("#mj_rs_center_selector").val(results[0].formatted_address);
						
						jQuery("#JmapsHome").trigger("userpos", [po.coords.latitude, po.coords.longitude]);
						jQuery("#JmapsSearch").trigger("userpos", [po.coords.latitude, po.coords.longitude]);
		
						var elt = results[0]["address_components"];
						for(i in elt){
							if(elt[i].types[0] == "postal_code") {
								jQuery(".module.newsletter").trigger("userposzip", [elt[i].long_name, 0]);
								break;
							}
						}				
					} else {
						jQuery("#mj_rs_center_selector").val("'.Sobi::Txt( 'MJRS.GEOCODE_NOT_FOLLOWING_REASON').' " + status);
					}
				});
				jQuery("#top_button").fadeIn("fast");
			};';
			$js = str_replace("\n",'', str_replace("\t",'', str_replace("  ",'', $js))) ;
			SPFactory::header()->addJsCode($js);
		}

		if (($this->m_locateStart) || ($this->m_uselocateme)){
			$js = ' function userPos(){
						var gc = new google.maps.Geocoder();
						if (navigator.geolocation) {
							navigator.geolocation.getCurrentPosition(function (po) {
								gc.geocode({"latLng":  new google.maps.LatLng(po.coords.latitude, po.coords.longitude) }, function(results, status) {
									if(status == google.maps.GeocoderStatus.OK) {
									
										jQuery("#mj_rs_ref_lat").val(po.coords.latitude) ;
										jQuery("#mj_rs_ref_lng").val(po.coords.longitude) ;
										jQuery("#mj_rs_center_selector").val(results[0]["formatted_address"]);
										
										jQuery("#JmapsHome").trigger("userpos", [po.coords.latitude, po.coords.longitude]);
										jQuery("#JmapsSearch").trigger("userpos", [po.coords.latitude, po.coords.longitude]);
										
										var elt = results[0]["address_components"];
										for(i in elt){
											if(elt[i].types[0] == "postal_code") {
												jQuery(".module.newsletter").trigger("userposzip", [elt[i].long_name, 0]);
												break;
											}
										}

										jQuery("#SobiPro").trigger("userposget");
										
										
									} else {
										alert("'.Sobi::Txt( 'MJRS.GEOCODE_NOT_FOLLOWING_REASON').' " + status);
									}
								});
							}, 
							function(error) {},
							{maximumAge:60000, timeout:10000, enableHighAccuracy:false} );
						}
						else{
							alert("'.Sobi::Txt( 'MJRS.ALLOW_GEOCODE' ).'");
						}
					}';
		}
		$js = str_replace("\n",'', str_replace("\t",'', str_replace("  ",'', $js))) ;
		SPFactory::header()->addJsCode($js);

		$session	= JFactory::getSession();
		$ref_loc	= $session->get('mj_rs_center_selector', $this->m_defaultcenter);
		 // utilise cette option ET (entre courant est bien celui du backend ) ET le JS est chargé
		if ((strlen($this->m_defaultcenter)>0)&&((strcasecmp($ref_loc,$this->m_defaultcenter)==0)||(strlen($ref_loc)<1)) &&($this->m_geocodeMode>0))
			SPFactory::header()->addJsCode('jQuery(document).ready( function(){jQuery("#mj_rs_center_selector").val("'.$this->m_defaultcenter.'"); });');
		
		if ($this->m_locateStart)
			SPFactory::header()->addJsCode('jQuery(document).ready(function(){if(jQuery("#mj_rs_center_selector").val().length<3){userPos();}});');

		return ;
	}	

	private function _getCoordManual($ref_loc, &$ref_lat, &$ref_lng){ // plus utilisé, mas gardé pour la technique intéressante de l'ouverture de fichier avec app SP
		// si uniquement autocomplete, je laisse pas geocoder
		if ($this->m_geocodeMode == 0)
			return ;
		// ... voir les autres fct d'autre exts...
		$connection = SPFactory::Instance( 'services.remote' );
		$connection->setOptions(array('url' => $urlRequest,'connecttimeout' => 10,'header' => false,'returntransfer' => true));
		while($geocodePending){
			// ... voir les autres fct d'autre exts...
			// je ne peux pas passer direct le fichier, car certains serveurs crashent avec acces distant (URL file-access is disabled in the server configuration)
			$xml = simplexml_load_string($connection->exec());
			$inf = $connection->info();			
			if( isset($inf[ 'http_code' ] ) && $inf[ 'http_code' ] != 200 ) 
				return Sobi::Error( 'about', sprintf( 'CANNOT_GET_NEWS', $news, $cinf[ 'http_code' ] ), SPC::WARNING, 0, __LINE__, __FILE__ );

			$status = $xml->status;
			if (strcmp($status, "OK")==0){
				// ... $xml->result->formatted_address;
                return true ;
			}
		}
		return true ;
	}
}
?>