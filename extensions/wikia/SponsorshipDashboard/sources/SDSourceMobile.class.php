<?php

/**
 * Category service
 * @author Jakub Kurcek
 */
class SponsorshipDashboardSourceMobile extends SponsorshipDashboardSource {

	const SD_SOURCE_TYPE = 'Mobile';

	const SD_EMPTY = '';

	const SD_WIKI_APP_ID = 'MobileWiki';
	const SD_ACTION = 'MobileAction';
	const SD_OS = 'MobileOs';
	const SD_APP = 'MobileApp';
	const SD_SERIE_NAME = 'SerieName';

	const SD_ACTION_CATEGORY = 'list_category_contents';
	const SD_ACTION_GAMES = 'list_games';
	const SD_ACTION_CONTENTS = 'list_wiki_contents';
	const SD_ACTION_SEARCH = 'local_search';

	const SD_OS_ANDROID = 'android';
	const SD_OS_IPHONE = 'iphone';
	const SD_OS_UNDEFINED = 'undefined';
	
	const SD_APP_GAMEGUIDES = 'GameGuides';

	static $allowedActions = array( 
		self::SD_ACTION_CATEGORY,
		self::SD_ACTION_GAMES,
		self::SD_ACTION_CONTENTS, 
		self::SD_ACTION_SEARCH,
		self::SD_EMPTY
	);

	static $allowedOS = array(
		self::SD_OS_ANDROID,
		self::SD_OS_IPHONE,
		self::SD_OS_UNDEFINED,
		self::SD_EMPTY
	);

	static $allowedApp = array(
		self::SD_APP_GAMEGUIDES,
		self::SD_EMPTY
	);

	protected $action = '';
	protected $application = '';
	protected $operatingsystem = '';
	protected $wikiAppId = '';
	protected $serieName = '';

	protected $startDate;
	protected $endDate;
	
	// ============================

	public function __construct() {

		$this->iNumberOfXGuideLines = 7;
		$this->App = WF::build('App');
	}

	/*
	 * Returns calss DB identifier.
	 *
	 * @return string
	 */

	public function getSourceKey() {
		return 'Mobile';
	}

	/*
	 * Returns source memcache key. Key is built from varaibles specyfic for current serie.
	 *
	 * @return string
	 */

	public function getCacheKey() {

		$aKey = array();

		$aKey[] = $this->lastDateUnits;
		$akey[] = $this->action;
		$akey[] = $this->operatingsystem;
		$akey[] = $this->application;
		$aKey[] = $this->startDate;
		$aKey[] = $this->endDate;
		$aKey[] = $this->serieName;
		
		return $this->serieName.':'.self::SD_MC_KEY_PREFIX.':'.self::SD_SOURCE_TYPE.':'.md5(implode( ':', $aKey ));
	}

	protected function recalculateDateVariables() {

		// 2DO: add logic behind

		$this->startDate = 0;
		$this->enddate = 0;
	}

	/*
	 * Generates data on SQL basis.
	 *
	 * @return void
	 */

	protected function loadData() {

		if ( !$this->dataLoaded ) {

			$this->getResults();
			$this->dataLoaded = true;
		}
	}

	protected function getResults() {

		$this->recalculateDateVariables();

		if ( !$this->loadDataFromCache() ) {

			$wgStatsDB = $this->App->getGlobal('wgStatsDB');
			$returnArray = array();

			$sql = "SELECT count(url) as number, date(ts) as mobile_date
				FROM mobile_apps
				WHERE url like '{$this->getMobileURLforLike()}' ";

			$sql .= ( !empty( $this->operatingsystem ) ) ? " AND os = '".$this->operatingsystem."'" : '';
			$sql .= ( !empty( $this->application ) ) ? " AND app = '".$this->application."'" : '';

			$sql .= " GROUP BY date(ts)
				ORDER BY mobile_date";

			$dbr = wfGetDB( DB_SLAVE, array(), $wgStatsDB );
			$res = $dbr->query( $sql, __METHOD__ );
			
			$result = array();
			while ( $row = $res->fetchObject( $res ) ) {
				$sDate = $row->mobile_date;
				$this->dataAll[ $sDate ][ 'date' ] = $sDate;
				$this->dataAll[ $sDate ][ 'a'.md5( $this->serieName ) ] = $row->number;
			}
			$this->dataTitles[ 'a'.md5( $this->serieName ) ] = $this->serieName;

			$i = 0;
			$this->saveDataToCache();
		}
	}

	/*
	 * Returns LIKE string for query.
	 *
	 * @return string
	 */

	protected function getMobileURLforLike() {

		$string = '%';
		$string .= ( empty( $this->action ) ) ? '%' : $this->action;
		$string .= ( empty( $this->wikiAppId ) ) ? '%' : '/'.$this->wikiAppId;
		$string .= '%';

		return $string;
	}

	/*
	 * Returns array filled with params names and values. Used for saving and filling forms.
	 *
	 * @return array 'paramName' => 'paramValue'
	 */

	protected function getLocalParamsArray() {

		$aData = array();

		$aData[ self::SD_ACTION ] = $this->action;
		$aData[ self::SD_APP ] = $this->application;
		$aData[ self::SD_OS ] = $this->operatingsystem;
		$aData[ self::SD_WIKI_APP_ID ] = $this->wikiAppId;
		$aData[ self::SD_SERIE_NAME ] = $this->serieName;
		
		return $aData;
	}

	public function getMenuTemplateLink() {

		return '../../templates/editor/addNewMobile';
	}
	
	/*
	 * Fills object from array. Used when creating object from form results.
	 *
	 * @return string - url
	 */

	public function fillFromArray( $aParams ) {

		$sources = array();

		$this->action = ( isset( $aParams[ self::SD_ACTION ] ) && in_array( $aParams[ self::SD_ACTION ], self::$allowedActions ) )? $aParams[ self::SD_ACTION ]: '';
		$this->operatingsystem = ( isset( $aParams[ self::SD_OS ] ) && in_array( $aParams[ self::SD_OS ], self::$allowedOS ) )? $aParams[ self::SD_OS ]: '';
		$this->application = ( isset( $aParams[ self::SD_OS ] ) && in_array( $aParams[ self::SD_OS ], self::$allowedApp ) )? $aParams[ self::SD_OS ]: '';
		$this->wikiAppId = ( isset( $aParams[ self::SD_WIKI_APP_ID ] ) ) ? $aParams[ self::SD_WIKI_APP_ID ]: '';
		$this->serieName = ( isset( $aParams[ self::SD_SERIE_NAME ] ) ) ? $aParams[ self::SD_SERIE_NAME ]: '';

		$this->setGeneralParamsFromArray( $aParams );
	}

	public function validFrequency( $frequency ) {

		return in_array(
			$frequency,
			array(
			    SponsorshipDashboardDateProvider::SD_FREQUENCY_DAY
			)
		);
	}
}
