<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_LanguageNavigation extends pinax_components_Component
{

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',	false, 	'languages',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('separator',	false, 	'none',			COMPONENT_TYPE_STRING); // none, start, end

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		if (!__Config::get('MULTILANGUAGE_ENABLED')) {
			return false;
		}

		$iterator = pinax_ObjectFactory::createModelIterator('pinaxcms.core.models.Language',
				'all', array('order' => 'language_order'));

        $language = __ObjectFactory::createModel('pinaxcms.core.models.Language');
		if ($language->fieldExists('language_isVisible')) {
			$iterator->where('language_isVisible', 1);
		}

		if ($iterator->count() > 1)
		{
			$output = '<ul class="'.$this->getAttribute('cssClass').'" id="'.$this->getId().'">';
			if ($this->getAttribute('separator')=='start')
			{
				$output .= '<li class="separator">|</li>';
			}

			foreach($iterator as $ar)
			{
				$url = __Link::addParams(array('language' => $ar->language_code));
				if ($ar->language_id==$this->_application->getLanguageId())
				{
					$output .= '<li class="'.$ar->language_code.'">'.pinax_helpers_Link::makeSimpleLink( pinax_encodeOutput( $ar->language_name ), $url, '', 'active').'</li>';
				}
				else
				{
					$output .= '<li class="'.$ar->language_code.'">'.pinax_helpers_Link::makeSimpleLink( pinax_encodeOutput( $ar->language_name ), $url).'</li>';
				}
			}

			if ($this->getAttribute('separator')=='end')
			{
				$output .= '<li>|</li>';
			}
			$output .= '</ul>';
			$this->addOutputCode($output);
		}
	}
}
