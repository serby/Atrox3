<?php
/**
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2
 * @package Core
 */
class Layout {

	/**
	 * The name of the template file.
	 *
	 * @var string
	 */
	protected $templateFilenamename;

	/**
	 * Holds the content set by set, add and start/end.
	 *
	 * @var array
	 */
	protected $content = array();

	/**
	 * The current section that should be set when end() is called.
	 *
	 * @var string
	 */
	protected $currentSection;

	/**
	 * @param string $templateFilename
	 */
	public function __construct($templateFilename) {
		$this->templateFilename = $templateFilename;
	}


	/**
	 * Sets or overwrites the $section with $content.
	 *
	 * @param string $section
	 * @param string $content
	 */
	public function set($section, $content) {
		$this->content[$section] = $content;
	}

	/**
	 * Returns the content from $section
	 *
	 * @param string $section
	 */
	public function get($section) {
		if (isset($this->content[$section])) {
			return $this->content[$section];
		} else {
			return null;
		}
	}

	/**
	 * Adds $content to $section
	 *
	 * @param string $section
	 * @param string $content
	 */
	public function add($section, $content) {
		if (isset($this->content[$section])) {
			$this->content[$section] .= $content;
		} else {
			$this->content[$section] = $content;
		}
	}

	/**
	 * Start capturing output which will be stored in $section when end() is called.
	 *
	 * @param string $section
	 */
	public function start($section) {
		$this->end();
		$this->currentSection = $section;
		ob_start();
	}

	/**
	 * Call after start to store the captured output into the current section.
	 *
	 */
	public function end() {
		if ($this->currentSection !== null) {
			$this->add($this->currentSection, ob_get_contents());
			ob_end_clean();
		}
	}

	/**
	 * Processes the template file replaceing the ${token} with the contents from the sections with the same name.
	 */
	public function render() {
		$this->end();
		include($this->templateFilename);
	}
}