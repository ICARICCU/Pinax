<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_ResolvedVO
{
    public $refObj;
    public $url;
    public $link;
    public $title;

    public function __construct($refObj, $url, $link, $title)
    {
        $this->refObj = $refObj;
        $this->url = $url;
        $this->link = $link;
        $this->title = $title;
    }

    static public function create($refObj, $url, $link, $title)
    {
        return new self($refObj, $url, $link, $title);
    }

    static public function create404()
    {
        return new self(null, '', '', '');
    }
}
