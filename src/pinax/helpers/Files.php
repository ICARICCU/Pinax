<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Files extends PinaxObject
{
    /**
     * @param string $dir
     * @param integer $expireTime
     * @param boolean $onlyContent
     * @return boolean
     */
    public static function deleteDirectory($dir, $expireTime=null, $onlyContent=false)
    {
        if (!file_exists($dir)) return false;

        if (!is_dir($dir) || is_link($dir)) {
            if ($expireTime) {
                $fileCreationTime = filectime($dir);
                if ((time() - $fileCreationTime) < $expireTime) {
                    return true;
                }
            }
            return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..') continue;

            if (!pinax_helpers_Files::deleteDirectory($dir . "/" . $item, $expireTime)) {
                @chmod($dir . "/" . $item, 0777);
                if (!pinax_helpers_Files::deleteDirectory($dir . "/" . $item, $expireTime)) return false;
            }
        }

        return !$onlyContent ? @rmdir($dir) : true;
    }

    /**
     * @param string $src
     * @param string $dst
     * @return void
     */
    public static function copyDirectory($src, $dst)
    {
        if (is_dir($src)) {
            @mkdir($dst, fileperms($src), true);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    self::copyDirectory($src.'/'.$file, $dst.'/'.$file);
                }
            }
        } else if (file_exists($src)) {
            copy($src, $dst);
        }
    }

    /**
     * @param string $folder
     * @param string $pattern
     * @return array
     */
    public static function rsearch($folder, $pattern)
    {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }
}
