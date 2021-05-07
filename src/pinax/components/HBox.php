<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_HBox extends pinax_components_ComponentContainer
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
		$this->defineAttribute('width',	false, 	'40%',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function render_html_onEnd()
	{

		$output = '<div style="clear: both;"></div>';
		$this->addOutputCode($output);
	}

	/*function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if (!empty($output) && $output!='') $output = '<div style="position: relative; float: left; margin-right: 5px; ">'.$output.'</div>';
		$this->addParentOutputCode($output, $editableRegion, $atEnd);
	}*/

	function renderChilds($outputMode=NULL)
	{
		if ($this->checkBreakCycle())
		{
			$this->breakCycle(false);
			return;
		}

		if ($this->canHaveChilds)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				if ($this->childComponents[$i]->getAttribute('visible'))
				{
					$clear = $i==0 ? 'clear: both; ' : '';
					$this->addOutputCode('<div style="'.$clear.'position: relative; width: '.$this->getAttribute('width').'; float: left; margin-right: 5px;">');
					$this->childComponents[$i]->render($outputMode);
					$this->addOutputCode('</div>');
				}
				if ($this->checkBreakCycle())
				{
					$this->breakCycle(false);
					break;
				}
			}
		}
	}
}
