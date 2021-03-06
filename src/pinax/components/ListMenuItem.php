<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_ListMenuItem extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('url',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',			false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('selected',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('acl',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',		false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTag',		false, 	'',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function getItem()
	{
		$acl = $this->getAttribute( 'acl' );

		if ( !empty( $acl ) )
		{
			list( $service, $action ) = explode( ',', $acl );
			if ( !$this->_user->acl( $service, $action ) )
			{
			return false;
			}
		}
		$value = strtolower( $this->getAttribute( 'value' ) );
		$label = $this->getAttribute( 'label' );
		$url = $this->getAttribute( 'url' );
		$routeUrl = $this->getAttribute( 'routeUrl' );
		$cssClass = $this->getAttribute( 'cssClass' );

		if ( $routeUrl )
		{
			$title = $label;
			if ( $cssClass )
			{
				$label = '<i class="'.$cssClass.'"></i>'.$label;
			}
			$label = $this->addWrap($label);
			$url = pinax_helpers_Link::makeLink( $routeUrl, array( 'pageId' => $value, 'title' => $title, 'label' => $label ), array(), '', false );
			return array( 'url' => $url, 'selected' => $this->checkSelected($value, $this->_application->getPageId()) );
		}
		else if ( $url )
		{
			return array( 'url' => pinax_helpers_Link::makeSimpleLink($this->addWrap($label), $url, $label, $cssClass ), 'selected' => $this->checkSelected($value, __Request::get( '__url__' )) );
		}
		else if ( $value )
		{
            $url = pinax_helpers_Link::makeLink( $value, array( 'label' => $this->addWrap($label),'title' => $label, 'cssClass' => $cssClass ), array(), '', false );
			return array( 'url' => $url, 'selected' => $this->checkSelected($value, __Request::get( '__url__' ))	 );
		}
		else
		{
			return array( 'url' => '<span class="'.$cssClass.'">'.$label.'</span>', 'selected' => false );
		}
	}

	private function addWrap($label) {
		$wrapTag = $this->getAttribute('wrapTag');
		if ($wrapTag) {
			$label = '<'.$wrapTag.'>'.$label.'</'.$wrapTag.'>';
		}
		return $label;
	}

	private function checkSelected($value, $valueToCheck)
	{
		$condition = $this->getAttribute( 'selected' );
		if ( !$condition ) $condition = $value;
		$condition = explode( ',', $condition );

		if ( count( $condition ) > 1 ) {
			$selected = true;
			for ($i=0; $i < count( $condition ); $i++) {
				if ( __Request::get( $condition[ $i ] ) != $condition[ $i + 1 ] ) {
					$selected = false;
					break;
				}
				$i++;
			}

			return $selected;
		} else {
			return $condition == $valueToCheck;
		}
	}
}
