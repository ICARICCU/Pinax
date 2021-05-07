<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_views_components_PagePicker extends pinax_components_Input
{
	function init()
	{
		// define the custom attributes
        $this->defineAttribute('ajaxController',    false,  'pinaxcms.contents.controllers.autocomplete.ajax.PagePicker',   COMPONENT_TYPE_STRING);
        $this->defineAttribute('menuType',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('pageType',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('protocol',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('makeLink',  false,  false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('multiple',	false, 	false,	COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('queryString',  false,  __Config::get('pinaxcms.pagePicker.queryStringEnabled'),  COMPONENT_TYPE_BOOLEAN);
		parent::init();

	}


    function process()
    {
        if (!$this->_application->isAdmin()) {
            $this->_content = $this->_parent->loadContent($this->getId());

            $speakingUrlManager = $this->_application->retrieveProxy('pinaxcms.speakingUrl.Manager');
            $this->_content = $this->getAttribute('makeLink') ?
                                    $speakingUrlManager->makeLink($this->_content) :
                                    $speakingUrlManager->makeUrl($this->_content);
        } else {
            $this->setAttribute('data', ';type=CmsPagePicker;controllername='.$this->getAttribute('ajaxController').
                                        ';menutype='.$this->getAttribute('menuType').
                                        ';pagetype='.$this->getAttribute('pageType').
                                        ';multiple='.($this->getAttribute('multiple') ? 'true':'false').
                                        ';querystring='.($this->getAttribute('queryString') ? 'true':'false').
                                        ';protocol='.$this->getAttribute('protocol')
                                        , true);
        }
    }
}
