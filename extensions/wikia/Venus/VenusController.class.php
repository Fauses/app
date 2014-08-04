<?php

class VenusController extends WikiaController {
	private static $bodyParametersArray = [];
	private static $skinAssetGroups = [];

	private $assetsManager;
	private $skinTemplateObj;
	private $skin;

	public function init() {
		$this->assetsManager = AssetsManager::getInstance();
		$this->skinTemplateObj = $this->app->getSkinTemplateObj();
		$this->skin = RequestContext::getMain()->getSkin();

		$skinVars = $this->skinTemplateObj->data;

		// this should be re-viewed and removed if not nessesary
		$this->pageTitle = $skinVars['pagetitle'];
		$this->displayTitle = $skinVars['displaytitle'];
		$this->mimeType = $skinVars['mimetype'];
		$this->charset = $skinVars['charset'];
		$this->dir = $skinVars['dir'];
		$this->lang = $skinVars['lang'];
		$this->pageClass = $skinVars['pageclass'];
		$this->skinNameClass = $skinVars['skinnameclass'];
		$this->bottomScriptLinks = $skinVars['bottomscripts'];

		if ($pageCss = $skinVars['pagecss']) {
			$this->pageCss = '<style type="text/css">' . $pageCss . '</style>';
		} else {
			$this->pageCss = '';
		}

		// initialize variables
		$this->comScore = null;
		$this->quantServe = null;

		//TODO clean up wg variables inclusion in views
		global $wgOut;
		$this->topScripts = $wgOut->topScripts;
	}

	public function executeIndex() {
		global $wgUser, $wgTitle;

		$this->title = $wgTitle->getText();
		$this->contents = $this->skinTemplateObj->data['bodytext'];

		$this->isUserLoggedIn = $wgUser->isLoggedIn();

		$this->setBodyModules();

		$this->setBodyClasses();
		$this->setHeadItems();
		$this->setAssets();
	}

	public function setBodyModules() {
		$this->globalHeader = $this->getGlobalHeader();
		$this->notifications = $this->getNotifications();
		$this->topAds = $this->getTopAds();
		$this->wikiHeader = $this->getWikiHeader();
		$this->globalFooter = $this->getGlobalFooter();
		$this->corporateFooter = $this->getCorporateFootet();
	}


	public function setBodyClasses() {
		// generate list of CSS classes for <body> tag
		$bodyClasses = [$this->skinNameClass, $this->dir, $this->pageClass];

		// add skin theme name
		if(!empty($this->skin->themename)) {
			$bodyClasses[] = 'venus-' . $this->skin->themename;
		}

		// mark dark themes
		if (SassUtil::isThemeDark()) {
			$bodyClasses[] = 'venus-dark-theme';
		}

		$this->bodyClasses = implode(' ', $bodyClasses);
	}

	public function setHeadItems() {
		global $wgOut;
		$this->headLinks = $wgOut->getHeadLinks();
		$this->headItems = $this->skin->getHeadItems();

		$this->pageTitle = htmlspecialchars( $this->pageTitle );
		$this->displayTitle = htmlspecialchars( $this->displayTitle );
		$this->mimeType = htmlspecialchars( $this->mimeType );
		$this->charset = htmlspecialchars( $this->charset );
	}

	private function setAssets() {
		global $wgOut;

		$jsHeadGroups = ['venus_head_js'];
		$jsHeadFiles = '';
		$jsBodyGroups = ['venus_body_js'];
		$jsBodyFiles = '';
		$cssGroups = ['venus_css'];
		$cssLinks = '';

		$styles = $this->skin->getStyles();
		$scripts = $this->skin->getScripts();

		//let extensions manipulate the asset packages (e.g. ArticleComments,
		//this is done to cut down the number or requests)
		$this->app->runHook(
			'VenusAssetsPackages',
			[
				&$jsHeadGroups,
				&$jsBodyGroups,
				&$cssGroups
			]
		);

		//
		foreach ( $this->assetsManager->getURL( $cssGroups ) as $s ) {
			if ( $this->assetsManager->checkAssetUrlForSkin( $s, $this->skin ) ) {
				$cssLinks .= "<link rel=stylesheet href='{$s}'/>";
			}
		}

		if ( is_array( $styles ) ) {
			foreach ( $styles as $s ) {
				$cssLinks .= $s['tag'];
				//TODO: Compare with $cssLinks .= "<link rel=stylesheet href='{$s['url']}'/>";
			}
		}

		foreach ( $this->assetsManager->getURL( $jsHeadGroups ) as $src ) {
			if ( $this->assetsManager->checkAssetUrlForSkin( $s, $this->skin ) ) {
				$jsHeadFiles .= "<script src='{$src}'></script>";
			}
		}

		foreach ( $this->assetsManager->getURL( $jsBodyGroups ) as $src ) {
			if ( $this->assetsManager->checkAssetUrlForSkin( $s, $this->skin ) ) {
				$jsBodyFiles .= "<script src='{$src}'></script>";
			}
		}

		if ( is_array( $scripts ) ) {
			foreach ( $scripts as $s ) {
				$jsBodyFiles .= "<script src='{$s['url']}'></script>";
			}
		}

		// set variables
		$this->cssLinks = $cssLinks;
		$this->jsHeadFiles = $jsHeadFiles;
		$this->jsBodyFiles = $jsBodyFiles;
		$this->jsTopScripts = $wgOut->topScripts;
	}

	public function getGlobalHeader() {
		//return $this->app->renderView('GlobalNavigation', 'Index');
	}

	public function getNotifications() {
		//return $this->app->renderView('Notifications', 'Confirmation');
	}

	public function getWikiHeader() {
		//return $this->app->renderView( 'LocalHeader', 'Index' );
	}

	public function getTopAds() {
		return $this->app->renderView('Ad', 'Top');
	}

	public function getGlobalFooter() {
		return $this->app->renderView('GlobalFooter', 'index');
	}

	public function getCorporateFootet() {
		//return $this->app->renderView('CorporateFooter', 'Index');
	}

	public static function addBodyParameter($parameter) {
		static::$bodyParametersArray[] = $parameter;
	}

	/**
	 * Adds given AssetsManager group to Oasis main non-blocking JS request
	 *
	 * @param string $group group name
	 */
	public static function addSkinAssetGroup($group) {
		self::$skinAssetGroups[] = $group;
	}
}
