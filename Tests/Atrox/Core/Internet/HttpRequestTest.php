<?php
require_once "Atrox/Core/Internet/HttpRequest.php";

/**
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 */
class Atrox_Core_Internet_HttpRequestTest extends PHPUnit_Framework_TestCase {

	protected function setup() {
	}

	public function testLargeHttpBodysAreFullyReturned() {
		$httpRequest = new HttpRequest("http://www.random.org/strings/?num=10000&len=20&digits=on&unique=on&format=html&rnd=new");

		$repsonse = $httpRequest->send();
		$this->assertEquals($repsonse, "");
	}
}