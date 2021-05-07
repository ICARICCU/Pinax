<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_EditLanguage extends pinax_components_Component
{
	function init()
	{
		$this->defineAttribute('label', false, 	'{i18n:PNX_EDIT_LANGUAGE}',	COMPONENT_TYPE_STRING);
		parent::init();
		$this->doLater($this, 'checkSwicth');
	}

	function checkSwicth()
	{
		if (!is_null(pinax_Request::get('switchLanguage')))
		{
			$language = pinax_Request::get('switchLanguage');
			$this->_application->switchEditingLanguage($language);
		}
	}

	function process()
	{
		$this->_content = array();
		$this->_content['label'] = $this->getAttribute('label');
		$this->_content['cssClass'] = $this->getAttribute('cssClass');
		$this->_content['current'] = '';
		$this->_content['records'] = array();

		$iterator = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language');
		$iterator->orderBy('language_order');

        if ($iterator->count()) {
        	$editLanguageId = $this->_application->getEditingLanguageId();
			foreach ($iterator as $ar) {
				if ($ar->language_id == $editLanguageId) {
				    $this->_content['current'] = $ar->language_name;
				    continue;
				}

				$url = pinax_helpers_Link::addParams(array('switchLanguage' => $ar->language_id));
				$this->_content['records'][] = pinax_helpers_Link::makeSimpleLink( pinax_encodeOutput( $ar->language_name ), $url, $ar->language_name);
			}
		}
	}

}

class pinaxcms_views_components_EditLanguage_render extends pinax_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<div tal:attributes="id id; class Component/cssClass" tal:condition="Component/records">
	<span tal:omit-tag="" tal:content="Component/label" />
    <div class="btn-group">
    	<a tal:attributes="data-target php: '#' . id . 'menu'" data-toggle="dropdown" class="btn dropdown-toggle action-link"><i class="icon-chevron-down"></i> <span tal:omit-tag="" tal:content="Component/current" /></a>
        <div tal:attributes="id php: id . 'menu'">
            <ul class="dropdown-menu right">
            	<li tal:repeat="item Component/records" tal:content="structure item" />
            </ul>
        </div>
    </div>
</div>
EOD;
		return $skin;
	}
}
