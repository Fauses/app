<?php
	// get wiki thumbnail
	$images = $resultSet->getHeader( 'images' );
	$helper = new \WikiaHomePageHelper();

	if ( !empty( $images ) ) {
		foreach($images as $k=>$v) {
			$images[$k] = $helper->getImageUrl($v, 150, 100);

		}
		$imageURL = $images[0];
	} else {
		// display placeholder image if no thumbnail
		$imageURL = $wgExtensionsPath . '/wikia/Search/images/wiki_image_placeholder.png';
	}
?>

<?php if($resultSet->getResultsFound() > 1): ?>
    <!-- grouped search result-->
    <li class="result">

		<?php
		$trackingData = 'class="ResultLink" data-wid="' . $resultSet->getHeader('wid') . '" data-gpos="' . $pos
			. '" data-pos="0" data-sterm="' . addslashes($query) . '" data-stype="' .( $isInterWiki ? 'inter' :
			'intra' ) . '" data-rver="' . WikiaSearchController::RVERSION . '" data-event="search_click_wiki"';
		?>

        <img src="<?= $imageURL; ?>" alt="<?= $resultSet->getHeader('title'); ?>" class="wikiPromoteThumbnail grid-1 alpha" />
        <div class="grid-5 result-description">

            <h1>
                <a href="<?= $resultSet->getHeader('host'); ?>" <?=$trackingData;?> ><?= $resultSet->getHeader
				('wikititle'); ?></a>
            </h1>

            <p class="hub subtle"><?= strtoupper( $resultSet->getHeader( 'hub' ) ); ?></p>
            <p class="description"><?= $resultSet->getHeader('description'); ?></p>

            <ul class="wiki-statistics subtle">
                <li><?= wfMsg( 'wikiasearch2-pages', $wg->Lang->formatNum($resultSet->getHeader('articles_count')) ); ?></li>
                <li><?= wfMsg( 'wikiasearch2-images', $wg->Lang->formatNum($resultSet->getHeader('images_count')) ); ?></li>
                <li><?= wfMsg( 'wikiasearch2-videos', $wg->Lang->formatNum($resultSet->getHeader('videos_count')) ); ?></li>
            </ul>
        </div>
    </li>
<?php else: ?>
	<?= $app->getView( 'WikiaSearch', 'CrossWiki_exactResult', array(
		'resultSet' => $resultSet,
		'gpos' => 0,
		'pos' => $pos,
		'query' => $query,
		'rank' =>  $resultSet->getHeader('cityRank'),
		'imageURL' => $imageURL
		)); ?>
<?php endif; ?>
