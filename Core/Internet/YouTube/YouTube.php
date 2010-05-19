<?php
/**
 * @package YouTube
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package YouTube
 */
class YouTube {
 	
 	function __construct() {
 	}
 	
	function getUploadMetadata($title, $description, $category, $keywords) {
		$xml = <<<XML
<?xml version="1.0"?>
<entry xmlns="http://www.w3.org/2005/Atom"
  xmlns:media="http://search.yahoo.com/mrss/"
  xmlns:yt="http://gdata.youtube.com/schemas/2007">
  <media:group>
    <media:title type="plain">{$title}</media:title>
    <media:description type="plain">
      {$description}
    </media:description>
    <media:category
      scheme="http://gdata.youtube.com/schemas/2007/categories.cat">People
      </media:category>
    <media:keywords>{$keywords}</media:keywords>
  </media:group>
</entry>
XML;
 		return $xml;
	}
 	
 	function uploadMetadata($developerKey, $authToken, $title, $description, $category, $keywords) {
 		
		$xml = $this->getUploadMetadata($title, $description, $category, $keywords);
	 	
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL,"http://gdata.youtube.com/action/GetUploadToken");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: GoogleLogin auth=\"{$authToken}\"",
			"X-GData-Key: key={$developerKey}",
			"GData-Version: 2",
			"Content-Type: application/atom+xml; charset=UTF-8",
			"Connection: close"		
		));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec($curl);
		curl_close($curl);
	 	$response = new SimpleXMLElement($response);
		return (string)$response->token; 
	}
	
	function uploadVideo($developerKey, $authToken, $title, $description, $category, $keywords, $videoFilepath) {
		
		if (!file_exists($videoFilepath)) {
			throw new Exception("'$videoFilepath' does not exist");
			return;
		}
		
		$mime = CoreFactory::getMime();
		$mime->addPart($this->getUploadMetadata($title, $description, $category, $keywords), "application/atom+xml; charset=UTF-8");
		
		$handle = fopen($videoFilepath, "r");
		$contents = fread($handle, filesize($videoFilepath));
		fclose($handle);

		$mime->addPart($contents, "application/octet-stream", Mime::ENCODING_BINARY);

		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL,"http://uploads.gdata.youtube.com/feeds/api/users/default/uploads");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: GoogleLogin auth=\"{$authToken}\"",
			"X-GData-Key: key={$developerKey}",
			"GData-Version: 2",
			"Slug: test.avi",
			"Content-Type: multipart/related; boundary=\"" . $mime->getBoundary() . "\"",
			"Connection: close"		
		));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $mime->toString());
		$response = curl_exec($curl);
		
		curl_close($curl);
		
		$xml = new SimpleXMLElement($response);
		foreach($xml->link as $link) {
			switch ($link["rel"]) {
				case "self":
					$return->checkLink = (string)$link["href"];
					break;
			}
		}
		$results = $xml->children("http://search.yahoo.com/mrss/")->children("http://gdata.youtube.com/schemas/2007");
		$return->videoId = (string)$results->videoid;
		return $return;
	}
	
	/**
	 * Returns the state of video uploaded to YouTube  
	 *
	 * @param String $checkLink 
	 * @return String The state of the uploaded video
	 */
	function getUploadedVideoState($checkLink) {
		
		$state = "unknown";
		
		if ($checkLink == "") {
			throw new Exception("Check Link URL must not be empty");
			return false;
		}
		
		$response = file_get_contents($checkLink);	
		
		try {
			$xml = new SimpleXMLElement($response);	
		} catch (Exception $e) {
			throw new Exception("Unexpected response. Should be a vaild YouTube API check response.");
			return false;
		}
		
		$results = $xml->children("http://search.yahoo.com/mrss/")->children("http://gdata.youtube.com/schemas/2007");
		$attributes = $results->duration->attributes();
		
		if ($attributes["seconds"] <= 0) {
			$results = $xml->children("http://purl.org/atom/app#")->children("http://gdata.youtube.com/schemas/2007");
			$attributes = $results->state->attributes();
			$state = (string)$attributes["name"];
		}	else {
			$state = "processed";
		}
		
		return $state; 
	}
	
	function stripVideoIdFromWatchUrl($watchUrl) {		
		if (preg_match("/http\:\/\/[^?]*\.youtube\.com\/watch\?v=([^&]*)/", $watchUrl, $matches)) {
			return $matches[1];
		}
		return false;
	}
	
	function makeWatchVideoUrl($videoId) {		
		return "http://www.youtube.com/watch?v=" . $videoId;
	}
	
	function makeThumbUrl($videoId) {		
		return "http://i1.ytimg.com/vi/{$videoId}/default.jpg?v=@VERSION-NUMBER@";
	}	
}