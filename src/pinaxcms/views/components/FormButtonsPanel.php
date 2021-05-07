<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_views_components_FormButtonsPanel extends pinax_components_ComponentContainer
{
    private $templateStart = '';
    private $templateEnd = '';

    function process()
    {
        $template = __Config::get('pinaxcms.form.buttonsPanel');
        if ($template) {
            list($templateStart, $templateEnd) = explode('##CONTENT##', $template);
            $this->templateStart = str_replace('##ID##', $this->getid(), $templateStart);
            $this->templateEnd = $templateEnd;
        }
        parent::process();
    }

    function render_html_onStart()
    {
        $this->addOutputCode($this->templateStart);
    }

    function render_html_onEnd()
    {
       $this->addOutputCode($this->templateEnd);
    }
}
