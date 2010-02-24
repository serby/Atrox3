<?php

/**
 * @package Core
 * @subpackage Cache
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1117 $ - $Date: 2009-08-15 23:56:20 +0100 (Sat, 15 Aug 2009) $
 */

/**
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 1117 $ - $Date: 2009-08-15 23:56:20 +0100 (Sat, 15 Aug 2009) $
 * @package Core
 * @subpackage Cache
 */
class MemcachedControl {

	/**
	 *
	 * @var memcache
	 */
	private $memcache;

	/**
	 *
	 * @var array
	 */
	private $startStack;

	/**
	 * Prefix all the memcache keys with the following
	 * @var string
	 */
	private $keyPrefix;

	/**
	 *
	 * @param $keyPrefix
	 * @return unknown_type
	 */
	function __construct($keyPrefix = "") {
		$this->memcache = new Memcache();
		$this->keyPrefix = $keyPrefix . ":";
	}

	/**
	 *
	 * @param string $server
	 * @param int $port
	 * @return Atrox_Core_Cache_memcache
	 */
	function addServer($server = "127.0.0.1", $port = 11211) {
		$this->memcache->addServer($server, $port);
		return $this;
	}

	/**
	 *
	 * @see Core/Cache/Atrox_Core_Cache_ICache#set($key, $data, $tag, $expire)
	 */
	function set($key, $data, $tag = false, $expire = false) {
		$this->memcache->set($this->keyPrefix . $key, $data, false, $expire);
		if ($tag) {
			if (!$tagIndex = $this->memcache->get($this->keyPrefix . "__AtroxTagIndex")) {
				$tagIndex = array();
			}
			$tagIndex[$tag][] = $key;
			$this->memcache->set($this->keyPrefix . "__AtroxTagIndex", $tagIndex);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#get($key)
	 */
	function get($key) {
		return $this->memcache->get($this->keyPrefix . $key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#get($key)
	 */
	function getWithoutPrefix($key) {
		return $this->memcache->get($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#start($key, $tag)
	 */
	function start($key, $tag = false, $expire = false) {
		if ($content = $this->memcache->get($this->keyPrefix . $key)) {
			echo $content;
			return false;
		} else {
			$this->startStack[] = array($key, $tag, $expire);
			ob_start(array($this, "writeOutputBufferToCache"));
		}
		return true;
	}

	/**
	 *
	 * @param $buffer
	 * @return unknown_type
	 */
	function writeOutputBufferToCache($buffer) {
		$details = array_pop($this->startStack);
		$this->set($details[0], $buffer, $details[1], $details[2]);
		return $buffer;
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#end()
	 */
	function end() {
		$contents = ob_get_contents();
		ob_end_flush();
		//echo $contents;
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#clearAll()
	 */
	function clearAll() {
		$this->memcache->flush();
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#clear($key)
	 */
	function clear($key) {
		$this->memcache->delete($this->keyPrefix . $key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#get($key)
	 */
	function clearWithoutPrefix($key) {
		$this->memcache->delete($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#clearTag($tag)
	 */
	function clearTag($tag) {
		if ($tagIndex = $this->memcache->get($this->keyPrefix . "__AtroxTagIndex")) {
			if (isset($tagIndex[$tag])) {
				foreach ($tagIndex[$tag] as $key) {
					$this->clear($key);
					unset($tagIndex[$tag]);
				}
			}
			$this->memcache->set($this->keyPrefix . "__AtroxTagIndex", $tagIndex);

		} else {
			$this->clearAll();
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#getFileContents($filename, $expire, $context)
	 */
	function getFileContents($filename, $expire = false, $context = null) {
		$key = $this->keyPrefix . "__File:" . md5($filename);
		if ($data = $this->memcache->get($key)) {
 			return $data;
		} else {
			try {
 				$data = @file_get_contents($filename, 0, $context);
 				$this->memcache->set($key, $data, false, $expire);
			} catch(Exception $e) {
				echo $e->getMessage();
				throw new Exception("'{$filename}' does not exist");
			}
		}
		return $data;
	}

	/**
	 * (non-PHPdoc)
	 * @see Atrox/Core/Cache/Atrox_Core_Cache_ICache#clearFileContents($filename)
	 */
	function clearFileContents($filename) {
		$key = "__File:" . md5($filename);
		$this->clear($key);
	}

	function listContents($filter = null) {
    $list = array();
    $allSlabs = $this->memcache->getExtendedStats("slabs");
    $items = $this->memcache->getExtendedStats("items");
    foreach ($allSlabs as $server => $slabs) {
    	foreach ($slabs as $slabId => $slabMeta) {
    		$cdump = $this->memcache->getExtendedStats("cachedump", (int)$slabId);
    		foreach ($cdump as $server => $entries) {
    			if($entries) {
    				foreach($entries as $eName => $eData) {
    					$list[] = $eName;
    				}
    			}
    		}
    	}
    }
    sort($list);
    return $list;
	}
}