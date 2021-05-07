<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Email extends pinax_components_Text
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('makeLink', false, false, COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		$output = $this->getContent();
		$this->addOutputCode($output);
	}

	function getContent()
	{
		$tagContent = trim($this->getText());
		if ($this->getAttribute('makeLink') && !empty($tagContent))
		{
			return pinax_helpers_Link::makeEmailLink($tagContent);
		}
		else
		{
			return $tagContent;
		}
	}
}
