<?php
class OpenGraph {

	protected $metaData = array();

	/**
	 * @param $title
	 * @return OpenGraph
	 */
	public function setTitle($title) {
		$item = new stdClass();
		$item->property = "og:title";
		$item->value = $this->formatInput($title);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $type
	 * @return OpenGraph
	 */
	public function setType($type) {
		$item = new stdClass();
		$item->property = "og:type";
		$item->value = $this->formatInput($type);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $url
	 * @return OpenGraph
	 */
	public function setUrl($url) {
		$item = new stdClass();
		$item->property = "og:url";
		$item->value = $this->formatInput($url);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $image
	 * @return OpenGraph
	 */
	public function setImage($image) {
		$item = new stdClass();
		$item->property = "og:image";
		$item->value = $this->formatInput($image);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $siteName
	 * @return OpenGraph
	 */
	public function setSiteName($siteName) {
		$item = new stdClass();
		$item->property = "og:site_name";
		$item->value = $this->formatInput($siteName);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $image
	 * @return OpenGraph
	 */
	public function setAdmins($admins) {
		$item = new stdClass();
		$item->property = "fb:admins";
		$item->value = $this->formatInput($admins);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @param $description
	 * @return OpenGraph
	 */
	public function setDescription($description) {
		$item = new stdClass();
		$item->property = "og:description";
		$item->value = $this->formatInput($description);
		$this->metaData[] = $item;
		return $this;
	}

	/**
	 * @return string OpenGraph metadata
	 */
	public function getMetaData() {
		$metaDataFormatted = array();

		foreach ($this->metaData as $metaDataItem) {
			$metaDataFormatted[] = "<meta property=\"{$metaDataItem->property}\" content=\"{$metaDataItem->value}\"/>";
		}

		return implode(PHP_EOL, $metaDataFormatted);
	}

	/**
	 * Add these to the html tag
	 * @return string
	 */
	public function getOpenGraphNameSpaces() {
		return "xmlns:og=\"http://ogp.me/ns#\" xmlns:fb=\"http://www.facebook.com/2008/fbml\"";
	}

	/**
	 * @param string $input
	 * @return string HTML safe output
	 */
	protected function formatInput($input) {
		return htmlentities($input, ENT_COMPAT, "UTF-8");
	}
}