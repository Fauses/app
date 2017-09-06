<?php
/**
 * Implements Special:Statistics
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 */


use Wikia\Service\User\Permissions\PermissionsServiceAccessor;

/**
 * Special page lists various statistics, including the contents of
 * `site_stats`, plus page view details if enabled
 *
 * @ingroup SpecialPage
 */
class SpecialStatistics extends SpecialPage {
	use PermissionsServiceAccessor;

	private $edits, $good, $images, $total, $users,
			$activeUsers = 0;

	public function __construct() {
		parent::__construct( 'Statistics' );
	}

	public function execute( $par ) {
		global $wgMemc, $wgMiserMode;

		$this->setHeaders();
		$this->getOutput()->addModuleStyles( 'mediawiki.special' );

		$this->edits = SiteStats::edits();
		$this->good = SiteStats::articles();
		$this->images = SiteStats::images();
		$this->total = SiteStats::pages();
		$this->users = SiteStats::users();
		$this->activeUsers = SiteStats::activeUsers();
		$this->hook = '';

		# Set active user count
		if( !$wgMiserMode ) {
			$key = wfMemcKey( 'sitestats', 'activeusers-updated' );
			// Re-calculate the count if the last tally is old...
			if( !$wgMemc->get($key) ) {
				$dbw = wfGetDB( DB_MASTER );
				SiteStatsUpdate::cacheUpdate( $dbw );
				$wgMemc->set( $key, '1', 24*3600 ); // don't update for 1 day
			}
		}

		$text = Xml::openElement( 'table', array( 'class' => 'wikitable mw-statistics-table' ) );

		# Statistic - pages
		$text .= $this->getPageStats();

		# Statistic - edits
		$text .= $this->getEditStats();

		# Statistic - users
		$text .= $this->getUserStats();

		# Statistic - usergroups
		$text .= $this->getGroupStats();

		# Statistic - other
		$extraStats = array();
		if( Hooks::run( 'SpecialStatsAddExtra', array( &$extraStats ) ) ) {
			$text .= $this->getOtherStats( $extraStats );
		}

		$text .= Xml::closeElement( 'table' );

		#<Wikia>
		Hooks::run( "CustomSpecialStatistics", [ $this, &$text ] );
		#</Wikia>

		# Customizable footer
		$footer = wfMessage( 'statistics-footer' );
		if ( !$footer->isBlank() ) {
			$text .= "\n" . $footer->parse();
		}

		$this->getOutput()->addHTML( $text );
	}

	/**
	 * Format a row
	 * @param $text  String: description of the row
	 * @param $number  Float: a statistical number
	 * @param $trExtraParams  Array: params to table row, see Html::elememt
	 * @param $descMsg  String: message key
	 * @param $descMsgParam  Array: message params
	 * @return string table row in HTML format
	 */
	private function formatRow( $text, $number, $trExtraParams = array(), $descMsg = '', $descMsgParam = '' ) {
		if( $descMsg ) {
			$msg = wfMessage( $descMsg, $descMsgParam );
			if ( $msg->exists() ) {
				$descriptionText = $msg->parse();
				$text .= "<br />" . Xml::element( 'small', array( 'class' => 'mw-statistic-desc'),
					" ($descriptionText)" );
			}
		}
		return Html::rawElement( 'tr', $trExtraParams,
			Html::rawElement( 'td', array(), $text ) .
			Html::rawElement( 'td', array( 'class' => 'mw-statistics-numbers' ), $number )
		);
	}

	/**
	 * Each of these methods is pretty self-explanatory, get a particular
	 * row for the table of statistics
	 * @return string
	 */
	private function getPageStats() {
		return Xml::openElement( 'tr' ) .
			Xml::tags( 'th', array( 'colspan' => '2' ), wfMsgExt( 'statistics-header-pages', array( 'parseinline' ) ) ) .
			Xml::closeElement( 'tr' ) .
				$this->formatRow( Linker::linkKnown( SpecialPage::getTitleFor( 'Allpages' ),
						wfMsgExt( 'statistics-articles', array( 'parseinline' ) ) ),
						$this->getLanguage()->formatNum( $this->good ),
						array( 'class' => 'mw-statistics-articles' ) ) .
				$this->formatRow( wfMsgExt( 'statistics-pages', array( 'parseinline' ) ),
						$this->getLanguage()->formatNum( $this->total ),
						array( 'class' => 'mw-statistics-pages' ),
						'statistics-pages-desc' ) .
				$this->formatRow( Linker::linkKnown( SpecialPage::getTitleFor( 'Listfiles' ),
						wfMsgExt( 'statistics-files', array( 'parseinline' ) ) ),
						$this->getLanguage()->formatNum( $this->images ),
						array( 'class' => 'mw-statistics-files' ) );
	}
	private function getEditStats() {
		return Xml::openElement( 'tr' ) .
			Xml::tags( 'th', array( 'colspan' => '2' ), wfMsgExt( 'statistics-header-edits', array( 'parseinline' ) ) ) .
			Xml::closeElement( 'tr' ) .
				$this->formatRow( wfMsgExt( 'statistics-edits', array( 'parseinline' ) ),
						$this->getLanguage()->formatNum( $this->edits ),
						array( 'class' => 'mw-statistics-edits' ) ) .
				$this->formatRow( wfMsgExt( 'statistics-edits-average', array( 'parseinline' ) ),
						$this->getLanguage()->formatNum( sprintf( '%.2f', $this->total ? $this->edits / $this->total : 0 ) ),
						array( 'class' => 'mw-statistics-edits-average' ) );
	}

	private function getUserStats() {
		global $wgActiveUserDays;
		return Xml::openElement( 'tr' ) .
			Xml::tags( 'th', array( 'colspan' => '2' ), wfMsgExt( 'statistics-header-users', array( 'parseinline' ) ) ) .
			Xml::closeElement( 'tr' ) .
				$this->formatRow( wfMsgExt( 'statistics-users', array( 'parseinline' ) ),
						$this->getLanguage()->formatNum( $this->users ),
						array( 'class' => 'mw-statistics-users' ) ) .
				$this->formatRow( wfMsgExt( 'statistics-users-active', array( 'parseinline' ) ) . ' ' .
							Linker::linkKnown(
								SpecialPage::getTitleFor( 'Activeusers' ),
								wfMsgHtml( 'listgrouprights-members' )
							),
						$this->getLanguage()->formatNum( $this->activeUsers ),
						array( 'class' => 'mw-statistics-users-active' ),
						'statistics-users-active-desc',
						$this->getLanguage()->formatNum( $wgActiveUserDays ) );
	}

	private function getGroupStats() {
		global $wgGroupPermissions;
		$text = '';
		foreach( $wgGroupPermissions as $group => $permissions ) {
			# Skip generic * and implicit groups
			if ( $this->permissionsService()->getConfiguration()->isImplicitGroup( $group ) ) {
				continue;
			}
			$groupname = htmlspecialchars( $group );
			$msg = wfMessage( 'group-' . $groupname );
			if ( $msg->isBlank() ) {
				$groupnameLocalized = $groupname;
			} else {
				$groupnameLocalized = $msg->text();
			}
			$msg = wfMessage( 'grouppage-' . $groupname )->inContentLanguage();
			if ( $msg->isBlank() ) {
				$grouppageLocalized = MWNamespace::getCanonicalName( NS_PROJECT ) . ':' . $groupname;
			} else {
				$grouppageLocalized = $msg->text();
			}
			$linkTarget = Title::newFromText( $grouppageLocalized );
			$grouppage = Linker::link(
				$linkTarget,
				htmlspecialchars( $groupnameLocalized )
			);
			$grouplink = Linker::linkKnown(
				SpecialPage::getTitleFor( 'Listusers' ),
				wfMsgHtml( 'listgrouprights-members' ),
				array(),
				array( 'group' => $group )
			);
			# Add a class when a usergroup contains no members to allow hiding these rows
			$classZero = '';
			$countUsers = SiteStats::numberingroup( $groupname );
			if( $countUsers == 0 ) {
				// wikia change start
				if( $this->permissionsService()->getConfiguration()->isGlobalGroup( $groupname ) ) {
					//rt#57322 hide our effective global groups
					continue;
				}
				// wikia change end
				$classZero = ' statistics-group-zero';
			}
			$text .= $this->formatRow( $grouppage . ' ' . $grouplink,
				$this->getLanguage()->formatNum( $countUsers ),
				array( 'class' => 'statistics-group-' . Sanitizer::escapeClass( $group ) . $classZero )  );
		}
		return $text;
	}

	private function getOtherStats( $stats ) {
		if ( !count( $stats ) )
			return '';

		$return = Xml::openElement( 'tr' ) .
			Xml::tags( 'th', array( 'colspan' => '2' ), wfMsgExt( 'statistics-header-hooks', array( 'parseinline' ) ) ) .
			Xml::closeElement( 'tr' );

		foreach( $stats as $name => $number ) {
			$name = htmlspecialchars( $name );
			$number = htmlspecialchars( $number );

			$return .= $this->formatRow( $name, $this->getLanguage()->formatNum( $number ), array( 'class' => 'mw-statistics-hook' ) );
		}

		return $return;
	}
}
