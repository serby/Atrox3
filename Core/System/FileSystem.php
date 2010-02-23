<?php
/**
 * @package Core
 * @subpackage System
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Useful functions for working with files and directories
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 * @subpackage System
 */

class FileSystem {
	/**
	 * Create a new directory path, creating sub directories if they don't exist
	 * @param String Directory path to create
	 * @param Integer Mode to apply on the directory
	 * @return Boolean Return true on success, else false
	 */
	function mkdir($path, $mode = null, $recursive = false) {
		
		$return = true;
		
		if ($recursive) {
			$directories = explode('/', $path);
			$currentPath = "";
			foreach ($directories as $part) {
				$currentPath .= $part . '/';
				if ((!file_exists($currentPath)) && (!is_dir($currentPath)) && (mb_strlen($currentPath) > 0)) {
					$return = $return && mkdir($currentPath, $mode);
				}
			}
		} else {
			$return =  mkdir($path, $mode);
		}
		return $return;
	}
	/**
	 * WARNING VERY VERY VERY DANAGEROUS FUNCTION!!!!!!!!
	 * Recursively delete any sub folders and files in the path
	 * @param String Directory path to delete 
	 * @return Boolean Return true on success, false else
	 */
	function deleteFiles($path) {
		
		// This switch is used to reduce the chances of the whole directory structure being delete.
		switch ($path) {
			case null:
			case "/":
			case "/etc":
			case "/var":
			case "/webpub":
			case "/webpub/sites":
			case "/mnt":
				return false;
				break;
		}
		$hiddenFiles = glob("$path/.*");
		$files = glob("$path/*");
		$files = array_merge($hiddenFiles, $files);
		foreach ($files as $file) {
			if (is_dir($file)) {
				if (($file != "$path/.") && ($file != "$path/..")) {
					$this->deleteFiles($file);
				}
			} else {
				@unlink($file);
			}
		}
		@rmdir($path);
	}
}