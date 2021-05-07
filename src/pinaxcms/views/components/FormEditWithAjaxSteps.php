<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_FormEditWithAjaxSteps extends pinaxcms_views_components_FormEdit
{
	public function render_html_onStart()
	{
        // TODO localizzare il javascript
        $this->addOutputCode(pinax_helpers_JS::linkCoreJSfile('jquery-simplemodal/jquery.simplemodal.1.4.1.min.js'));
        $this->addOutputCode(pinax_helpers_JS::linkCoreJSfile('progressBar/progressBar.js'));
        $this->addOutputCode(pinax_helpers_CSS::linkCoreCSSfile2('progressBar/progressBar.css'), 'head');
        $this->addOutputCode(pinax_helpers_JS::linkCoreJSfile('formWithAjaxSteps.js?v='.PNX_CORE_VERSION));

        parent::render_html_onStart();

        $ajaxUrl = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

        $output = <<<EOD
<div id="progress_bar" class="js-pinaxcms-FormEditWithAjaxSteps ui-progress-bar ui-container" data-ajaxurl="$ajaxUrl">
  <div class="ui-progress" style="width: 0%;">
    <span class="ui-label" style="display:none;"><b class="value">0%</b></span>
  </div>
</div>
EOD;
        $this->addOutputCode($output);
	}
}
