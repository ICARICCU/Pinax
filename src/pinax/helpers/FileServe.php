<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_FileServe
{
	/**
	 * http://stackoverflow.com/questions/2000715/answering-http-if-modified-since-and-http-if-none-match-in-php
	 * http://stackoverflow.com/questions/14661637/allowing-caching-of-image-php-until-source-has-been-changed
	 * http://www.coneural.org/florian/papers/04_byteserving.php
	 * @param string $fileName
	 * @param string $originalFileName
	 * @param integer $expires
	 * @param boolean $forceDownload
	 * @return void
	 */
	static public function serve($fileName, $originalFileName=null, $expires=null, $forceDownload=false)
	{
		if (preg_match('/^(http:|https:)/', $fileName)) {
			header('Content-Length: 0');
			header('Location: ' . $fileName);
			exit;
		}

		$mime = !$forceDownload ? pinax_helpers_FileServe::mimeType($fileName) : 'application/force-download';
		$fileSize = filesize($fileName);
		$gmdate_mod = gmdate('D, d M Y H:i:s', filemtime($fileName) );
		if(! strstr($gmdate_mod, 'GMT')) {
			$gmdate_mod .= ' GMT';
		}

		$disposition = in_array($mime, array('application/pdf', 'image/gif', 'image/png', 'image/jpeg', 'audio/mpeg', 'audio/mp4', 'video/mp4', 'video/mpeg')) ? 'inline' : 'attachment';

		if ($disposition == 'attachment') {
			$mime = 'application/force-download';
		}

		if ($expires) {
			$exp_gmt = gmdate("D, d M Y H:i:s", time() + $expires) . " GMT";
    		header('Cache-Control: max-age='.$expires.', must-revalidate');
        	header('Expires: '.$exp_gmt);
		}

        header('Accept-Ranges: bytes');
	    header('Content-Type: ' . $mime);
	    header('Content-Length: ' . $fileSize);
		header('Last-Modified: ' . $gmdate_mod);
		header('Content-Transfer-Encoding: binary');
    	if ($originalFileName) {
		    header('Content-Disposition: '.$disposition.'; filename="' . $originalFileName . '"');
	    } else {
		    header('Content-Disposition: '.$disposition);
	    }

	    @ob_end_clean();
	    @ob_end_flush();
	    readfile($fileName);
	    exit;
	}

	/**
	 * @param string $fileName
	 * @return string
	 */
	static public function mimeType($fileName)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, $fileName);
	}
}
