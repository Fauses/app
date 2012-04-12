<?php
class FinishCreateWikiModule extends WikiaController {
	
	// form field values
	var $params;

	/**
	 * Loads params from cookie.
	 */
	public function executeLoadState() {
		wfProfileIn(__METHOD__);
		if(!empty($_COOKIE['createnewwiki'])) {
			$this->params = json_decode($_COOKIE['createnewwiki'], true);
		} else {
			$this->params = array();
		}
		wfProfileOut(__METHOD__);
	}
	
	/**
	 * empty method for almost static template
	 */
	public function executeWikiWelcomeModal() {
		wfProfileIn(__METHOD__);
		
		wfProfileOut(__METHOD__);
	}
	
	/**
	 * Updates wiki specific properties set from wiki creation wizard.
	 * Context of this method is on the wiki that the values are changing on.
	 * Main wiki creation happens on www, and it will redirect to the newly created wiki.
	 * The values are read from the session and only accessible by the admin.
	 */
	public function executeFinishCreate() {
		global $wgUser, $wgSitename;
		
		if ( !$wgUser->isAllowed( 'finishcreate' ) ) {
			return false;
		}

		global $wgOut;
		$this->executeLoadState();
		
		$mainPage = wfMsgForContent( 'mainpage' );
		
		// set theme
		if(!empty($this->params['color-body'])) {
			$themeSettings = new ThemeSettings();
			$themeSettings->saveSettings($this->params);
		}
		
		// set description on main page
		if(!empty($this->params['wikiDescription'])) {
			$mainTitle = Title::newFromText($mainPage);
			$mainId = $mainTitle->getArticleID();
			$mainArticle = Article::newFromID($mainId);
			if (!empty($mainArticle)) {
				$firstSectionText = $mainArticle->getSection($mainArticle->getRawText(), 1);
				$matches = array();
				if(preg_match('/={2,3}[^=]+={2,3}/', $firstSectionText, $matches)) {
					$newSectionTitle = str_replace('Wiki', $wgSitename, $matches[0]);
					$newSectionText = $mainArticle->replaceSection(1, "{$newSectionTitle}\n{$this->params['wikiDescription']}");
				} else {
					$newSectionText = $mainArticle->replaceSection(1, $this->params['wikiDescription']);
				}
				$mainArticle->updateArticle($newSectionText, '', false, false);
			}
		}
		
		$wgOut->enableClientCache(false);
		$wgOut->redirect($mainPage.'?wiki-welcome=1');
	}

}
