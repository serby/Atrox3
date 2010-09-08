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

	/**
	 * The path to the site root.
	 * @var string
	 */
	protected $sitePath;

	/**
	 * The path to the cache location.
	 * @var string
	 */
	protected $cachePath;

	/**
	 * The URL to the cache location.
	 * @var string
	 */
	protected $cacheUrl;

	/**
	 * The delegate for aggregating the resources.
	 * @var IResourceAggregatorDelegate
	 */
	protected $delegate;

	/**
	 * The processor to apply to the aggregated resources.
	 * @var IProcessor
	 */
	protected $processor;

	/**
	 * Collection of all the resources to aggregate.
	 * @var Array
	 */
	protected $resources = array();

	/**
	 * Sets up the aggregator and assigns parameters to variables.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $sitePath
	 * @param string $cachePath
	 * @param string $cacheUrl
	 * @param IResourceAggregatorDelegate $delegate
	 * @param IProcessor $processor
	 */
	public function __construct($sitePath, $cachePath, $cacheUrl = null, IResourceAggregatorDelegate $delegate,
		IProcessor $processor) {

		$this->sitePath = $sitePath;
		$this->cachePath = $cachePath;
		$this->cacheUrl = $cacheUrl;

		$this->delegate = $delegate;
		$this->processor = $processor;
	}

	/**
	 * Adds the given resources to the specified group, using the options if provided.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param array $resources Collection of resources to aggregate.
	 * @param string $group The group to collect the resources in.
	 * @param stdClass $options
	 */
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

	/**
	 * Creates a group with the specified name, and sets it with the given options.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $name The name of the group to create.
	 * @param stdClass $options The options for the group.
	 *
	 * @return stdClass The created group.
	 */
	protected function createGroup($name, stdClass $options) {
		$group = new stdClass();
		foreach ($options as $key => $value) {
			$group->$key = $options->$key;
		}
		$group->name = $name;

		return $group;
	}

	/**
	 * Creates an item to be aggregated.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $type The type of item, used to determine how it is aggregated.
	 * @param string $value The value of the item.
	 */
	protected function createItem($type, $value) {
		$item = new stdClass();
		$item->type = $type;
		$item->value = $value;

		return $item;
	}

	/**
	 * Aggregates the resources and generates the HTML to include it on page.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @return string The generated HTML include.
	 */
	public function output($postfix = "") {

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
			$response .= $this->delegate->makeHtml($this->cacheUrl . "/" . $filename . $postfix, $resourceGroup);
		}

		unset($this->resources);
		$this->resources = array();
		return $response;
	}

	/**
	 * Gets the contents of a resource file and processes it with the aggregators processor.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $filePath The path to the resource file.
	 *
	 * @return string The processed content of the resource file.
	 */
	protected function processFile($filePath) {
		$contents = file_get_contents($filePath);
		return $this->processor->process($contents);
	}

	/**
	 * Starts the collection of on-page resources in the output buffer.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 *
	 * @param string $group The group to assign the collected resource to.
	 */
	public function start($group = "default") {
		ob_start();
	}

	/**
	 * Ends the collection of on-page resources, saves the output buffer and clears it.
	 *
	 * @author Dom Udall <dom.udall@clock.co.uk>
	 */
	public function end() {
		$this->resources["default"]->items[] = $this->createItem("content", ob_get_contents());
		ob_end_clean();
	}
}