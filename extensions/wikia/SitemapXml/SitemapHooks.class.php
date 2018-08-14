<?php

class SitemapHooks {

	/**
	 * Disable title redirects for sitemaps, as we want to use the original request url rather than Special:Sitemap.
	 */
	public static function onBeforeTitleRedirect( WebRequest $request, Title $title ): bool {
		if ( $title->isSpecial( 'Sitemap' ) ) {
			return false;
		}
		return true;
	}

	public static function onTestCanonicalRedirect( WebRequest $request, Title $title, OutputPage $output ) {
		// Question 1: do we cancel a redirect for all alternative domain?
		$output->cancelRedirect( false );
		if ( $output->isRedirect() ) {
			// there is still a protocol redirect left, proceed with redirecting
			return true;
		}
		header('X-sitemaps-redirect-cancelled: 1');
		// TBD: override the wgServer so the sitemap uses currently used address
		// this needs to be done because WFL used the address from city_url
		return false;
	}

}
