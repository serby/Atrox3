<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2011
 * @version @VERSION-NUMBER@
 */

/**
 * Sitemap Generator
 * Generates valid XML Sitemaps
 *
 * @author Elliot Coad (Clock Ltd) {@link mailto:elliot.coad@clock.co.uk}
 * @copyright Clock Limited 2010
 * @version @VERSION-NUMBER@
 * @package Base
 * @subpackage Internet
 */
 class SitemapGenerator {
 	/**
 	 * @var string
 	 */
 	protected $outputLocation;

 	/**
 	 * @var array
 	 */
 	protected $items;

 	/**
 	 * @param string $outputLocation
 	 */
 	public function setSitemapOutputLocation($outputLocation) {
		$this->outputLocation = $outputLocation;
 	}

 	/**
 	 * Set an item
 	 * @param string $url
 	 * @param string $lastModified
 	 * @param string $changeFreqency
 	 * @param string $priority
 	 */
 	public function setItem($url, $lastModified, $changeFreqency = "always", $priority = "1") {
		$this->items[] = <<<XML
	<url>
		<loc>$url</loc>
		<lastmod>$lastModified</lastmod>
		<changefreq>$changeFreqency</changefreq>
		<priority>$priority</priority>
	</url>
XML;
 	}

	/**
	 * Save the Sitemap to the output location
	 */
	public function save() {
		$fileContent = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
XML;

		$fileContent .= implode(PHP_EOL, $this->items);
		$fileContent .= "</urlset>";

		file_put_contents($this->outputLocation, $fileContent);
 	}
 }