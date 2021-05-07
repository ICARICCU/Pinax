<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_TabGroup extends pinax_components_StateSwitch
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('addWrapDiv',	false, false, 		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClass',		false, '', 			COMPONENT_TYPE_STRING);
		$this->defineAttribute('forceLink',		false, true, 		COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();

		$this->setAttribute('useIdPrefix', true);
	}

	function render_html()
	{
		if ($this->getAttribute('addWrapDiv'))
		{
			$output  = '<div id="'.$this->getId().'" class="clearfix"><ul '.(!is_null($this->getAttribute('cssClass')) ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>';
		}
		else
		{
			$output = '<ul id="'.$this->getId().'"'.(!is_null($this->getAttribute('cssClass')) ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>';
		}
		for ($i=0; $i<count($this->childComponents); $i++)
		{
			$label = $this->childComponents[$i]->getAttribute('label');
			$onlyLabel = $this->childComponents[$i]->getAttribute('onlyLabel');
			$states = $this->childComponents[$i]->getStatesArray();
			$draw = $this->childComponents[$i]->getAttribute('draw');
			$cssClass = in_array($this->getState(), $states) ? ' class="active"' : '';
			$id = $this->childComponents[$i]->getId();
			if ( !$draw ) {
				continue;
			}

			if ($onlyLabel)
			{
				$output .= '<li class="tab-label">'.$label.'</li>';
			}
			else if (!empty($cssClass) && !$this->getAttribute('forceLink'))
			{
				$output .= '<li'.$cssClass.'>'.$label.'</li>';
			}
			else
			{
				$url = $this->childComponents[$i]->getAttribute('url');
				if (is_null($url))
				{
					$url = $this->childComponents[$i]->getAttribute('routeUrl');
					if (!is_null($url))
					{
						$url = __Link::makeUrl( $url );
					}
				}
				if (is_null($url))
				{
					$url = $this->changeStateUrl($states[0], true );
				}
				$output .= '<li'.$cssClass.'><a id="' . $id . '" href="'.$url.'">'.$label.'</a></li>';
			}
		}

		$output  .= '</ul>';
		if ($this->getAttribute('addWrapDiv'))
		{
			$output  .= '</div>';
		}
		$this->addOutputCode($output);
	}
}
