<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_String
{
	/**
	 * @param integer $size
	 * @return string
	 */
	public static function formatFileSize($size)
	{
		if($size<1024)
		{
			return $size.' bytes';
		}
		else if($size<1048576)
		{
			return round(($size/1024), 1 ).' kb';
		}
		else
		{
			return round(($size/1048576), 1).' Mb';
		}
	}

	// code based on http://www.php.net/manual/en/function.substr.php#70417
	// by feedback at realitymedias dot com
	/**
	 * @param string $str
	 * @param integer $maxlen
	 * @param string $elli
	 * @param boolean $stripTags
	 * @param integer $maxoverflow
	 * @return string
	 */
	public static function strtrim($str, $maxlen=100, $elli=NULL, $stripTags=false, $maxoverflow=15)
	{
		if ($stripTags)
		{
			$str = str_replace('</p>', '</p> ', $str);
			$str = str_replace('<br', ' <br', $str);
			return strip_tags($str);
		}
		if (strlen($str) > $maxlen)
		{
			$output = NULL;
			$body = explode(" ", $str);
			$body_count = count($body);

			$i=0;

			do {
				$output .= $body[$i]." ";
				$thisLen = strlen($output);
				$cycle = ($thisLen < $maxlen && $i < $body_count-1 && ($thisLen+strlen($body[$i+1])) < $maxlen+$maxoverflow?true:false);
				$i++;
			} while ($cycle);
			return $output.$elli;
		}
		else return $str;
	}
}
