<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_State extends pinax_components_ComponentContainer
{
	var $_state;
	private $addChildMethodName;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('name', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('forceChildCreation',	false, 	NULL,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	function deferredChildCreation($force=false)
	{
		// aggiunge i figli
		$methodName = $this->addChildMethodName;
		$methodName ( $this->_application, $this );
		$this->initChilds();
		$this->execDoLater();
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->_state = explode(',', strtolower($this->getAttribute('name')));

		if (in_array($this->_parent->getState(), $this->_state))
		{
			if ( !count( $this->childComponents ) )
			{
				$this->deferredChildCreation();
			}

			$this->processChilds();
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode = NULL, $skipChilds = false)
	{
		if (in_array($this->_parent->getState(), $this->_state) || $outputMode=='jsediting')
		{
			$this->renderChilds($outputMode);
		}
	}

	function getState()
	{
		return $this->_parent->getState();
	}

	function getStatesArray()
	{
		return $this->_state;
	}

	function getDefaultState()
	{
		return $this->_state[0];
	}

	public function setAddChildMethodName($methodName)
	{
		$this->addChildMethodName = $methodName;
	}


	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		$compiler->_classSource .= '$n'.$counter.' = &pinax_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', '.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.PNX_COMPILER_NEWLINE;

		$forceChildCreation = $node->hasAttribute( 'forceChildCreation' ) && strtolower( $node->getAttribute( 'forceChildCreation' ) ) == 'true' ?
									'true' :
									$node->parentNode->hasAttribute( 'forceChildCreation' ) && strtolower($node->parentNode->getAttribute( 'forceChildCreation' ) ) == 'true' ? 'true' : 'false';
		$compiler->_classSource .= '$forceChildCreation = '.$forceChildCreation.PNX_COMPILER_NEWLINE;
		if ($parent!='NULL')
		{
			$compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.PNX_COMPILER_NEWLINE;
		}

		if (count($node->attributes))
		{
			// compila  gli attributi
			$compiler->_classSource .= '$attributes = array(';
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					$compiler->_classSource .= '\''.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}
			$compiler->_classSource .= ')'.PNX_COMPILER_NEWLINE;
			$compiler->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.PNX_COMPILER_NEWLINE;
		}

		$methodName = 'addChild_'.md5($componentId.microtime(true));
		$compiler->_classSource .= '$n'.$counter.'->setAddChildMethodName( \''.$methodName.'\' )'.PNX_COMPILER_NEWLINE;
		$compiler->_classSource .= 'if ($skipImport || $forceChildCreation) {'.$methodName.'($application, $n'.$counter.', $skipImport, $idPrefix, $mode);}'.PNX_COMPILER_NEWLINE;

		$previusClassSource = $compiler->_classSource;
		$compiler->_classSource = '';
		$compiler->_classSource .= '// STATE function '.PNX_COMPILER_NEWLINE2;
		$compiler->_classSource .= 'function '.$methodName.'( &$application, &$n'.$counter.', $skipImport=false, $idPrefix=\'\', $mode=\'\') {'.PNX_COMPILER_NEWLINE2;

		$oldcounter = $counter;
		foreach( $node->childNodes as $nc )
		{
			$counter++;
			$compiler->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
		}
		$compiler->_classSource .= '} '.PNX_COMPILER_NEWLINE;
		$compiler->_classSource .= '// end STATE function '.PNX_COMPILER_NEWLINE2;

		$compiler->_customClassSource .= $compiler->_classSource;
		$compiler->_classSource = $previusClassSource;
	}
}
