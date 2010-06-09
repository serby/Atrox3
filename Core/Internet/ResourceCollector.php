<?php
/**
 * Collects a number of resources and minifies them.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
abstract class ResourceCollector {

	protected $sitePath;
	protected $cachePath;
	protected $cacheUrl;

	protected $resources = array();

	public function __construct($sitePath, $cachePath, $cacheUrl = null) {
		$this->sitePath = $sitePath;
		$this->cachePath = $cachePath;
		$this->cacheUrl = $cacheUrl;
	}

	public function collect(array $resources, $group = "default", $media = "all") {

		if (isset($this->resources[$group])) {
			if ($media != $resourceGroup->media) {
				throw new Exception("Unable to change the media type of a existing group");
			}
			$this->resources[$group]->resources += $resources;
		} else {
			$resourceGroup = new stdClass();
			$resourceGroup->media = $media;
			$resourceGroup->name = $group;
			$resourceGroup->items = array();
			$item = new stdClass();
			$item->resource = "";
			$item->content = "";
			$this->resources[$group] = $resourceGroup;
		}
	}

	protected function createGroup() {
		$group = new stdClass();
		$group->media = $media;
		$group->name = $group;
		$group->resources = $resources;
		$group->content = array();
	}

	public function output() {

		$response = "";
		$files = "";

		foreach ($this->resources as $resources) {
			$minifiedCss = "";
			$filename = $resources->name . "." . $this->getFileExtention();
			$cachedFilePath = $this->cachePath . "/" . $filename;
			//if (!file_exists($cachedFilePath)) {

				foreach ($resources->resources as $resource) {
					$files .= ":" . $resource;
					$minifiedCss .= $this->minifyFile($this->sitePath . "/" . $resource);
				}
				if ($resource->content) {
					$minifiedCss .= $this->minify($resource->content);
				}
				file_put_contents($cachedFilePath, $minifiedCss);
			//}
			$response .= $this->makeHtml($this->cacheUrl . "/" . $filename, $resources->media);
		}

		unset($this->resources);
		$this->resources = array();
		return $response;
	}

	abstract public function getFileExtention();
	abstract public function makeHtml($filename, $other);

	/**
	 *
	 * @param $source
	 */
	abstract protected function minify($source);

	/**
	 *
	 * @param string $filePath
	 * @return string
	 */
	protected function minifyFile($filePath) {
		$contents = file_get_contents($filePath);
		return $this->minify($contents);
	}

	public function start($group) {
		ob_start(array($this, "end"));
	}

	public function end($buffer) {
		$this->resources["default"]->content = $buffer;
		ob_end_clean();
	}
}