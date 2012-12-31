<?php
/**
 * @version: $Id: contact.php 2559 2012-07-07 10:30:40Z Radek Suski $
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
 * $Date: 2012-07-07 12:30:40 +0200 (Sa, 07 Jul 2012) $
 * $Revision: 2559 $
 * $Author: Radek Suski $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created Thu, Jun 7, 2012
 */
class SPContactForm extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'send';
	/**
	 * @var SPField
	 */
	protected $fieldDefinition = null;

	public function __construct()
	{
	}

	/**
	 */
	public function execute()
	{
		$this->_task = $task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		if ( method_exists( $this, $this->_task ) ) {
			$this->$task();
		}
		else {
			Sobi::Error( 'SPRrCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
		}
	}

	protected function send()
	{
		SPLang::load( 'SpApp.contact' );
		$messageData = array();
		if ( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		if ( strlen( SPRequest::string( 'email' ) ) || strlen( SPRequest::string( 'name' ) ) ) {
			$this->response( array( 'status' => 'ok', 'message' => 'Thanks' ) );
			Sobi::Error( 'contact form', 'Fake data filled in - probably a bot', SPC::ERROR, 0, __LINE__, __FILE__ );
		}
		$fid = SPRequest::int( 'fid' );
		if ( !( $fid ) ) {
			Sobi::Error( 'contact form', 'Missing field id', SPC::ERROR, 0, __LINE__, __FILE__ );
			$this->response( array( 'status' => 'error', 'message' => Sobi::Txt( 'AFCF_RESPONSE_MSG_FAILED' ) ) );
		}
		$sid = SPRequest::int( 'sid' );
		if ( !( $sid ) ) {
			Sobi::Error( 'contact form', 'Missing associated entry', SPC::ERROR, 0, __LINE__, __FILE__ );
			$this->response( array( 'status' => 'error', 'message' => Sobi::Txt( 'AFCF_RESPONSE_MSG_FAILED' ) ) );
		}
		$messageData[ 'entry' ] = SPFactory::Entry( $sid );
		if ( $messageData[ 'entry' ]->get( 'owner' ) ) {
			$messageData[ 'author' ] = SPFactory::Instance( 'cms.base.user', $messageData[ 'entry' ]->get( 'owner' ) );
		}

		$this->fieldDefinition = SPFactory::Model( 'field' )->init( $fid );
		$required = $this->getRequired();
		$data = SPRequest::arr( 'spcform', array(), 'post' );

		if ( Sobi::My( 'id' ) ) {
			$messageData[ 'user' ] = SPFactory::user();
			$data[ 'email' ] = Sobi::My( 'email' );
			$data[ 'name' ] = Sobi::My( 'name' );
		}
		if ( count( $required ) ) {
			foreach ( $required as $field ) {
				if ( !( isset( $data[ $field ] ) && strlen( $data[ $field ] ) ) ) {
					$this->response( array( 'status' => 'error', 'message' => Sobi::Txt( 'AFCF_RESPONSE_REQUIRED_ERROR' ), 'require' => 'spcform[' . $field . ']' ) );
				}
			}
		}
		if ( isset( $data[ 'email' ] ) ) {
			$filter = SPFactory::registry()
					->loadDBSection( 'fields_filter' )
					->get( 'fields_filter.email.params' );
			$data[ 'email' ] = trim( $data[ 'email' ] );
			if ( !( preg_match( base64_decode( $filter ), $data[ 'email' ] ) ) ) {
				$this->response( array( 'status' => 'error', 'message' => Sobi::Txt( 'AFCF_RESPONSE_MAIL_ERROR' ), 'require' => 'spcform[email]' ) );
			}
		}
		$messageData[ 'settings' ] = $this->fieldDefinition->loadMessage();
		$content = null;
		$newLine = $messageData[ 'settings' ][ 'html' ] ? '<br/>' : "\n";
		foreach ( $data as $input => $value ) {
            if( $messageData[ 'settings' ][ 'html' ] ) {
                $value = nl2br( $value );
            }
			$messageData[ 'message' ][ $input ] = $value;
			$content .= "{$newLine}{$input}: {$value}";
		}
		$content .= $newLine;
		$messageData[ 'message' ][ 'content' ] = $content;
		Sobi::Trigger( 'Contact', 'Send', array( &$messageData ) );
		$send = SPFactory::db()
				->select( 'send', 'spdb_notifications', array( 'mailDate' => 'FUNCTION:NOW()', ) )
				->loadResult();
		if ( $send ) {
			$this->response( array( 'status' => 'ok', 'message' => Sobi::Txt( 'AFCF_RESPONSE_MAIL_SENT' ) ) );
		}
		else {
			$this->response( array( 'status' => 'failed', 'message' => Sobi::Txt( 'AFCF_RESPONSE_MSG_FAILED' ) ) );
		}
	}

	private function getRequired()
	{
		$currentTpl = Sobi::Cfg( 'section.template' );
		$currentTpl = SOBI_PATH . "/usr/templates/{$currentTpl}/contact_form/{$this->fieldDefinition->get( 'template' )}";
		$template = DOMDocument::load( $currentTpl );
		$xTemplate = new DOMXPath( $template );
		$entries = $xTemplate->query( '//form//*[contains(@class,"required")]' );
		$requires = array();
		if ( $entries->length ) {
			for ( $i = 0; $i < $entries->length; $i++ ) {
				$node = $entries->item( $i );
				$name = str_replace( array( 'spcform[', ']' ), null, $node->attributes->getNamedItem( 'name' )->nodeValue );
				$requires[ $name ] = $name;
			}
		}
		return $requires;
	}

	private function response( $response )
	{
		SPFactory::mainframe()->cleanBuffer();
		echo json_encode( $response );
		exit;
	}
}
