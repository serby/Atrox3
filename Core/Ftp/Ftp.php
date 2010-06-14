<?php
/**
 * FTP Class
 *
 * @author Tom Smith (Clock Limited) {@link mailto:thomas.smith@clock.co.uk thomas.smith@clock.co.uk }
 * @copyright Clock Limited 2009
 * @version 3.0 - $Revision: 737 $ - $Date: 2008-09-08 16:09:48 +0100 (Mon, 08 Sep 2008) $
 * @package Core
 * @subpackage Ftp
 */
class Ftp {
	var $host;
	var $username;
	var $password;
	var $passive;
	var $ftpHandle;

	/**
	 * Make connection and login to ftp host
	 *
	 * @param string $host Remote ftp site
	 * @param string $username Username of remote ftp site
	 * @param string $password Password of remote ftp site
	 * @param string $passive Use ftp passive mode
	 * @return boolean If connection was made.
	 */
	function __construct($host, $username, $password, $passive = true) {

		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->passive = $passive;

		try {
			if (!$this->ftpHandle = @ftp_connect($this->host)) {
				throw new Exception("Unable to connect to ftp host: " . $this->host);
			}
		} catch (Exception $e) {
			trigger_error($e->getMessage());
		}

		try {
			$loginResult = @ftp_login($this->ftpHandle, $this->username, $this->password);

			if (!$loginResult) {
				throw new Exception("Unable to connect to ftp host, or Username and password are incorrect.");
			}
		} catch (Exception $e) {
			trigger_error($e->getMessage());
		}

		if ($this->passive) {
			ftp_pasv($this->ftpHandle, true);
		}
   	return true;
	}

	/**
	 * Close ftp connection.
	 *
	 * @return boolean
	 */
	function closeConnection() {
		return ftp_close($this->ftpHandle);
	}

	/**
	 * Is path a directory
	 *
	 * @param string $directory Directory Path
	 * @return boolean True if is a directory.
	 */
	function isDirectory($directory) {
		$isDir = $this->changeDirectory($directory);
		$this->changeToRootDirector();
		return $isDir;
	}

	/**
	 * Change to root directory
	 *
	 * @return boolean True if successful.
	 */
	function changeToRootDirector() {
		return @ftp_cdup($this->ftpHandle);
	}

	/**
	 * Change directory
	 *
	 * @param string $directory Directory to change to
	 * @return unknown
	 */
	function changeDirectory($directory) {
		return @ftp_chdir($this->ftpHandle, $directory);
	}

	/**
	 * Upload file
	 *
	 * @param string $source Path of local file
	 * @param string $destination Path of remote file
	 * @return boolean True if successful.
	 */
	function uploadFile($source, $destination) {
		return ftp_put($this->ftpHandle, $destination, $source, FTP_BINARY);
	}

	/**
	 * Make directory
	 *
	 * @param string $directory Directory Path to make.
	 * @return boolean True if successful.
	 */
	function makeDirectory($directory) {
		return @ftp_mkdir($this->ftpHandle, $directory);
	}

	/**
	 * Check whether the file exists on the remote host
	 *
	 * @param string $file Full path of file
	 * @return boolean True if file exists
	 */
	function doesFileExist($file) {
		if (ftp_mdtm($this->ftpHandle, $file) != -1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Download file from ftp server
	 *
	 * @param string $remoteFile Path to remote file
	 * @param string $localFile Where to save remote file too.
	 * @return boolean True if successfully downloaded.
	 */
	function downloadFile($remoteFile, $localFile) {
		return ftp_get($this->ftpHandle, $localFile, $remoteFile, FTP_BINARY);
	}

	/**
	 * Rename a file
	 *
	 * @param string $oldName the old name of the file
	 * @param string $newName the new name of the remote file
	 * @return boolean True if successful.
	 */
	function renameFile($oldName, $newName) {
		return ftp_rename($this->ftpHandle, $oldName, $newName);
	}
}