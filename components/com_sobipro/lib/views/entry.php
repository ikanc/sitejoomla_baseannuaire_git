<?php
/**
 * @version: $Id: entry.php 1734 2011-07-26 08:57:23Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-07-26 10:57:23 +0200 (Tue, 26 Jul 2011) $
 * $Revision: 1734 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/views/entry.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadView( 'view' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 5:15:02 PM
 */
class SPEntryView extends SPFrontView implements SPView
{

	public function display()
	{
		$this->_task = $this->get( 'task' );
		switch ( $this->get( 'task' ) ) {
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'details':
				$this->details();
				break;
		}
		parent::display();
	}

	private function edit()
	{
		SPLoader::loadClass( 'html.tooltip' );
		$this->_type = 'entry_form';
		$pid = $this->get( 'entry.parent' );
		if( !$pid ) {
			$pid = SPRequest::int( 'pid' );
		}
		$id = $this->get( 'entry.id' );
		if( $id ) {
			$this->addHidden( $id, 'entry.id' );
		}

		/* load the SigsiuTree class */
		$tree = SPLoader::loadClass( 'mlo.tree' );
		/* create new instance */
		$tree = new $tree( Sobi::Cfg( 'list.categories_ordering' ) );
		$link = "javascript:SP_selectCat( '{sid}' )";
		$tree->setHref( $link );
		$tree->setTask( 'category.chooser' );
		$tree->disable( Sobi::Section() );
		$tree->init( Sobi::Section() );
		$head =& SPFactory::header();
		$params = array();
		$params[ 'URL' ] = Sobi::Url( array( 'task' => 'category.parents', 'out' => 'json' ), true, false, true );
		$params[ 'MAXCATS' ] = Sobi::Cfg( 'entry.maxCats', '5'  );
		$params[ 'SEPARATOR' ] = Sobi::Cfg( 'string.path_separator', ' > '  );
		$head->addJsVarFile( 'edit', md5( Sobi::Section().Sobi::Section( true ).serialize( $params ) ), $params );

		$type = $this->key( 'template_type', 'xslt' );
		if( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if( $type == 'xslt' ) {
			$data = $this->entryData( false );
			$fields = $this->get( 'fields' );
			$f = array();
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'form' ) ) {
						$pf = null;
						$pfm = null;
						if( !( $field->get( 'isFree' ) ) && $field->get( 'fee' ) && !( Sobi::Can( 'entry.payment.free' ) ) ) {
							$pf = SPLang::currency( $field->get( 'fee' ) );
							$pfm = Sobi::Txt( 'EN.FIELD_NOT_FREE_MSG', array( 'fee' => $pf, 'fieldname' => $field->get( 'name' ) ) );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => array(
										'_complex' => 1,
										'_xml' => 1,
										'_data' => $field->field( true ),
									),
									'description' => array(
										'_complex' => 1,
										'_xml' => 1,
										'_data' => $field->get( 'description' ),
									),
									'fee' => $pf,
									'fee_msg' => $pfm
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
					}
				}
			}
			$f[ 'save_button' ] = array(
				'_complex' => 1,
				'_data' => array(
						'data' => array(
							'_complex' => 1,
							'_xml' => 1,
							'_data' => SPHtml_Input::submit( 'save', Sobi::Txt( 'EN.SAVE_ENTRY_BT' ) ),
						),
				)
			);
			$f[ 'cancel_button' ] = array(
				'_complex' => 1,
				'_data' => array(
						'data' => array(
							'_complex' => 1,
							'_xml' => 1,
							'_data' =>  SPHtml_Input::button( 'cancel', Sobi::Txt( 'EN.CANCEL_BT' ), array( 'onclick' => 'SPcancelEdit();') ),
						),
				)
			);

			//LGW
			$data[ 'entry' ][ '_data' ][ 'category_chooser' ] = array(
				'path' =>  array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' => SPHtml_Input::textarea( 'parent_path', $this->get( 'parent_path' ), false, '90%', 60, array( 'id' => 'entry.path', 'class' => 'inputbox required', 'readonly' => 'readonly' ) ),
				),
				'selected' =>  array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' => SPHtml_Input::text( 'entry.parent', $this->get( 'parents' ), array( 'id' => 'entry.parent', 'size' => 15, 'maxlength' => 50, 'class' => 'inputbox required', 'readonly' => 'readonly', 'style' => 'text-align:center;' ) ),
				),
			);
			$data[ 'entry' ][ '_data' ][ 'fields' ] = array(
						'_complex' => 1,
						'_data' => $f,
						'_attributes' => array( 'lang' => Sobi::Lang( false ) )
			);
			$data[ 'tree' ] =  array(
					'_complex' => 1,
					'_xml' => 1,
					'_data' => SPLang::entities( $tree->display( true ), true ),
			);
			$this->_attr = $data;
			Sobi::Trigger( $this->_type, ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
		}
	}

	private function details()
	{
		$this->_type = 'entry_details';
		$type = $this->key( 'template_type', 'xslt' );
		if( $type != 'php' && Sobi::Cfg( 'global.disable_xslt', false ) ) {
			$type = 'php';
		}
		if( $type == 'xslt' ) {
			$this->_attr = $this->entryData();
			SPFactory::header()->addCanonical( $this->_attr[ 'entry' ][ '_data' ][ 'url' ] );
			Sobi::Trigger( 'EntryView', ucfirst( __FUNCTION__ ), array( &$this->_attr ) );
		}
	}

	private function entryData( $getFields = true )
	{
		$entry = $this->get( 'entry' );
		$visitor = $this->get( 'visitor' );
		$data = array();
		$data[ 'section' ] = array(
				'_complex' => 1,
				'_data' => Sobi::Section( true ),
				'_attributes' => array( 'id' => Sobi::Section(), 'lang' => Sobi::Lang( false ) )
		);
		$en = array();
		$en[ 'name' ] = array(
			'_complex' => 1,
			'_data' => $entry->get( 'name' ),
			'_attributes' => array( 'lang' => Sobi::Lang( false ) )
		);
		$en[ 'created_time' ] = $entry->get( 'createdTime' );
		$en[ 'updated_time' ] = $entry->get( 'updatedTime' );
		$en[ 'valid_since' ] = $entry->get( 'validSince' );
		$en[ 'valid_until' ] = $entry->get( 'validUntil' );
		$en[ 'valid_until' ] = $entry->get( 'validUntil' );
		$en[ 'author' ] = $entry->get( 'owner' );
		$en[ 'counter' ] = $entry->get( 'counter' );
		$en[ 'approved' ] = $entry->get( 'approved' );

		if( $entry->get( 'state' ) == 0 ) {
			$en[ 'state' ] = 'unpublished';
		}
		else {
			if( strtotime( $entry->get( 'validUntil' ) ) != 0 && strtotime( $entry->get( 'validUntil' ) ) < time() ) {
				$en[ 'state' ] = 'expired';
			}
			elseif( strtotime( $entry->get( 'validSince' ) ) != 0 && strtotime( $entry->get( 'validSince' ) ) > time() ) {
				$en[ 'state' ] = 'pending';
			}
			else {
				$en[ 'state' ] = 'published';
			}
		}

//		$en[ 'confirmed' ] = $entry->get( 'confirmed' );
		$en[ 'url' ] = Sobi::Url( array( 'pid' => $entry->get( 'parent' ), 'sid' => $entry->get( 'id' ), 'title' => $entry->get( 'name' ) ), true, true, true );

		if( Sobi::Can( 'entry', 'edit', '*' ) || ( ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own'  ) ) ) {
			$en[ 'edit_url' ] = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $entry->get( 'id' ) ) );
		}
		if( Sobi::Can( 'entry', 'manage', '*' ) ) {
			$en[ 'approve_url' ] = Sobi::Url( array( 'task' => ( $entry->get( 'approved' ) ? 'entry.unapprove' : 'entry.approve' ), 'sid' => $entry->get( 'id' ) ) );
		}

		if( Sobi::Can( 'entry', 'publish', '*' ) || ( ( Sobi::My( 'id' ) == $entry->get( 'owner' ) && Sobi::Can( 'entry', 'publish', 'own' ) ) ) ) {
			$en[ 'publish_url' ] = Sobi::Url( array( 'task' => ( $entry->get( 'state' ) ? 'entry.unpublish' : 'entry.publish' ), 'sid' => $entry->get( 'id' ) ) );
		}

		$cats = $entry->get( 'categories' );
		$categories = array();
		if( count( $cats ) ) {
			$cn = SPLang::translateObject( array_keys( $cats ), 'name', 'category' );
		}
		foreach( $cats as $cid => $cat ) {
			$categories[] = array(
				'_complex' => 1,
				'_data' => SPLang::clean( $cn[ $cid ][ 'value' ] ),
				'_attributes' => array( 'lang' => Sobi::Lang( false ), 'id' => $cat[ 'pid' ], 'position' => $cat[ 'position' ], 'url' => Sobi::Url( array( 'sid' => $cat[ 'pid' ], 'title' => $cat[ 'name' ] ) )  )
			);
		}
		$en[ 'categories' ] = $categories;
		$en[ 'meta' ] = array(
			'description' => $entry->get( 'metaDesc' ),
			'keys' => $this->metaKeys( $entry ),
			'author' => $entry->get( 'metaAuthor' ),
			'robots' => $entry->get( 'metaRobots' ),
		);
		if( $getFields ) {
			$fields = $entry->getFields();
			$f = array();
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'details' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
						$struct = $field->struct();
						$options = null;
						if( isset( $struct[ '_options' ] ) ) {
							$options = $struct[ '_options' ];
							unset( $struct[ '_options' ] );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => $struct,
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
						if( Sobi::Cfg( 'entry.field_description', false ) ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
						}
						if( $options ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
						}
						if( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
							foreach( $struct[ '_xml_out' ] as $k => $v )
								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
						}
					}
				}
				$en[ 'fields' ] = $f;
			}
		}
		$this->menu( $data );
		$this->alphaMenu( $data );
		$data[ 'entry' ] = array(
						'_complex' => 1,
						'_data' => $en,
						'_attributes' => array( 'id' => $entry->get( 'id' ), 'nid' => $entry->get( 'nid' ), 'version' => $entry->get( 'version' ) )
		);
		$data[ 'visitor' ] = $this->visitorArray( $visitor );
		return $data;
	}
}
?>