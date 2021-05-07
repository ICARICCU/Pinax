<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_SearchFilters extends pinax_components_SearchFilters
{
	function process()
	{
		$visible = $this->_parent->loadContent($this->getId())===1;
		if ($visible) {
			parent::process();
		} else {
			$this->setAttribute('visible', false);
		}
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['data'] = '';
		$attributes['noChild'] = 'true';
		$attributes['xmlns:pnx'] = "pinax.components.*";

		return pinax_helpers_Html::renderTag('pnx:Checkbox', $attributes);
	}
}
