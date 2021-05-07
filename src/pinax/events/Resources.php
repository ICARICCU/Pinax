<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_events_Resources
{
    const ADD_RESOURCE = 'onAddResource';

    public $type;
    public $src;
    public $region;
    public $minify;
    public $media;
    public $replaceValues;

    public function __construct($type, $src, $region, $minify=false, $media=null, $replaceValues=true)
    {
        $this->type = $type;
        $this->src = $src;
        $this->region = $region;
        $this->minify = $minify;
        $this->media = $media;
        $this->replaceValues = $replaceValues;
    }

    public static function addResource($type, $src, $region, $minify=false, $media=null, $replaceValues=true)
    {
        return [
                    'type' => self::ADD_RESOURCE,
                    'data' => new self($type, $src, $region, $minify, $media, $replaceValues)
                ];
    }
}
