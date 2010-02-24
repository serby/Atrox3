<?php
	/**
	 * @package Core
	 * @subpackage Cache
	 * @copyright Clock Limited 2010
	 * @version 3.2 - $Revision$ - $Date$
	 */

	/**
	 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
	 * @copyright Clock Limited 2010
	 * @version 3.2 - $Revision$ - $Date$
	 * @package Core
	 * @subpackage Cache
	 */
	class CacheControl {

		var $maxWebPageCacheSize = 20971520;
		var $maxBinaryCacheSize = 67108864;
		var $webPageCacheRemove = 0.25;
		var $filename = 1;
		var $caching = false;

		function clearBinaries($binary) {
			$application = CoreFactory::getApplication();
			$fileSystemControl = CoreFactory::getFileSystem();
			$fileSystemControl->deleteFiles($application->registry->get("Cache/Binary/Path") . "/" . $binary->get("HashValue"));
		}

		function cacheOutputBuffer($section) {
			CacheControl::isCaching($section, true);
			ob_start(array("CacheControl", "writeOutputBufferToDisk"));
		}

		function writeOutputBufferToDisk($buffer) {
			$filename = CacheControl::getSetFilename();
			$handle = fopen($filename, "w+");
			$written = fwrite($handle, $buffer);
			fclose($handle);
			if ($written == 0) {
				unlink($filename);
			}
			return $buffer;
		}

		function getSetFilename($filename = null) {
			static $lastFilename;
			if ($filename != null) {
				$lastFilename[] = $filename;
				return $filename;
			}
			$filename = array_pop($lastFilename);
			return $filename;
		}

		function getWebPageCache($section) {
			$this->isCached($section);
			$this->showCached($section);
		}
		
		function isCached($section, $ignoreUri = false, $expiryTime = null) {
			// Warning: No caching occurs in debug mode
			$application = &CoreFactory::getApplication();				
			return CacheControl::isCachedQuick($section, $application->registry->get("Cache/WebPages/Path"), $ignoreUri, $expiryTime);
		}
		
		function isCachedQuick($section, $path, $ignoreUri = false, $expiryTime = null) {

			if (defined("DEBUG_MODE") && DEBUG_MODE) {
				return false;
			}			
			
			$filename = $path . "/{$section}_" . ($ignoreUri ? "" : crc32(mb_strtolower($_SERVER["REQUEST_URI"])));
			$timeFile = "$filename.dat";

			if (is_file($timeFile)) {
				if (time() >= file_get_contents($timeFile)) {
					unlink($filename);
					unlink($timeFile);
				}
			} else if ($expiryTime != null) {
				$handle = fopen($timeFile, "w+");
				fwrite($handle, $expiryTime);
				fclose($handle);
			}
			
			CacheControl::getSetFilename($filename);
			
			if (is_file($filename)) {
				return true;
			} else {
				CacheControl::cacheOutputBuffer($section);
				return false;
			}
		}

		function isCaching($section, $set = null) {
			static $caching;
			if ($set != null) {
				$caching[$section] = $set;
			}
			return isset($caching[$section]) && $caching[$section];
		}
		
		function showCached($section) {
			
			if (defined("DEBUG_MODE") && DEBUG_MODE) {
				return false;
			}
						
			if (CacheControl::isCaching($section)) {
				ob_end_flush();
			} else {
				$filename = CacheControl::getSetFilename();
				echo file_get_contents($filename);
			}			
			CacheControl::isCaching($section, false);			
		}

		function deleteWebPageCache($section = null) {
			$application = CoreFactory::getApplication();
			if ($section != null) {
				$this->deleteGlob($application->registry->get("Cache/WebPages/Path") . "/$section" . "_*");
			} else {
				$this->deleteGlob($application->registry->get("Cache/WebPages/Path") . "/*");
			}
		}

		function deleteListCache($section) {
			$application = CoreFactory::getApplication();
			$this->deleteGlob($application->registry->get("Cache/Lists/Path") . "/$section" . "_*");
		}

		function deleteGlob($glob) {
			$files = glob($glob);
			if (is_array($files)) {
				foreach ($files as $filename) {
					unlink($filename);
				}
			}
		}

		function clearWebPageCache() {
			$application = CoreFactory::getApplication();
			$this->makeDir($application->registry->get("Cache/WebPages/Path"));
			$this->deleteGlob($application->registry->get("Cache/WebPages/Path") . "/*");
		}

		function clearBinaryCache() {
			$application = CoreFactory::getApplication();
			$this->makeDir($application->registry->get("Cache/Binaries/Path"));
			$this->deleteGlob($application->registry->get("Cache/Binaries/Path") . "/*");
		}

		function clearListCache() {
			$application = CoreFactory::getApplication();
			$this->makeDir($application->registry->get("Cache/Lists/Path"));
			$this->deleteGlob($application->registry->get("Cache/Lists/Path") . "/*");
		}

		function manageWebPageCache() {
			$currentSize = $this->getWebPageCacheSize();
			if ($currentSize > $this->maxWebPageCacheSize) {
				$application = CoreFactory::getApplication();
				$handle = opendir($application->registry->get("Cache/WebPages/Path"));
				$files = array();
				while ($file = readdir($handle)) {

					if (filetype($application->registry->get("Cache/WebPages/Path") . "/" .$file) == "file") {
						$temp["Name"] = $file;
						$temp["Size"] = filesize($application->registry->get("Cache/WebPages/Path") . "/" .$file);
						$temp["Time"] = filectime($application->registry->get("Cache/WebPages/Path") . "/" . $file);
						$files[] = $temp;
					}
				}
				usort($files, array("CacheControl", "compare"));
				$sumSize = 0;
				$targetSize = ($currentSize - $this->maxWebPageCacheSize) + ($this->maxWebPageCacheSize * $this->webPageCacheRemove);
				foreach($files as $v) {
					$sumSize += $v["Size"];
					unlink($application->registry->get("Cache/WebPages/Path") . "/" . $v["Name"]);
					if ($sumSize > $targetSize) {
						return;
					}
				}
			}
		}

		function compare(&$a, $b) {
			if($a["Time"] < $b["Time"]) {
				return -1;
			} else if($a["Time"] > $b["Time"]) {
				return 1;
			} else {
				return 0;
			}
		}

		function getWebPageCacheSize() {
			$application = CoreFactory::getApplication();
			$handle = opendir($application->registry->get("Cache/WebPages/Path"));
			$size = 0;
			while ($file = readdir($handle)) {
				$size += filesize($application->registry->get("Cache/WebPages/Path") . "/" .$file);
			}
			return $size;
		}

		function getWebPageMaxCacheSize() {
			return $this->maxWebPageCacheSize;
		}

		function getBinaryCacheSize() {
			return filesize($application->registry->get("Cache/Binaries/Path"));
		}

		function getBinaryMaxCacheSize() {
			return $this->maxBinaryCacheSize;
		}

		function getListsCacheSize() {
			$handle = opendir($application->registry->get("Cache/Lists/Path"));
			$size = 0;
			while ($file = readdir($handle)) {
				$size += filesize($application->registry->get("Cache/Lists/Path") . "/" .$file);
			}
			return $size;
		}

		function makeDir($path) {
			if (!is_dir($path)) {
				@mkdir($path, null, true);
			}
		}

		function getCacheList($name, $dataControl, $fieldName) {
			$application = CoreFactory::getApplication();
			$filename = $application->registry->get("Cache/Lists/Path") . "/$name" . "_" . "$fieldName";
			if (is_file($filename)) {
				$output = file_get_contents($filename);
			} else {
				$tempArray = array();
				while ($data = $dataControl->getNext()) {
					$tempArray[] = "\"" . str_replace('"', '\"', $data->get($fieldName)) . "\"";
				}
				$output = "return array(" . implode(", ", $tempArray)  . ");";
				$handle = fopen($filename, "w+");
				$written = fwrite($handle, $output);
				fclose($handle);
				if ($written == 0) {
					unlink($filename);
				}
			}
			return eval($output);
		}
	}
