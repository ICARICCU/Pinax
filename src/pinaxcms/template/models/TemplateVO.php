<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_models_TemplateVO extends PinaxObject
{
    public $name;
    public $path;
    public $preview;

    function __construct($name, $path, $preview) {
        $this->name = str_replace('-', ' ', $name);
        $this->path = $path;
        $this->preview = $preview;
    }
}
