<?php
/**
 * Collects a number of resources and minifies them.
 *
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1480 $ - $Date: 2010-05-19 14:51:14 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Internet
 */
class ResourceAggregator {

	protected $sitePath;
	protected $cachePath;
	protected $cacheUrl;

	/**
	 * @var IResourceAggregatorDelegate
	 */
	protected $delegate;

	/**
	 * @var IProcessor
	 */
	protected $processor;

	protected $resources = array();

	public function __construct($sitePath, $cachePath, $cacheUrl = null, IResourceAggregatorDelegate $delegate, IProcessor $processor) {
		$this->sitePath = $sitePath;
		$this->cachePath = $cachePath;
		$this->cacheUrl = $cacheUrl;

		$this->delegate = $delegate;
		$this->processor = $processor;
	}

	public function collect(array $resources, $group = "default", stdClass $options = null) {
		$resourceGroup = isset($this->resources[$group]) ? $this->resources[$group] : null;

		$options = $this->delegate->validateOptions($options, $resourceGroup);

		if (!$resourceGroup) {
			$this->resources[$group] = $this->createGroup($group, $options);
		}

		foreach ($resources as $resource) {
			$this->resources[$group]->items[] = $this->createItem("resource", $resource);
		}
	}

	protected function createGroup($name, stdClass $options) {
		$group = new stdClass();
		foreach ($options as $key => $value) {
			$group->$key = $options->$key;
		}
		$group->name = $name;

		return $group;
	}

	protected function createItem($type, $value) {
		$item = new stdClass();
		$item->type = $type;
		$item->value = $value;

		return $item;
	}

	public function output() {

		$response = "";
		$files = "";

		foreach ($this->resources as $resourceGroup) {
			$processed = "";
			$filename = $resourceGroup->name . "." . $this->delegate->getFileExtension();
			$cachedFilePath = $this->cachePath . "/" . $filename;
			//if (!file_exists($cachedFilePath)) {

				foreach ($resourceGroup->items as $item) {
					switch ($item->type) {
						case "resource":
							$files .= ":" . $item->value;
							$processed .= $this->processFile($this->sitePath . "/" . $item->value);
							break;
						case "content":
							$processed .= $this->processor->process($item->value);
							break;
					}
				}
				file_put_contents($cachedFilePath, $processed);
			//}
			$response .= $this->delegate->makeHtml($this->cacheUrl . "/" . $filename, $resourceGroup);
		}

		unset($this->resources);
		$this->resources = array();
		return $response;
	}

	/**
	 *
	 * @param string $filePath
	 * @return string
	 */
	protected function processFile($filePath) {
		$contents = file_get_contents($filePath);
		return $this->processor->process($contents);
	}

	public function start($group = "default") {
		ob_start();
	}

	public function end() {
		$this->resources["default"]->items[] = $this->createItem("content", ob_get_contents());
		ob_end_clean();
	}
}