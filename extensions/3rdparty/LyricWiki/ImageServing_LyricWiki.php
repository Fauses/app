<?php
/**
 * @author Sean Colombo
 *
 * This extension hooks into Wikia's ImageServing to provide reasonable fallback images
 * on LyricWiki since there is a logical heirarchy of song -> album -> artist on LyricWiki.
 */
 
 
if ( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'ImageServing tweaks for LyricWiki',
	'version' => '1.0',
	'author' => '[http://seancolombo.com Sean Colombo]',
	//'url' => 'http://lyrics.wikia.com/User:Sean_Colombo',
	'descriptionmsg' => 'imageserving-lw-desc',
);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['ImageServing_LyricWiki'] = $dir . '/ImageServing_LyricWiki.i18n.php';

$wgHooks['ImageServing::fallbackOnNoResults'] = 'lw_ImageServingFallback';

/**
 * This is called by ImageServing if no representative image is found for a page.
 * If this is a song page, will first check the album page, then the artist page for a good image.
 * If this is an album page, will check the artist page for a good image.
 * If this is an artist page, there is no good fallback, so 'out' will be returned unmodified.
 */
function lw_ImageServingFallback(&$imageServing, $n, &$out){
	
	// TODO: Make this work for Gracenote pages too? (they don't have their own images and would have to fall back to the NS_MAIN pages with the same titles).

	$gnArticles = $imageServing->articlesByNS[ NS_GRACENOTE ];
	if(!empty($gnArticles)){
		foreach($gnArticles as $gnArticleId => $gnArticleData){
			$title = $gnArticleData['title'];
			
print "TRYING TO FIND FALLBACK IMAGE FOR GRACENOTE PAGE: \"$title\"<br/>\n";
		}
	}

	$articles = $imageServing->articlesByNS[ NS_MAIN ];
	if(!empty($articles)){
		foreach($articles as $articleId => $articleData){
			$title = $articleData['title'];
			
print "TRYING TO FIND FALLBACK IMAGE FOR PAGE: \"$title\"<br/>\n";
		}
	}
	
exit;

	return true; // because this is a hook
} // end lw_ImageServingFallback()
