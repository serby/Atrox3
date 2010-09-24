<?php
/**
 * @package Core
 * @subpackage Graphic
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 536 $ - $Date: 2008-03-04 11:47:00 +0100 (Thu, 03 Apr 2008) $
 */

/**
 * @author Robert Arnold {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision: 536 $ - $Date: 2008-03-04 11:47:00 +0100 (Thu, 03 Apr 2008) $
 * @package Core
 * @subpackage Graphic
 */
class VideoControl {

	function createThumbnail($inputVideoFileName, $outputImageFileName, $offsetSeconds = "2",
		$outputImageWidth = 0, $outputImageHeight = 0) {

		$commandOptions = array(
			"-i '$inputVideoFileName'",
			"-vcodec mjpeg",
			"-ss $offsetSeconds",
			"-vframes 1",
			"-f rawvideo"
		);

		if (($outputImageWidth > 0) && ($outputImageHeight > 0)) {
			$outputImageWidth = floor(($outputImageWidth) / 2) * 2;
			$outputImageHeight = floor(($outputImageHeight) / 2) * 2;
			$commandOptions[] = "-s " . $outputImageWidth . "x" . $outputImageHeight;
		}

		$command = "ffmpeg " . implode(" ", $commandOptions) . " '$outputImageFileName'";
//		echo $command; exit;
		shell_exec($command);
	}

	function saveThumbnail($video, $offsetSeconds, $outputImageWidth, $outputImageHeight) {

		// Make thumbnail
		$application = CoreFactory::getApplication();
		$htmlControl = CoreFactory::getHtmlControl();

		$imageName = $video->getRelation("BinaryId")->get("Filename");
		$siteFriendlyImageName = htmlentities($imageName);
		$inputVideoFileName = $application->registry->get("Binary/Path") . "/" . $video->getRelation("BinaryId")->get("HashValue") . "/" . $imageName;

//		$inputVideoFileName = $application->registry->get("Path") . $htmlControl->getBinaryLocation($video->getRelation("BinaryId"));
		$filename = md5(uniqid(rand(), true)) . ".jpg";
		$outputImageFileName = "/tmp/" . $filename;

		$this->createThumbnail($inputVideoFileName, $outputImageFileName, $offsetSeconds, $outputImageWidth, $outputImageHeight);

		// Build Array
		$thumbnailInfo = array();
		$thumbnailInfo["Filename"] = $filename;
		$thumbnailInfo["Type"] = mime_content_type($outputImageFileName);
		$thumbnailInfo["Size"] = filesize($outputImageFileName);
		$thumbnailInfo["Remove"] = false;
		$thumbnailInfo["TempName"] = $outputImageFileName;

		$video->setBinary("ThumbnailId", null, $thumbnailInfo);
		$video->save();
	}
}