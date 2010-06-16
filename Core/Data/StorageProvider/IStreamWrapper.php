<?php
/**
 * @author Dom Udall <dom.udall@clock.co.uk>
 * @copyright Clock Limited 2010
 * @version 3.0 - $Revision: 777 $ - $Date: 2008-10-30 12:49:04 +0000 (Thu, 30 Oct 2008) $
 * @package Data
 * @subpackage StorageProvider
 */
interface Atrox_Core_Data_StorageProvider_IStreamWrapper {
	/**
	 * Called when opening the stream wrapper, right before streamWrapper::stream_open.
	 */
	public function __construct();

	/**
	 * This method is called in response to closedir().
	 * Closes the directory stream. The stream must have previously been opened by opendir().
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function dir_closedir();

	/**
	 * This method is called in response to opendir().
	 *
	 * @param string $path Specifies the URL that was passed to opendir().
	 * @param int $options Whether or not to enforce safe_mode.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function dir_opendir($path , $options);

	/**
	 * Returns the filename of the next file from the directory. The filenames are returned in the order in which they are
	 * stored by the filesystem.
	 *
	 * @return string Should return string representing the next filename, or FALSE if there is no next file.
	 */
	public function dir_readdir();

	/**
	 * Resets the directory stream to the beginning of the directory.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function dir_rewinddir();

	/**
	 * Attempts to create the directory specified by pathname.
	 *
	 * @param string $path The directory path.
	 * @param int $mode The mode is 0777 by default, which means the widest possible access. For more information on
	 * modes, read the details on the chmod() page.
	 * @param int $options A bitwise mask of values, such as STREAM_MKDIR_RECURSIVE.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function mkdir($path , $mode , $options);

	/**
	 * Attempts to rename pathFrom to pathTo.
	 *
	 * @param string $pathFrom The URL to the current file.
	 * @param string $pathTo The URL which the pathFrom should be renamed to.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function rename($pathFrom , $pathTo);

	/**
	 * Attempts to remove the directory named by path. The directory must be empty, and the relevant permissions must
	 * permit this. A E_WARNING level error will be generated on failure.
	 *
	 * @param string $path The directory URL which should be removed.
	 * @param int $options A bitwise mask of values, such as STREAM_MKDIR_RECURSIVE.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function rmdir($path , $options);

	/**
	 * Waits for the stream change status. Its operation is equivalent to that of the socket_select() function, except
	 * that it acts on a stream.
	 *
	 * @param int $castAs Can be STREAM_CAST_FOR_SELECT when stream_select() is calling stream_cast() or
	 * STREAM_CAST_AS_STREAM when stream_cast() is called for other uses.
	 *
	 * @return resource Should return the underlying stream resource used by the wrapper, or FALSE.
	 */
	public function stream_cast($castAs);

	/**
	 * All resources that were locked, or allocated, by the wrapper should be released.
	 */
	public function stream_close();

	/**
	 * Tests for end-of-file on a stream.
	 *
	 * @return bool Should return TRUE if the read/write position is at the end of the stream and if no more data is
	 * available to be read, or FALSE otherwise.
	 */
	public function stream_eof();

	/**
	 * This function forces a write of all buffered output to the stream.
	 *
	 * @return bool Should return TRUE if the cached data was successfully stored (or if there was no data to store), or
	 * FALSE if the data could not be stored.
	 */
	public function stream_flush();

	/**
	 * This method is called in response to flock(), when file_put_contents() (when flags  contains LOCK_EX),
	 * stream_set_blocking() and when closing the stream (LOCK_UN).
	 *
	 * @param mode $operation operation is one of the following:
	 * * LOCK_SH to acquire a shared lock (reader).
	 * * LOCK_EX to acquire an exclusive lock (writer).
	 * * LOCK_UN to release a lock (shared or exclusive).
	 * * LOCK_NB if you don't want flock() to block while locking. (not supported on Windows)
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function stream_lock($operation);

	/**
	 * This method is called immediately after the wrapper is initialized (for example by fopen() and
	 * file_get_contents()).
	 *
	 * @param string $path Specifies the URL that was passed to the original function.
	 * @param string $mode The mode used to open the file, as detailed for fopen().
	 * @param int $options Holds additional flags set by the streams API. It can hold one or more of the following values
	 * OR'd together.
	 * @param string $opened_path If the path is opened successfully, and STREAM_USE_PATH is set in options, opened_path
	 * should be set to the full path of the file/resource that was actually opened.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function stream_open($path , $mode , $options , $openedPath);

	/**
	 * This method is called in response to fread() and fgets().
	 *
	 * Note: Remember to update the read/write position of the stream (by the number of bytes that were successfully read)
	 *
	 * @param int $count How many bytes of data from the current position should be returned.
	 *
	 * @return string If there are less than count  bytes available, return as many as are available. If no more data is
	 * available, return either FALSE or an empty string.
	 */
	public function stream_read($count);

	/**
	 * Sets the file position indicator for the stream. The new position, measured in bytes from the beginning of the
	 * stream, is obtained by adding offset to the position specified by whence.
	 *
	 * @param int $offset The offset. To move to a position before the end-of-file, you need to pass a negative value in
	 * offset and set whence to SEEK_END.
	 * @param int $whence = SEEK_SET whence values are:
	 * * SEEK_SET - Set position equal to offset bytes.
	 * * SEEK_CUR - Set position to current location plus offset.
	 * * SEEK_END - Set position to end-of-file plus offset.
	 *
	 * @return bool
	 */
	public function stream_seek($offset , $whence = SEEK_SET);

	/**
	 * This method is called to set options on the stream.
	 *
	 * @param int $option One of:
	 * * STREAM_OPTION_BLOCKING (The method was called in response to stream_set_blocking())
	 * * STREAM_OPTION_READ_TIMEOUT (The method was called in response to stream_set_timeout())
	 * * STREAM_OPTION_WRITE_BUFFER (The method was called in response to stream_set_write_buffer())
	 * @param int $arg1 If option is
	 * * STREAM_OPTION_BLOCKING: requested blocking mode (1 meaning block 0 not blocking).
	 * * STREAM_OPTION_READ_TIMEOUT: the timeout in seconds.
	 * * STREAM_OPTION_WRITE_BUFFER: buffer mode (STREAM_BUFFER_NONE or STREAM_BUFFER_FULL).
	 * @param int $arg2 If option is
	 * * STREAM_OPTION_BLOCKING: This option is not set.
	 * * STREAM_OPTION_READ_TIMEOUT: the timeout in microseconds.
	 * * STREAM_OPTION_WRITE_BUFFER: the requested buffer size.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure. If option is not implemented, FALSE should be returned.
	 */
	public function stream_set_option($option , $arg1 , $arg2);

	/**
	 * Gathers the statistics of the file opened by the file pointer handle.
	 *
	 * @return array See stat().
	 */
	public function stream_stat();

	/**
	 * Returns the position of the file pointer referenced by the stream.
	 *
	 * @return int Should return the current position of the stream.
	 */
	public function stream_tell();

	/**
	 * Writes the contents of data to the file stream.
	 *
	 * Note: Remember to update the current position of the stream by number of bytes that were successfully written.
	 *
	 * @param string $data Should be stored into the underlying stream. If there is not enough room in the underlying
	 * stream, store as much as possible.
	 *
	 * @return int Should return the number of bytes that were successfully stored, or 0 if none could be stored.
	 */
	public function stream_write($data);

	/**
	 * Deletes path. Similar to the Unix C unlink() function. A E_WARNING level error will be generated on failure.
	 *
	 * Note: In order for the appropriate error message to be returned this method should not be defined if the wrapper
	 * does not support removing files.
	 *
	 * @param string $path The file URL which should be deleted.
	 *
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function unlink($path);

	/**
	 * This method is called in response to all stat() related functions.
	 *
	 * @param string $path The file path or URL to stat. Note that in the case of a URL, it must be a :// delimited URL.
	 * Other URL forms are not supported.
	 * @param int $flags Holds additional flags set by the streams API. It can hold one or more of the following values
	 * OR'd together.
	 *
	 * @return array Should return as many elements as stat() does. Unknown or unavailable values should be set to a
	 * rational value (usually 0).
	 */
	public function url_stat($path , $flags);
}