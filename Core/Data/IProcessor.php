<?php
/**
 * Interface for processors that take values and convert them.
 *
 * @author Dom Udall  <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1481 $ - $Date: 2010-05-19 19:09:03 +0100 (Wed, 19 May 2010) $
 * @package Core
 * @subpackage Data
 */
interface IProcessor {
	public function process($value);
}