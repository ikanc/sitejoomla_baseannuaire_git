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
SPLoader::loadController( 'config', true );

class SPMJRadiusCtrl extends SPConfigAdmCtrl{
	protected $_type = 'mjradius';
	protected $_defTask = 'config';

	public function execute(){
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		SPLang::load( 'SpApp.mjradius' );
		switch ( $this->_task ) {
			case 'config':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'save':
				$this->save();
				break;
			default:
				Sobi::Error( 'MJRadiusCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				break;
		}
	}

	private function screen(){
		SPFactory::registry()->loadDBSection( 'mjradius' );
		$view = $this->getView( 'mjradius' );
		if( SPFs::exists( implode( DS, array( SOBI_PATH, 'opt', 'plugins', 'mjradius', 'description_'.Sobi::Lang().'.html' ) ) ) ) {
			$c = SPFs::read( implode( DS, array( SOBI_PATH, 'opt', 'plugins', 'mjradius', 'description_'.Sobi::Lang().'.html' ) ) );
		}
		else {
			$c = SPFs::read( implode( DS, array( SOBI_PATH, 'opt', 'plugins', 'mjradius', 'description_en-GB.html' ) ) );
		}
		
		$view->assign( $c, 'description' );
		$view->assign( Sobi::Reg('mjradius.m_enabled.value'		), 'm_enabled');
		$view->assign( Sobi::Reg('mjradius.m_unit.value' 		), 'm_unit');
		$view->assign( Sobi::Reg('mjradius.m_distances.value'	), 'm_distances');
		$view->assign( Sobi::Reg('mjradius.m_uselocateme.value' ), 'm_uselocateme');
		$view->assign( Sobi::Reg('mjradius.m_googleicon.value' 	), 'm_googleicon');
		$view->assign( Sobi::Reg('mjradius.m_orderresult.value' ), 'm_orderresult');
		$view->assign( Sobi::Reg('mjradius.m_label.value' 		), 'm_label');
		$view->assign( Sobi::Reg('mjradius.m_mjrslic.value' 	), 'm_mjrslic');
		$view->assign( Sobi::Reg('mjradius.m_raddec.value' 		), 'm_raddec');
		$view->assign( Sobi::Reg('mjradius.m_radmil.value' 		), 'm_radmil');
		$view->assign( Sobi::Reg('mjradius.m_radvir.value' 		), 'm_radvir');
		$view->assign( Sobi::Reg('mjradius.m_restricpt1.value' 	), 'm_restricpt1');
		$view->assign( Sobi::Reg('mjradius.m_restricpt2.value' 	), 'm_restricpt2');
		$view->assign( Sobi::Reg('mjradius.m_inputText.value' 	), 'm_inputText');
		$view->assign( Sobi::Reg('mjradius.m_geocodeMode.value' ), 'm_geocodeMode');
		$view->assign( Sobi::Reg('mjradius.m_acTypes.value' 	), 'm_acTypes');
		$view->assign( Sobi::Reg('mjradius.m_acCountry.value' 	), 'm_acCountry');
		$view->assign( Sobi::Reg('mjradius.m_mapVariable.value' ), 'm_mapVariable');
		$view->assign( Sobi::Reg('mjradius.m_locateStart.value' ), 'm_locateStart');
		$view->assign( Sobi::Reg('mjradius.m_inputwidth.value'	), 'm_inputwidth');
		$view->assign( Sobi::Reg('mjradius.m_custDistText.value'), 'm_custDistText');
		$view->assign( Sobi::Reg('mjradius.m_defaultcenter.value'), 'm_defaultcenter');
		$view->loadConfig( 'extensions.mjradius' );
		$view->setTemplate( 'extensions.mjradius' );
		$view->display();
	}

	protected function save(){
		SPFactory::registry()->saveDBSection(array(	array('key'=>'m_enabled', 'value'=>SPRequest::int('m_enabled')),
													array('key'=>'m_unit', 'value'=>SPRequest::int( 'm_unit')),
													array('key'=>'m_distances', 'value'=>SPRequest::string( 'm_distances')),
													array('key'=>'m_googleicon', 'value'=>SPRequest::int( 'm_googleicon')),
													array('key'=>'m_orderresult', 'value'=>SPRequest::int( 'm_orderresult')),
													array('key'=>'m_mjrslic', 'value'=>SPRequest::int( 'm_mjrslic')),
													array('key'=>'m_raddec', 'value'=>SPRequest::int( 'm_raddec')),
													array('key'=>'m_radmil', 'value'=>SPRequest::int( 'm_radmil')),
													array('key'=>'m_radvir', 'value'=>SPRequest::int( 'm_radvir')),
													array('key'=>'m_restricpt1', 'value'=>SPRequest::string( 'm_restricpt1')),
													array('key'=>'m_restricpt2', 'value'=>SPRequest::string( 'm_restricpt2')),
													array('key'=>'m_label', 'value'=>SPRequest::string( 'm_label')),
													array('key'=>'m_uselocateme', 'value'=>SPRequest::int( 'm_uselocateme')),
													array('key'=>'m_inputText', 'value'=>SPRequest::string( 'm_inputText')),
													array('key'=>'m_geocodeMode', 'value'=>SPRequest::int( 'm_geocodeMode')),
													array('key'=>'m_mapVariable', 'value'=>SPRequest::string( 'm_mapVariable')),
													array('key'=>'m_acTypes', 'value'=>SPRequest::string( 'm_acTypes')),
													array('key'=>'m_locateStart', 'value'=>SPRequest::int( 'm_locateStart')),
													array('key'=>'m_inputwidth', 'value'=>SPRequest::int( 'm_inputwidth')),
													array('key'=>'m_defaultcenter', 'value'=>SPRequest::string( 'm_defaultcenter')),
													array('key'=>'m_custDistText', 'value'=>SPRequest::string( 'm_custDistText')),
													array('key'=>'m_acCountry', 'value'=>SPRequest::string( 'm_acCountry'))
													), $this->_type);
		Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
	}
}
?>