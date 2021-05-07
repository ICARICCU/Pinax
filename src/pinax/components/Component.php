<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('COMPONENT_TYPE_STRING', 1);
define('COMPONENT_TYPE_BOOLEAN', 2);
define('COMPONENT_TYPE_INTEGER', 3);
define('COMPONENT_TYPE_OBJECT', 4);
define('COMPONENT_TYPE_ENUM', 5);

define('COMPONENT_STATE_NULL', 0);
define('COMPONENT_STATE_INIT', 1);
define('COMPONENT_STATE_PROCESS', 2);
define('COMPONENT_STATE_RENDER', 3);
define('COMPONENT_STATE_BLOCKED', 4);

/**
 * Class pinax_components_Component
 */
class pinax_components_Component extends PinaxObject
{
    /** @var pinax_mvc_core_Application $_application */
	public $_application;
    /** @var pinax_components_Component */
    public $_parent;
    public $_tagname;
    public $_content				= NULL;
    public $_attributesDefinition;
    public $_attributes;
    public $canHaveChilds			= false;
    /** @var pinax_components_Component[] $childComponents */
    public $childComponents;
    public $_oldID;
    public $_originalId;
    public $state;
    /** @var pinax_application_User $_user  */
    public	$_user					= NULL;
    public	$_outputMode			= NULL;
	protected $controller = NULL;
	public $canCallController = true;

    /**
     * @param        $application
     * @param        $parent
     * @param string $tagName
     * @param string $id
     * @param string $originalId
     */
    function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		$IDmap = &self::_getIDmapArray();

		$this->_application 	= &$application;
		$this->_user			= &pinax_ObjectValues::get('org.pinax', 'user');
		$this->_parent 			= &$parent;
		$this->_tagname 		= $tagName;
		$this->childComponents = array();
		$this->_originalId		= $originalId;
		$this->state			= COMPONENT_STATE_NULL;

		$id = ltrim($id, '/');

		// definisce gli attributi di default
		$this->_attributes 				= array();
		$this->_attributesDefinition	= array();
		$this->defineAttribute('id',				false, 	!empty($id) ? $id :'c'.md5(uniqid(rand(), true)), 		COMPONENT_TYPE_STRING);

		$this->_initAttributes();
		$this->_initID();
		$this->breakCycle(false);
	}

	/**
	 * @return void
	 */
	public function reset()
	{
		$this->state = COMPONENT_STATE_INIT;
		$this->_content = NULL;
	}

	/**
	 * @return void
	 */
	public function resetChilds()
	{
		if ($this->canHaveChilds)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$this->childComponents[$i]->reset();
				$this->childComponents[$i]->resetChilds();
			}
		}
	}

	/**
	 * @return void
	 */
	public function init()
	{
		$this->state = COMPONENT_STATE_INIT;

		$this->defineAttribute('enabled', 				false, 	true,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('editableRegion', 		false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('editableRegionAtEnd', 	false, 	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('skin',					false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('acl',					false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('visible', 				false, 	true,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('og', 					false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('controllerName',		false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('actionName',			false, 'action',	COMPONENT_TYPE_STRING);
        $this->defineAttribute('data',		        	false, 	'',	       	COMPONENT_TYPE_STRING);

		$this->_initAttributes();
		$this->initChilds();
	}

	/**
	 * @return void
	 */
	protected function initChilds()
	{
		if ($this->canHaveChilds)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$this->childComponents[$i]->init();
			}
		}
	}

    /**
     * @param      $name
     * @param bool $required
     * @param null $defaultValue
     * @param int  $type
     */
	public function defineAttribute($name, $required=false, $defaultValue=NULL, $type=COMPONENT_TYPE_STRING)
	{
		if (!isset($this->_attributesDefinition[$name])) {
			$this->_attributesDefinition[$name] = array('defaultValue' => $defaultValue, 'required' => $required, 'type' => $type);
		}
	}


    /**
     * @param $name
     */
	public function removeAttribute($name)
	{
		unset($this->_attributesDefinition[$name]);
		unset($this->_attributes);
	}

	/**
	 * @return void
	 */
	public function dumpAttributes()
	{
		var_dump($this->_attributes);
	}

    /**
     * @param $idPrefix
     */
	public function remapAttributes($idPrefix)
	{
		foreach ($this->_attributes as $k=>$v)
		{
			if (preg_match("/\{[^\:]*\}/i", $v))
			{
				$id = str_replace(array('{', '}'), '', $v);
				if (is_object($this->getComponentById($id))) continue;
				$v = str_replace('{','{'.$idPrefix, $v);
				$this->_attributes[$k] = $v;
			}
			else if (isset($this->_attributesDefinition[$k]) && $this->_attributesDefinition[$k]['type']==COMPONENT_TYPE_OBJECT)
			{
				$v = str_replace('{','', $v);
				$v = str_replace('}','', $v);

				if (is_object($this->getComponentById($v))) continue;
				$this->_attributes[$k] = $idPrefix.$v;
			}
		}

		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$this->childComponents[$i]->remapAttributes($idPrefix);
		}
	}

    /**
     * @param $idPrefix
     */
	public function remapChildsIDs($idPrefix)
	{
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$this->childComponents[$i]->setId($idPrefix.$this->childComponents[$i]->getId());
			$this->childComponents[$i]->remapChildsIDs($idPrefix);
		}
	}

    /**
     * @param $idPrefix
     */
	public function remapChildsOriginalIDs($idPrefix)
	{
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$this->childComponents[$i]->setId($idPrefix.$this->childComponents[$i]->getOriginalId());
			$this->childComponents[$i]->remapChildsOriginalIDs($idPrefix.$this->childComponents[$i]->getOriginalId().'-');
		}
	}

    /**
     * @param $name
     *
     * @return mixed|pinax_components_Component|string
     * @throws Exception
     */
	public function &getAttribute($name)
	{
		$value = isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
		if (is_string($value))
		{
			if (@$this->_attributesDefinition[$name]['type']==COMPONENT_TYPE_OBJECT || preg_match("/^\{[^\:]*\}$/i", $value))
			{
				$value = str_replace('{','',$value);
				$value = str_replace('}','',$value);
				if (strpos($value,'.')===false)
				{
					$value = &$this->getComponentById($value);
				}
				else
				{
					// controlla se c'è da chiamare un metodo
					$phpArray = explode('.', $value);
					if (count($phpArray))
					{
						$phpcode = '$A = &$this->getComponentById(\''.$phpArray[0].'\');';
						for ($i=1;$i<count($phpArray);$i++)
						{
							$phpcode .= '$'.chr(65+$i).'=$'.chr(64+$i).'->'.$phpArray[$i].';';
						}
						$phpcode = $phpcode.'; return $'.chr(64+$i).';';
						$value= eval($phpcode);
					}
				}
			}
			else
			{
				if (preg_match("/\{php\:.*\}/i", $value))
				{
					// Codice php per ottenere il valore dell'attributo
					$phpcode = pinax_helpers_PhpScript::parse($value);
					$value= eval($phpcode);
					//return $value;
				}
				else if (preg_match("/\{i18n\:.*\}/i", $value))
				{
					$code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $value);
					$value = pinax_locale_Locale::getPlain($code);
				}
				else if (preg_match("/\{config\:.*\}/i", $value))
				{
					preg_match_all( "/\{config:([^\{]*)\}/U", $value, $resmatch );
			            foreach( $resmatch[1] as $varname) {
							$newValue = __Config::get($varname);
							$value = str_replace('{config:'.$varname.'}', $newValue, $value);
			            }
				}
			}
		}

		if ($value && !is_object($value) && !is_array($value))
		{
			$value = $this->_validateAttributeValue($name, $value);
		}
		return $value;
	}

    /**
     * @param $name
     *
     * @return mixed|string
     */
	public function getAttributeString($name)
	{
		$value = strval($this->_attributes[$name]);

		if (preg_match("/\{i18n\:.*\}/i", $value))
		{
			$code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $value);
			$value = pinax_locale_Locale::get($code);
		}
		else
		{
			$value = pinax_encodeOutput($value);
		}

		return $value;
	}

    /**
     * @param      $name
     * @param      $value
     * @param bool $merge
     */
	public function setAttribute($name, $value, $merge = false )
	{
		if ( !$merge )
		{
			$this->_attributes[$name] = $this->_validateAttributeValue($name, $value);
		}
		else
		{
			$this->_attributes[$name] .= $value;
		}

		// se l'attributo settato è l'ID
		// aggiorna la mappa degli ID.
		if ($name=='id')
		{
			$this->_initID();
			$IDmap = &self::_getIDmapArray();
		}

		if ($name=='enabled' && $value === true && $this->state==COMPONENT_STATE_INIT && $this->_parent->state!=COMPONENT_STATE_INIT)
		{
			$this->process();
			$this->state = COMPONENT_STATE_PROCESS;
		}
	}

    /**
     * @param array $attributes
     */
	public function setAttributes( $attributes=array() )
	{
		if ( is_array( $attributes ) )
		{
			foreach ( $attributes as $name=>$value )
			{
				$this->_attributes[$name] = $this->_validateAttributeValue($name, $value);

				// se l'attributo settato è l'ID
				// aggiorna la mappa degli ID.
				if ($name=='id')
				{
					$this->_initID();
					$IDmap = &pinax_components_Component::_getIDmapArray();
				}

				if ($name=='enabled' && $this->state==COMPONENT_STATE_INIT && $this->_parent->state!=COMPONENT_STATE_INIT)
				{
					$this->process();
					$this->state = COMPONENT_STATE_PROCESS;
				}
			}
		}

	}

    /**
     * @param $name
     *
     * @return bool
     */
	public function issetAttribute($name)
	{
		return isset($this->_attributes[$name]);
	}

	/**
	 * @return void
	 */
	private function _initID()
	{
		$IDmap = &self::_getIDmapArray();
		if (!is_null($this->_oldID)) unset($IDmap[$this->_oldID]);
		$ID = $this->getId();
		$IDmap[$ID] = &$this;
		$this->_oldID = $ID;
	}

	/**
	 * @return void
	 */
	private function _initAttributes()
	{
		// legge le definizioni degli attributi
		// inserisce quelli di default e controlla se ci sono attributi richiesti e non inseriti
		foreach ($this->_attributesDefinition as $key=>$value)
		{
			if (!array_key_exists($key, $this->_attributes))
			{
				// l'attributo non esiste
				if ($value['required'])
				{
					// TODO visualizzare l'errore
					// attributo non settato
					//$this->RaiseError(___ERRORS_MESSAGE_ABSTRACT_METHOD_UNDEFINED___,__FUNCTION__);
					echo "errore required: ".get_class($this)." ".$this->_tagname." ".$key."<br>";
				}
				else
				{
					$this->_attributes[$key] = $value['defaultValue'];
				}
			}
		}
	}

    /**
     * @param $name
     * @param $value
     *
     * @return mixed
     */
	private function _validateAttributeValue($name, $value)
	{
		if (!array_key_exists($name, $this->_attributesDefinition)) {
			return $value;
		}

		$attributeDefinition = $this->_attributesDefinition[$name];
		$typeToCheck = [COMPONENT_TYPE_BOOLEAN, COMPONENT_TYPE_INTEGER, COMPONENT_TYPE_ENUM];

		if (in_array($attributeDefinition['type'], $typeToCheck) && !is_object($value) && !preg_match("/\{(.*)\}/i", $value)) {
			// l'attributo esiste esegue il casting del valore
			switch ($attributeDefinition['type'])
			{
				case COMPONENT_TYPE_BOOLEAN:
					if (is_string($value)) $value = $value=="true" || $value=="1" ? true : false;
					break;
				case COMPONENT_TYPE_INTEGER:
					$value = intval($value);
					break;
				case COMPONENT_TYPE_ENUM:
					$value = explode(',', $value);
					break;
			}
		}

		return $value;
	}

    /**
     * @param $component
     */
	public function addChild(&$component)
	{
		$this->childComponents[] = &$component;
	}

    /**
     * @param $value
     */
	public function setContent($value)
	{
		$this->_content = $value;
	}

	/**
	 * @param boolean $force
	 * @return void
	 */
	public function deferredChildCreation($force=false)
	{
		// aggiunge i figli
		if ($this->canHaveChilds)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$this->childComponents[$i]->deferredChildCreation($force);
			}
		}
	}

	/**
	 * @return void
	 */
	public function process()
	{
		$this->processChilds();
	}

	/**
	 * @param string $acl
	 * @return boolean
	 */
	protected function evalueteAcl($acl)
	{
		$aclResult = true;
		if ($acl) {
			$aclBooleanAnd = true;
			$aclPart = explode(' ', $acl);
			foreach ($aclPart as $aclItem) {
				if ($aclItem=='and') {
					$aclBooleanAnd = true;
				} else if ($aclItem=='or') {
					$aclBooleanAnd = false;
				} else if ($aclItem) {
					list($service, $action) = explode(',', $aclItem);
					$r = $this->_user->acl($service, $action);
					$aclResult = $aclBooleanAnd ? $aclResult && $r : $aclResult || $r;
				}
			}
		}

		return $aclResult;
	}

	/**
	 * @return void
	 */
	protected function processChilds()
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
				$acl = $this->childComponents[$i]->getAttribute('acl');
				if ($acl && !$this->evalueteAcl($acl)) {
					$this->childComponents[$i]->setAttribute( 'enabled', false );
					continue;
				}

				if ($this->childComponents[$i]->getAttribute('enabled') && $this->childComponents[$i]->state==COMPONENT_STATE_INIT)
				{
					if ($this->childComponents[$i]->canCallController) $this->childComponents[$i]->callController();
					$this->childComponents[$i]->process();
					$this->childComponents[$i]->setOgValue();
					$this->state = COMPONENT_STATE_PROCESS;

					if ($this->checkBreakCycle())
					{
						$this->state = COMPONENT_STATE_BLOCKED;
						$this->breakCycle(false);
						break;
					}

					if ($this->childComponents[$i]->canCallController) $this->childComponents[$i]->callController(true);
				}
			}
		}

		if ($this->canCallController) {
			$this->callController(true);
		}
		$this->canCallController = false;
	}

	/**
	 * @param string $outputMode
	 * @param bool|false $skipChilds
	 * @return string|void
	 * @throws Exception
	 */
	public function render($outputMode=NULL, $skipChilds=false)
	{
		if (!$this->getAttribute('visible')) return;
		if (is_null($outputMode)) $outputMode = $this->_application->getOutputMode();
		$this->_outputMode = $outputMode;

		// applica i filtri di pre rendering
		$this->_content = $this->getText();
		$this->applyOutputFilters('pre', $this->_content);

		// cerca se esiste una classe
		// che si chiama come il componente  ma con suffisso render
		$renderClassName = $this->getClassName().'_render';
		if (class_exists($renderClassName) && !method_exists($this, 'render_'.$outputMode))
		{
			// delega il rendering alla classe
            /** @var pinax_components_Component $renderClass */
			$renderClass = new $renderClassName($this, $outputMode, $skipChilds);
			$renderClass->render();
		}
		else if (!is_null($this->getAttribute('skin')) && $outputMode=='html')
		{
			// è definita una skin
			// questa possibilità è accettata solo per output di tipo HTML
			$renderClass = pinax_ObjectFactory::createObject('pinax.components.render.Render', $this, $outputMode, $skipChilds);
			$renderClass->render();
		}
		else
		{
			if (method_exists($this, 'render_'.$outputMode.'_onStart'))
			{
				$this->{'render_'.$outputMode.'_onStart'}();
			}
			else if (method_exists($this, 'render_onStart'))
			{
				$this->render_onStart();
			}
			if (method_exists($this, 'render_'.$outputMode))
			{
				$this->{'render_'.$outputMode}();
			}
			if (!$skipChilds)
			{
				$this->renderChilds($outputMode);
			}

			if (method_exists($this, 'render_'.$outputMode.'_onEnd'))
			{
				$this->{'render_'.$outputMode.'_onEnd'}();
			}
			else if (method_exists($this, 'render_onEnd'))
			{
				$this->render_onEnd();
			}
		}

		return '';
	}

    /**
     * @param null $outputMode
     */
	public function renderChilds($outputMode=NULL)
	{
		if (is_null($outputMode)) $outputMode = $this->_application->getOutputMode();
		if ($this->checkBreakCycle())
		{
			$this->breakCycle(false);
			return;
		}

		if ($this->canHaveChilds)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				if ($this->childComponents[$i]->getAttribute('visible') && $this->childComponents[$i]->getAttribute('enabled'))
				{
					$this->childComponents[$i]->render($outputMode);
					$this->state = COMPONENT_STATE_RENDER;
					if ($this->checkBreakCycle())
					{
						$this->state = COMPONENT_STATE_BLOCKED;
						$this->breakCycle(false);
						break;
					}
				}
			}
		}
	}

    /**
     * @param bool $later
     *
     * @return bool|mixed|null
     */
	public function callController($later=false) {
		if (!$this->controller) {
			$controllerName = $this->getAttribute( 'controllerName' );
			if ($controllerName) {
				if (substr($controllerName, -1)=='*') {
					$controllerName = substr($controllerName, 0, -1);
					$actionAdditionalPath = explode('.', __Request::get($this->getAttribute('actionName')));
					$actionName = ucfirst(array_pop($actionAdditionalPath));
					if (!$actionName) {
						return null;
					}
					$controllerName .= count($actionAdditionalPath) ? implode('.', $actionAdditionalPath).'.' : '';
					if($this->_application->_ajaxMode) {
						$controllerName .= strpos($controllerName, '\\')===false ? 'ajax.' : 'ajax\\';
					}
					$controllerName .= $actionName;
				}

				try {
					$evt = array('type' => PNX_EVT_CALL_CONTROLLER, 'data' => $controllerName);
                	$this->dispatchEvent($evt);

        			$this->controller = $this->_application->getContainer()->get($controllerName, $this->_application, $this);
				} catch (Exception $e) {
					if (strpos($e->getMessage(), 'does not exist')===false) {
						throw $e;
					}
                    			if (class_exists('__DebugBar')) {
						__DebugBar::warning($e->getMessage());
                    			}
				}
			}
		}

		if ($this->controller) {
			return pinax_helpers_PhpScript::callMethodWithParams( $this->controller, $later ? 'executeLater' : 'execute', __Request::getAllAsArray(), true, $this->_application->getContainer());
		}

		return null;
	}

    /**
     * @param        $output
     * @param string $editableRegion
     * @param boolean   $atEnd
     */
	public function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if ($output) {
			$atEnd = empty( $atEnd ) ? $this->getAttribute( 'editableRegionAtEnd' ) : $atEnd;
			// applica i filtri di post rendering
			$this->applyOutputFilters('post', $output);
			$this->_parent->addOutputCode($output, empty($editableRegion) ? $this->getEditableRegion() : $editableRegion, $atEnd );
		}
	}

    /**
     * @return string
     */
	public function getEditableRegion()
	{
		$editableRegion = $this->getAttribute('editableRegion');
		if (empty($editableRegion) && $this->issetAttribute('defaultEditableRegion')) $editableRegion = $this->getAttribute('defaultEditableRegion');
		if (empty($editableRegion) && method_exists($this->_parent, 'getEditableRegion')) $editableRegion = $this->_parent->getEditableRegion();
		return $editableRegion;
	}

    /**
     * @return pinax_components_Component
     */
	public function &getRootComponent()
	{
		return $this->_application->getRootComponent();
	}

    /**
     * @return pinax_components_Component
     */
	public function &getParent()
	{
		return $this->_parent;
	}

    /**
     * @param $className
     *
     * @return pinax_components_Component|null
     */
	public function &getParentByClass($className)
	{
		$className = str_replace('.', '_', strtolower($className));
		return $this->_parent->_getParentByClass($className);
	}

    /**
     * @param $className
     *
     * @return pinax_components_Component|null
     */
	protected function &_getParentByClass($className)
	{
		if (strtolower($this->getClassName())==$className)
		{
			return $this;
		}
		else
		{
			if (is_object($this->_parent) && method_exists($this->_parent, '_getParentByClass'))
			{
				return $this->_parent->_getParentByClass($className);
			}
			else
			{
				$null = NULL;
				return $null;
			}
		}
	}

    /**
     * @return string
     */
	public function getId()
	{
		return $this->getAttribute('id');
	}

    /**
     * @return string
     */
    public function getOriginalId()
    {

        $originalId = $this->_originalId;
        if (preg_match("/\{php\:.*\}/i", $originalId)) {
            $phpcode    = pinax_helpers_PhpScript::parse($originalId);
            $originalId = eval( $phpcode );
        }

        return $originalId;
    }

    /**
     * @param $value
     */
	public function setId($value)
	{
		$this->setAttribute('id', $value);
	}

	/**
	 * @return array
	 */
	public function &_getIDmapArray()
	{
		static $_valuesArray = array();
		return $_valuesArray;
	}

    /**
     * @param $id
     *
     * @return pinax_components_Component
     */
    public function &getComponentById($id)
	{
		$id = preg_replace("/\{(.*)\}/i", "$1", $id);
		$IDmap = & pinax_components_Component::_getIDmapArray();
		$result = NULL;
		if (array_key_exists($id, $IDmap)) $result = &$IDmap[$id];
		return $result;
	}

	/**
	 * @return void
	 */
	public function setCustomClass()
	{
	}

    /**
     * @param $value
     */
	public function setText($value)
	{
		$this->_content = $value;
	}

    /**
     * @return null|string
     */
	public function getText()
	{
		$text = $this->getAttribute('text');
		return (empty($this->_content) && !empty($text)) ? html_entity_decode( $text ) : $this->_content;
	}

    /**
     * @return mixed
     */
	public function getContent()
	{
		return $this->_content;
	}

    /**
     * @return mixed
     */
	public function getTagName()
	{
		$tag = explode(':', $this->_tagname);
		return $tag[count($tag)-1];
	}

	/**
	 * @return void
	 */
	public function debugComponentId()
	{
		$IDmap = & pinax_components_Component::_getIDmapArray();
		print_r(array_keys($IDmap ));
	}

    /**
     * @param int $depth
     */
	public function debugComponentTree($depth=0)
	{
		if ($depth==0) echo 'id: '.$this->getId().' tagname: '.$this->_tagname.' orginalId: '.$this->getOriginalId()."\r\n<br>";
		$depth++;
		for ($i=0; $i<count($this->childComponents); $i++)
		{
			echo str_repeat(" ", $depth).'id: '.$this->childComponents[$i]->getId().' tagname: '.$this->childComponents[$i]->_tagname.' orginalId: '.$this->childComponents[$i]->getOriginalId()."\r\n<br>";
			$this->childComponents[$i]->debugComponentTree($depth);
		}
	}

	/**
	 * @param string $id
	 * @param string $bindTo
	 * @return mixed
	 */
	public function loadContent($id, $bindTo = '')
	{
		return '';
	}

    /**
     * @param $output
     *
     * @return mixed
     */
	public function encodeOuput($output)
	{
		return pinax_encodeOutput($output);
	}

    /**
     * @param $obj
     * @param $functionName
     */
	public function doLater(&$obj, $functionName)
	{
		$doLaterActions = &pinax_ObjectValues::get('org.pinax:components.Component', 'doLater');
		$doLaterActions[] = array('class'=> &$obj, 'function' => $functionName);
	}

	/**
	 * @return void
	 */
	public function resetDoLater()
	{
		$doLaterActions = &pinax_ObjectValues::get('org.pinax:components.Component', 'doLater');
		$doLaterActions = array();
	}

	/**
	 * @return void
	 */
	public function execDoLater()
	{
		$doLaterActions = &pinax_ObjectValues::get('org.pinax:components.Component', 'doLater');
		for ($i = 0; $i<count($doLaterActions); $i++)
		{
			call_user_func(array(&$doLaterActions[$i]['class'], $doLaterActions[$i]['function']));
		}
		$this->resetDoLater();
	}

    /**
     * @param array $attributes
     *
     * @return string
     */
	protected function _renderAttributes($attributes=[], $data=null, $emptyAttributes=[])
	{
		if (is_null($data)) {
			$data = $this->getAttribute( 'data' );
		}
		return pinax_helpers_Html::renderAttributes($attributes, $data, $emptyAttributes);
	}

    /**
     * @param bool $childrensReset
     */
	public function resetContent($childrensReset=false)
	{
		$this->_content = '';
		if ($this->canHaveChilds && $childrensReset)
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$this->childComponents[$i]->resetContent(true);
			}
		}
	}

    /**
     * @param $className
     */
	protected function createChildsFromModel($className)
	{
		// crea i figli
        /** @var pinax_components_Component $newComponent */
		$newComponent 	= &pinax_ObjectFactory::createComponent($className, $this->_application, $this, '', $this->getId());
		for($i=0; $i<count($newComponent->childComponents); $i++)
		{
			$newComponent->childComponents[$i]->remapAttributes($this->getId().'-');
			$this->addChild($newComponent->childComponents[$i]);
			$newComponent->childComponents[$i]->_parent = &$this;
		}
		$this->initChilds();

		// TODO
		// problema da risolvere in modo più elengante
		// quando si crea un componente figlio come in questo caso
		// l'id del component nella mappa degli ID viene sovrascritto
		// con il componente nuovo perché al memoneto della creazione gli viene passato lo stesso ID
		$this->setAttribute('id', $this->getId());
	}

	/**
	 * @param boolean $value
	 * @return void
	 */
	protected function breakCycle($value=true)
	{
		$breakClice = &pinax_components_Component::_breakCycle();
		$breakClice[0] = $value;
	}

	/**
	 * @return boolean
	 */
	protected function checkBreakCycle()
	{
		$breakClice = &pinax_components_Component::_breakCycle();
		return $breakClice[0];
	}

	/**
	 * @return array
	 */
	protected function &_breakCycle()
	{
		static $_breakCycle = array();
		return $_breakCycle;
	}

    /**
     * @return boolean
     */
	public function validate()
	{
		if ($this->canHaveChilds && $this->getAttribute( 'enabled' ) && $this->getAttribute( 'visible' ) )
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$this->childComponents[$i]->validate();
			}
		}
		return count( pinax_components_Component::validateErrors() ) == 0;
	}

	/**
	 * @return array
	 */
	private static function &validateErrors()
	{
		static $_validateErrors = array();
		return $_validateErrors;
	}

    /**
     * @param $message
     */
	public function validateAddError( $message )
	{
		$validateError = &self::validateErrors() ;
		$validateError[] = array( 'component' => &$this, 'message' => $message );
		pinax_application_MessageStack::add( $message, PNX_MESSAGE_ERROR );
	}

    /**
     * @param $mode
     * @param $value
     */
	protected function applyOutputFilters($mode, &$value)
	{
		$outputFilters = &pinax_ObjectValues::get('org.pinax:components.Component', 'OutputFilter.'.$mode);
		if (isset($outputFilters[$this->_tagname]))
		{
			foreach ($outputFilters[$this->_tagname] as $f)
			{
				if (method_exists($f, '__invoke')) {
					$f($value, $mode);
				} else {
					$fObj = new $f;
					if (is_object($fObj))
					{
						$fObj->apply($value, $this);
					}
				}
			}
		}
	}


    /**
     * @return string
     */
	public function getOgValue() {
		return strip_tags( $this->_content );
	}

	/**
	 * @return void
	 */
	public function setOgValue() {
		$og = $this->getAttribute('og');
		if ($og) {
			pinax_ObjectValues::set('pinax.og', $og, $this->getOgValue() );
		}
	}

    /**
     * @return bool
     */
	public function controllerDirectOutput() {
		return $this->controller && $this->controller->directOutput;
	}

    /**
     * @return string
     */
	public function getAjaxUrl()
	{
		return 'ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&action=';
	}

	/**
	 * @return string
	 */
	public function skin()
	{
		return $this->getAttribute('skin');
	}


}
