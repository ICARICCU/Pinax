<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_utils_DuplicationMenuStorageDelegate extends PinaxObject implements pinaxcms_contents_utils_DuplicationMenuStorageDelegateInterface
{
    public function contentDuplicationFix($contentVO)
    {
        return $contentVO;
    }
}
