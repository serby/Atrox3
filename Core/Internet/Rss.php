<?php
/**
 * @package Core
 * @subpackage Internet
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Validation.php so that ValidationControl can be extended.
 */
require_once("Atrox/Core/Data/Validation.php");

/**
 * Parse XML feeds into RSS standard format
 *
 * @author Dom Udall(Clock Limited) Dom Udall {@link mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage Internet
 */

class RssReader {

	/**
	 * SimpleXML object of items from the RSS feed
	 * @access private
	 * @var Object
	 */
	private $feedItems = false;

	/**
	 * SimpleXML object of the channel from the RSS feed
	 * @access private
	 * @var Object
	 */
	private $feedChannel = false;

	function RssReader($url = null, $expiryTime = null) {
		if ($url) {
			return $this->getFeed($url, $expiryTime);
		}
		return true;
	}

	/**
	 * Reads an RSS feed from a URL or file, and creates a cached version to reference
	 *
	 * @author Dom Udall { mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @param String $feedPath The RSS feed (file or URL)
	 * @param Integer $expiryTime Number of seconds before the feed cache is refreshed (default 24h)
	 * @return Array The feed as an Array
	 */
	function getFeed($feedPath, $expiryTime = 86400) {

		if (($feedPath == "") || (!is_int($expiryTime))) {
			return false;
		}

		$feedContent = $this->getCache($feedPath, $expiryTime);

		//TODO: This seems to behave differently on different enviroments. i.e. Uri can parse a document sets as utf-16 yet containing utf-8 characters, but the local stack running 5.2.9 can't
		if (!$feedXml = @simplexml_load_string(utf8_encode($feedContent), "SimpleXMLElement", LIBXML_NOCDATA + LIBXML_NOERROR)) {
			return false;
		}

		if (
			!isset($feedXml) ||
			empty($feedXml) ||
			!isset($feedXml->channel) ||
			empty($feedXml->channel) ||
			!isset($feedXml->channel->title)) {
				return false;
		}

		if (isset($feedXml->item) && !empty($feedXml->item)){
			$feedItems = $feedXml->item;
		} else {
			$feedItems = $feedXml->channel->item;
		}

		if (!isset($feedItems) && empty($feedItems)) {
			return false;
		}

		$namespaces = $feedXml->getNamespaces(TRUE);
		foreach ($feedItems as $item) {
			foreach ($namespaces as $namespaceName => $namespaceUri) {
				if (!empty($namespaceName)) {
			  	$childItem = $item->children($namespaceUri);
			  	foreach ($childItem as $name) {
			  		$child = $item->addChild($namespaceName . "_" . $name->getName(), htmlentities($name));
			  		foreach ($name->attributes() as $attName => $attValue) {
			  			$child->addAttribute($attName, $attValue);
			  		}
			  	}
				}
			}
		}
		$this->feedChannel = $feedXml->channel;
		$this->feedItems = $feedItems;

		return true;
	}

	/**
	 * Validates an RSS feed from a URL or file
	 *
	 * @author Dom Udall { mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @param String $feedPath The RSS feed (file or URL)
	 * @return Boolean Whether the feed has passed validation
	 */
	function validate($feedPath = null) {

		if (!empty($feedPath)) {
			if (!$this->getFeed($feedPath)) {
				return false;
			}
		}

		if (
			is_object($this->feedChannel) &&
			is_object($this->feedItems)) {
				return true;
		}

		return false;
	}

	/**
	 * Gets a cached RSS feed from a file, or creates it if it doesn't exist
	 *
	 * @author Dom Udall { mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @param String $feedPath The RSS feed (file or URL)
	 * @param Integer $expiryTime Number of seconds before the feed cache is refreshed (default 0s)
	 * @return String The cached RSS feed
	 */
	function getCache($feedPath, $expiryTime = 0) {
		if (
			!is_int($expiryTime) ||
			!is_string($feedPath)) {
			return false;
		}

		$cachedFeedName = crc32($feedPath);

		$application = CoreFactory::getApplication();
		$filename = $application->registry->get("Cache/Rss/Path") . "/XML_" . $cachedFeedName;

		$fileDetails = @stat($filename);
		if ($fileDetails["ctime"] + $expiryTime <= time() && is_file($filename)) {
			unlink($filename);
		}

		if (is_file($filename)) {
			$content = @file_get_contents($filename);
		} else {
			$handle = fopen($filename, "w+");
			$written = fwrite($handle, $content = @file_get_contents($feedPath));
			fclose($handle);
			if ($written == 0) {
				unlink($filename);
				return false;
			}
		}
		return $content;
	}

	/**
	 * Returns the items variable from this class
	 *
	 * @author Dom Udall { mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @return Object The feed items as a SimpleXML Object
	 */
	function getFeedItems() {
		return $this->feedItems;
	}

	/**
	 * Returns the items variable from this class
	 *
	 * @author Dom Udall { mailto:dom.udall@clock.co.uk dom.udall@clock.co.uk }
	 * @return Object The feed items as a SimpleXML Object
	 */
	function getFeedChannel() {
		return $this->feedChannel;
	}
}

/**
 * Creates an RSS feed using a dataControl object
 *
 * @author Paul Serby(ClockLtd) Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @package Core
 */
class RssFeeder {

	/**
	 * Title of the RSS feed
	 * @access private
	 * @var String
	 */
	var $title = null;

	/**
	 * Description for the RSS feed
	 * @access private
	 * @var String
	 */
	var $description = null;

	/**
	 * URL to web site
	 * @access private
	 * @var String
	 */
	var $link = null;

	/**
	 * Link to RSS feeds logo
	 * @access private
	 * @var String
	 */
	var $imageLink = null;

	/**
	 * Name of the dataControl object
	 * @access private
	 * @var String
	 */
	var $dataControl = null;

	/**
	 * Title of the feed item
	 * @access private
	 * @var String
	 */
	var $titleField = null;

	/**
	 * Database field to be associated with item description
	 * @access private
	 * @var String
	 */
	var $descriptionField = null;

	/**
	 * Database field to be associated with item ID
	 * @access private
	 * @var String
	 */
	var $idField = null;

	/**
	 * URL to item without ID attached
	 * @access private
	 * @var String
	 */
	var $resourceLink = null;

	/**
	 * Creates a new RssFeeder object
	 *
	 * @param String $dataControl Name of the dataControl object
	 * @param String $title Title of the RSS feed
	 * @param String $description Description for the RSS feed
	 * @param String $link URL to web site
	 * @param String $imageLink Link to RSS feeds logo
	 * @param String $titleField Title of the feed item
	 * @param String $descriptionField Database field to be associated with item description
	 * @param String $idField Database field to be associated with item ID
	 * @param String $resourceLink URL to item without ID attached
	 */
	function RssFeeder($dataControl, $title, $description, $link, $imageLink, $itemProcessor, $selfLink = null) {
		$this->title = $title;
		$this->description = $description;
		$this->link = $link;
		$this->selfLink = $selfLink;
		$this->imageLink = $imageLink;
		$this->dataControl = $dataControl;

		$this->rssItemProcessor = $itemProcessor;
		$this->rssItemProcessor->setBuilder($this);
	}

	/**
	 * Generates the RSS feed
	 *
	 * @return String The RSS feed
	 */
	function toString() {
		$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" .
		"<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";

		$output .= "\t<channel>\n";
		$output .= "\t\t<title>{$this->title}</title>\n";
		$output .= "\t\t<link>{$this->link}</link>\n";
		$output .= "\t\t<description>{$this->description}</description>\n";
		if ($this->imageLink != "") {
			$output .= "\t\t<image>\n";
			$output .= "\t\t\t<title>{$this->title}</title>\n";
			$output .= "\t\t\t<link>{$this->link}</link>\n";
			$output .= "\t\t\t<url>{$this->imageLink}</url>\n";
			$output .= "\t\t</image>\n";
		}

		$itemList = "";
		$crop = CoreFactory::getLeftCropEllipsisEncoder(50, true);

		while ($data = $this->dataControl->getNext()) {
			$itemList .= "\t<item>\n";
			$itemList .= "\t\t<title><![CDATA[" . $this->rssItemProcessor->getTitle($data) . "]]></title>\n";
			$itemList .= "\t\t<link>" . $this->rssItemProcessor->getLink($data) . "</link>\n";
			$itemList .= "\t\t<pubDate>" . $this->rssItemProcessor->getDate($data) . "</pubDate>\n";
			$itemList .= "\t\t<description><![CDATA[" . $this->rssItemProcessor->getDescription($data) . "]]></description>\n";
			$itemList .= "\t\t<guid>" . $this->rssItemProcessor->getLink($data) . "</guid>\n";
			$itemList .= "\t\t<author><![CDATA[" . $this->rssItemProcessor->getAuthor($data) . "]]></author>\n";
			$itemList .= "\t</item>\n";
		}

		$output .= $itemList;
		$output .= "\t<atom:link href=\"{$this->selfLink}\" rel=\"self\" type=\"application/rss+xml\" />";
		$output .= "\t</channel>\n";
		$output .= "</rss>\n";

		return $output;
	}

 /**
	* Stores the feed in a file
	*/
	function toFile($file) {
	//TODO: Output to file. Add function to cache
		//$this->dataControl->application->createSiteFileFromString($this->toString(), $file);
	}
}

class RssItemProcessor {

	function setBuilder(&$builder) {
		$this->builder = &$builder;
	}

	function getId($dataEntity) {
		return htmlentities($dataEntity->getFormatted($this->builder->idField));
	}

	function getTitle($dataEntity) {
		$crop = CoreFactory::getLeftCropEllipsisEncoder(50, true);
		$title = preg_replace("'(<.*?>|<\/.*?>|\[.*?\])'mis", "", $dataEntity->getFormatted($this->builder->titleField));
		$title = preg_replace("'(\r\n|\r|\n)'mis", " ", $title);
		return $title;
	}

	function getDescription($dataEntity) {
		return $dataEntity->getFormatted($this->builder->descriptionField);
	}

	function getLink($dataEntity) {
		return $this->builder->resourceLink . htmlentities($dataEntity->getFormatted($this->builder->idField));
	}

	function getAuthor($dataEntity) {
		return "paul.serby@clock.co.uk (Paul Serby)";
	}

	function getDate($dataEntity) {
		if ($this->builder->dateField) {
			return date("r", strtotime($dataEntity->get($this->builder->dateField)));
		} else {
			return "";
		}
	}
}