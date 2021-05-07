<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_Config implements pinax_interfaces_Config
{
    private $configArray = [];

    /**
     * @param string $serverName
     */
    function __construct($serverName='')
    {
        $this->init($serverName);
    }


    /**
	 * @param string $serverName
	 *
	 * @return void
	 */
	private function init( $serverName='' )
	{
        $this->parse( $serverName );

		$debugErrorLevel = $this->get('DEBUG_ERROR_LEVEL');
        if ($debugErrorLevel != '') {
        	$debugErrorLevel = explode(' ', $debugErrorLevel);
        	$level = 0;
        	$lastOp = '';
        	foreach($debugErrorLevel as $v) {
        		if ($v=='&' || $v=='|') {
        			$lastOp = $v;
        		} else if ($v{0}=='~') {
	        		$level = $level &~constant(substr($v, 1));
        		} else if ($lastOp=='|') {
        			$level = $level | constant($v);
        		} else if ($lastOp=='&') {
        			$level = $level & constant($v);
        		} else {
        			$level = $level + constant($v);
        		}
        	}

            error_reporting($level);
        }
	}

    /**
     * @param string $code
     * @param mixed $defaultValue
     * @return mixed|null
     */
	public function get($code, $defaultValue=null)
	{
		$value = isset($this->configArray[$code]) ? $this->configArray[$code] : $defaultValue;
		if( strpos($value, "{{") !== false )
        {
            preg_match_all( "/\{\{env:([^\{]*)\}\}/U", $value, $resmatch );
			if (count($resmatch[0])) {
				foreach( $resmatch[1] as $varname)
				{
					list($envName, $envDefaultValue) = explode(',', $varname);
					$envValue = getenv($envName);
					if (($envValue===false || is_null($envValue)) && $envDefaultValue) {
						$envValue = $envDefaultValue;
					}
					$value = str_replace('{{env:'.$varname.'}}', $envValue, $value);
				}
  			}

      		preg_match_all( "/\{\{path:([^\{]*)\}\}/U", $value, $resmatch );
		    if (count($resmatch[0])) {
				foreach( $resmatch[1] as $varname)
				{
					$value = str_replace('{{path:'.$varname.'}}', pinax_Paths::get( $varname ), $value);
				}
            }

            preg_match_all( "/\{\{([^\{]*)\}\}/U", $value, $resmatch );
			if (count($resmatch[0])) {
				foreach( $resmatch[1] as $varname)
				{
					$value = str_replace('{{'.$varname.'}}', $this->get( $varname ), $value);
				}
            }

            if ( $value === "true" )
            {
                $value = true;
            }
            else if ( $value === "false" )
            {
                $value = false;
            }
            else if (is_numeric($value)){
                $value = (int)$value;
            }
        }

		return $value;
	}

    /**
     * @param string $code
     * @param string $value
     *
     * @return void
     */
    public function set($code, $value)
	{
		if ( $value === "true" )
		{
			$value = true;
		}
		else if ( $value === "false" )
		{
			$value = false;
		}

		$this->configArray[$code] = $value;
	}

    /**
     * @param  string $code
     * @return boolean
     */
    public function exists($code)
    {
        return isset($this->configArray[$code]);
    }

    /**
     * @return void
     */
    public function dump()
	{
		var_dump($this->configArray);
	}

    /**
     * @return array
     */
	public function getAllAsArray()
	{
		return array_merge($this->configArray, []);
	}

    /**
     * @param string $serverName
     *
     * @throws Exception
     *
     * @return void
     */
    private function parse( $serverName='' )
	{
		// imposta i valori di default
		$this->configArray['DEBUG'] 				= false;
		$this->configArray['DEBUG_ERROR_LEVEL'] 	= '';
		$this->configArray['ERROR_DUMP']			= '';
		$this->configArray['DATASOURCE_MODE'] 	= '';
		$this->configArray['SESSION_PREFIX'] 		= '';
		$this->configArray['SESSION_TIMEOUT'] 	= 1800;
		$this->configArray['DEFAULT_LANGUAGE'] 	= 'en';
        $this->configArray['DEFAULT_LANGUAGE_ID'] = '1';
		$this->configArray['pinax.languages.available'] = '{{DEFAULT_LANGUAGE}}';
		$this->configArray['CHARSET'] 			= 'utf-8';
		$this->configArray['DB_LAYER'] 			= 'pdo';
		$this->configArray['DB_TYPE'] 			= 'none';
		$this->configArray['DB_HOST'] 			= '';
		$this->configArray['DB_NAME'] 			= '';
		$this->configArray['DB_USER'] 			= '';
		$this->configArray['DB_PSW'] 				= '';
        $this->configArray['DB_MYSQL_BUFFERED_QUERY'] = false;
		$this->configArray['DB_ATTR_PERSISTENT'] = false;
		$this->configArray['DB_PREFIX'] 			= '';
		$this->configArray['DB_SOCKET'] 			= '';
		$this->configArray['SMTP_HOST'] 			= '';
		$this->configArray['SMTP_PORT'] 			= 25;
		$this->configArray['SMTP_USER'] 			= '';
        $this->configArray['SMTP_PSW']            = '';
		$this->configArray['SMTP_SECURE'] 		= '';
		$this->configArray['SMTP_SENDER'] 		= '';
		$this->configArray['SMTP_EMAIL'] 			= '';
		$this->configArray['START_PAGE'] 			= 'HOME';
		$this->configArray['CACHE_IMAGES'] 		= -1;
        $this->configArray['CACHE_IMAGES_DIR_LEVEL'] = 0;
		$this->configArray['CACHE_CODE'] 			= -1;
        $this->configArray['CACHE_CODE_DIR_LEVEL'] = 0;
        $this->configArray['ACL_MODE']        	= 'xml';
		$this->configArray['ACL_CLASS']			= 'pinax.application.Acl';
		$this->configArray['pinax.acl.defaultIfNoDefined'] = false;
		$this->configArray['ADM_THUMBNAIL_CROP']	= false;
		$this->configArray['ADM_THUMBNAIL_CROPPOS']	= 1;
		$this->configArray['ADM_SITE_MAX_DEPTH']	= NULL;
		$this->configArray['HIDE_PRIVATE_PAGE']	= true;
		$this->configArray['APP_NAME'] 			= '';
		$this->configArray['APP_VERSION'] 		= '';
		$this->configArray['APP_AUTHOR'] 			= '';
		$this->configArray['CORE_VERSION'] 		= PNX_CORE_VERSION;
		$this->configArray['SEF_URL'] 			= false;
		$this->configArray['PRESERVE_SCRIPT_NAME']= false;
		$this->configArray['SITEMAP'] 			= 'config/siteMap.xml';
		$this->configArray['REMEMBER_PAGEID']		= false;
		$this->configArray['PSW_METHOD']			= 'MD5';
		$this->configArray['USER_LOG']			= false;
		$this->configArray['JS_COMPRESS']			= false;
		$this->configArray['JPG_COMPRESSION']		= 80;
		$this->configArray['THUMB_WIDTH']			= 150;
		$this->configArray['THUMB_HEIGHT']		= 150;
		$this->configArray['THUMB_SMALL_WIDTH']	= 50;
		$this->configArray['THUMB_SMALL_HEIGHT']	= 50;
		$this->configArray['IMG_LIST_WIDTH']		= 100;
		$this->configArray['IMG_LIST_HEIGHT']		= 100;
		$this->configArray['IMG_WIDTH_ZOOM'] 		= 800;
		$this->configArray['IMG_HEIGHT_ZOOM'] 	= 600;
		$this->configArray['IMG_WIDTH'] 			= 200;
		$this->configArray['IMG_HEIGHT'] 			= 200;
		$this->configArray['STATIC_FOLDER']		= NULL;
		$this->configArray['TEMPLATE_FOLDER']		= NULL;
		$this->configArray['FORM_ITEM_TEMPLATE']	= '<div class="formItem">##FORM_LABEL####FORM_ITEM##<br /></div>';
		$this->configArray['FORM_ITEM_RIGHT_LABEL_TEMPLATE']	= '<div class="formItemRigthLabel">##FORM_LABEL####FORM_ITEM##<br /></div>';
		$this->configArray['FORM_ITEM_HIDEN_TEMPLATE'] = '<div class="formItemHidden">##FORM_ITEM##</div>';
		$this->configArray['SITE_ID']				= '{{pinax.multisite.id}}';
		$this->configArray['ALLOW_MODE_OVERRIDE']	= false;
		$this->configArray['USER_DEFAULT_ACTIVE_STATE'] = 0;
		$this->configArray['USER_DEFAULT_USERGROUP'] = 4;
		$this->configArray['MULTILANGUAGE_ENABLED'] 	= false;
		$this->configArray['ACL_ENABLED'] 		= false;
		$this->configArray['CATEGORY_ENABLED'] 	= false;
		$this->configArray['SANITIZE_URL'] 		= true;
		$this->configArray['DEFAULT_SKIN_TYPE'] 	= 'PHPTAL';

		$this->configArray['AJAX_SKIP_DECODE'] 		= true;
		$this->configArray['PINAX_ADD_CORE_JS'] 		= true;
		$this->configArray['PINAX_ADD_JS_LIB'] 		= true;
		$this->configArray['PINAX_ADD_JQUERY_JS'] 	= true;
		$this->configArray['PINAX_ADD_JQUERYUI_JS'] 	= false;
		$this->configArray['PINAX_ADD_VALIDATE_JS'] 	= true;
		$this->configArray['PINAX_JQUERY'] 			= 'jquery-1.7.2.min.js';
		$this->configArray['PINAX_JQUERYUI'] 			= 'jquery-ui-1.8.14.custom.min.js';
		$this->configArray['PINAX_JQUERYUI_THEME'] 	= 'ui-lightness/jquery-ui-1.8.14.custom.css';
		$this->configArray['TINY_MCE_DEF_PLUGINS'] 	= 'inlinepopups,paste,directionality,xhtmlxtras,fullscreen,PNX_links,PNX_image';
		$this->configArray['TINY_MCE_PLUGINS'] 	= '';
		$this->configArray['TINY_MCE_BUTTONS1'] 	= 'bold,italic,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,blockquote';
		$this->configArray['TINY_MCE_BUTTONS2'] 	= 'formatselect,|,undo,redo,pastetext,pasteword,removeformat,|,link,unlink,anchor,image,hr,charmap,|,code,fullscreen';
        $this->configArray['TINY_MCE_BUTTONS3']   = '';
		$this->configArray['TINY_MCE_STYLES'] 	= '[]';
        $this->configArray['TINY_MCE_IMG_STYLES']     = '';
        $this->configArray['TINY_MCE_IMG_SIZES']  = '';
		$this->configArray['TINY_MCE_TEMPLATES'] 	= '';
		$this->configArray['TINY_MCE_INTERNAL_LINK'] 	= true;
		$this->configArray['TINY_MCE_ALLOW_LINK_TARGET'] 	= false;
		$this->configArray['COLORBOX_SLIDESHOWAUTO'] 	= true;
		$this->configArray['BASE_REGISTRY_PATH'] 			= 'org/pinax';
		$this->configArray['REGISTRY_TEMPLATE_NAME'] 		= '{{BASE_REGISTRY_PATH}}/templateName';
		$this->configArray['REGISTRY_TEMPLATE_VALUES'] 	= '{{BASE_REGISTRY_PATH}}/templateValues/';
		$this->configArray['REGISTRY_SITE_PROP'] 			= '{{BASE_REGISTRY_PATH}}/siteProp/';
		$this->configArray['REGISTRY_METANAVIGATION'] 	= '{{BASE_REGISTRY_PATH}}/metanavigation/';
		$this->configArray['pinax.media.imageMagick'] 			= false;
        $this->configArray['pinax.media.image.remoteCache.lifetime'] = 86400;
		$this->configArray['pinax.routing.newParser'] 		    = true;
		$this->configArray['pinax.form.cssClass'] = '';
        $this->configArray['pinax.formElement.cssClassLabel'] = '';
		$this->configArray['pinax.formElement.input.cssClass'] = '';
        $this->configArray['pinax.formElement.select.cssClass'] = '';
        $this->configArray['pinax.formElement.checkbox.cssClass'] = '';
        $this->configArray['pinax.formElement.radio.cssClass'] = '';
		$this->configArray['pinax.formElement.admCssClass'] = '';
        $this->configArray['pinax.formButton.cssClass']  = '';
        $this->configArray['pinax.formButton.region']  = '';
		$this->configArray['pinax.iconSet.prefix'] = 'icon';
		$this->configArray['pinax.iconSet.cssClass'] = 'btn-icon';
		$this->configArray['pinax.iconSet.cssClass.primary'] = 'btn-success';
		$this->configArray['pinax.iconSet.cssClass.secondary'] = 'btn-secondary';
		$this->configArray['pinax.iconSet.cssClass.other'] = 'btn-info';
		$this->configArray['pinax.iconSet.cssClass.delete'] = 'btn-warning';
		$this->configArray['pinax.iconSet.cssClass.link'] = 'btn-link';
        $this->configArray['pinax.icon.add'] = '{{pinax.iconSet.cssClass}}-plus';
		$this->configArray['pinax.datagrid.action.editCssClass'] 		= '{{pinax.iconSet.cssClass}} {{pinax.iconSet.cssClass.primary}} {{pinax.iconSet.prefix}}-pencil';
		$this->configArray['pinax.datagrid.action.editDraftCssClass'] = '{{pinax.iconSet.cssClass}} {{pinax.iconSet.cssClass.primary}} {{pinax.iconSet.prefix}}-edit';
		$this->configArray['pinax.datagrid.action.deleteCssClass'] 	= '{{pinax.iconSet.cssClass}} {{pinax.iconSet.cssClass.delete}} {{pinax.iconSet.prefix}}-trash';
		$this->configArray['pinax.datagrid.action.hideCssClass'] 		= '{{pinax.iconSet.cssClass}} {{pinax.iconSet.cssClass.other}} {{pinax.iconSet.prefix}}-eye-close';
        $this->configArray['pinax.datagrid.action.showCssClass']      = '{{pinax.iconSet.cssClass}} {{pinax.iconSet.cssClass.other}} {{pinax.iconSet.prefix}}-eye-open';
        $this->configArray['pinax.datagrid.checkbox.on']          = '{{pinax.iconSet.cssClass}} {{pinax.iconSet.prefix}}-check';
		$this->configArray['pinax.datagrid.checkbox.off'] 		= '{{pinax.iconSet.cssClass}} {{pinax.iconSet.prefix}}-check-empty';
		$this->configArray['pinax.authentication'] 		= 'pinax.authentication.Database';
		$this->configArray['pinax.dataAccess.schemaManager.cacheLife'] 		= 36000;
		$this->configArray['pinax.dataAccess.serializationMode'] 				= 'json';
		$this->configArray['pinax.dataAccess.document.enableComment'] 		= false;
        $this->configArray['pinax.dataAccess.sqlCount.new']       = false;
		$this->configArray['pinax.session.store'] 		= '';
        $this->configArray['pinax.dataAccess.validate'] = true;
        $this->configArray['pinax.multisite.sitename'] = '';
        $this->configArray['pinax.multisite.id'] = 0;
		$this->configArray['pinax.template.relative.url'] = '';
		$this->configArray['pinax.tab.cssClass'] 	= '';
		$this->configArray['pinax.tab.cssClassCurrent'] 	= 'current';
		$this->configArray['pinax.tab.cssClassItem'] 	= '';
		$this->configArray['pinax.tab.cssClassLink'] 	= '';

        $this->configArray['pinax.jstab'] = 'nav nav-tabs';
        $this->configArray['pinax.jstab.pane'] = 'tab-content';
        $this->configArray['pinax.jstab.tab'] = 'tab-pane';
        $this->configArray['pinax.jstab.content'] = '';
        $this->configArray['pinax.jstab.dropdown.tab'] = 'dropdown';
        $this->configArray['pinax.jstab.dropdown.menu'] = 'dropdown-menu';
        $this->configArray['pinax.jstab.dropdown.caret'] = 'caret';
        $this->configArray['pinax.jstab.dropdown.link'] = 'tab-dropdown dropdown-toggle';
        $this->configArray['pinax.jstab.navigation'] = 'tab-navigation';
        $this->configArray['pinax.jstab.navigation.link'] = 'btn';
        $this->configArray['pinax.jstab.navigation.next'] = 'fa fa-angle-double-right';
        $this->configArray['pinax.jstab.navigation.prev'] = 'fa fa-angle-double-left';
        $this->configArray['pinax.jstab.enableTabListener'] = 'true';
        $this->configArray['pinax.jstab.enableScrollOnChange'] = 'true';
        $this->configArray['pinax.jstab.enableHash'] = 'true';

        $this->configArray['pinax.accordion'] = 'panel panel-default';
        $this->configArray['pinax.accordion.heading'] = 'panel-heading';
        $this->configArray['pinax.accordion.title'] = 'panel-title';
        $this->configArray['pinax.accordion.bodyDiv'] = 'panel-collapse collapse';
        $this->configArray['pinax.accordion.body'] = 'panel-body';
        $this->configArray['pinax.accordion.open'] = 'in';

        $this->configArray['pinax.css.container'] = 'container';
        $this->configArray['pinax.css.containerfluid'] = 'container-fluid';
        $this->configArray['pinax.css.row'] = 'row';
        $this->configArray['pinax.css.rowfluid'] = 'row-fluid';
        $this->configArray['pinax.css.col1'] = 'span1';
        $this->configArray['pinax.css.col2'] = 'span2';
        $this->configArray['pinax.css.col3'] = 'span3';
        $this->configArray['pinax.css.col4'] = 'span4';
        $this->configArray['pinax.css.col5'] = 'span5';
        $this->configArray['pinax.css.col6'] = 'span6';
        $this->configArray['pinax.css.col7'] = 'span7';
        $this->configArray['pinax.css.col8'] = 'span8';
        $this->configArray['pinax.css.col9'] = 'span9';
        $this->configArray['pinax.css.col10'] = 'span10';
        $this->configArray['pinax.css.col11'] = 'span11';
        $this->configArray['pinax.css.col12'] = 'span12';

        $this->configArray['pinax.breadcrumbs.trimLength'] = 30;
        $this->configArray['pinax.breadcrumbs.trimElli'] = '...';

        if (!$serverName) {
            $configFileName = pinax_Paths::get('APPLICATION').'config/config.xml';
            $tempName = array(
                isset($_SERVER['PINAX_APPNAME']) ? $_SERVER['PINAX_APPNAME'] : '',
                getenv('PINAX_SERVER_NAME'),
                is_null( $_SERVER["SERVER_NAME"] ) ? 'console' : $_SERVER["SERVER_NAME"]
            );
            foreach($tempName as $serverName) {
                if ($serverName) {
                    $tempConfigFileName = pinax_Paths::get('APPLICATION').'config/config_'.$serverName.'.xml';
                    if (file_exists($tempConfigFileName)) {
                        $configFileName = $tempConfigFileName;
                        break;
                    }
                }
            }
        } else {
            $configFileName = pinax_Paths::get('APPLICATION').'config/config_'.$serverName.'.xml';
        }

		if (!file_exists($configFileName)) {
            $configFileName = pinax_Paths::get('APPLICATION').'config/config.xml';
            if (!file_exists($configFileName)) {
                throw new Exception('Config file not found.');
            }
		}

        /** @var $compiler pinax_compilers_Config  */
		$compiler 		= pinax_ObjectFactory::createObject('pinax.compilers.Config', null, -1);
		$compiledConfigFileName = $compiler->verify($configFileName);

		// TODO
		// controllare errore
        $configArray = [];
		include($compiledConfigFileName);

        $this->configArray = array_merge($this->configArray, $configArray);
        unset($configArray);

		if (!empty($this->configArray['STATIC_FOLDER']))
		{
			pinax_Paths::set('APPLICATION_STATIC', $this->get( 'STATIC_FOLDER' ) );
		}
		if (!empty($this->configArray['TEMPLATE_FOLDER']))
		{
			pinax_Paths::set('APPLICATION_TEMPLATE', $this->get('TEMPLATE_FOLDER') );
		}

		if ( $this->configArray['ALLOW_MODE_OVERRIDE'] && isset( $_GET['mode'] ) )
		{
			$this->setMode( $_GET['mode'] );
		}

		if (isset($this->configArray['pinax.config.mode'])) {
			$this->setMode($this->configArray['pinax.config.mode']);
		}

		define( 'PNX_CHARSET', strtoupper($this->get( 'CHARSET' )) );
	}

    /**
	 * @param string $modeName
	 *
	 * @return void
	 */
	public function setMode( $modeName )
	{
		if ( isset( $this->configArray[ '__modes__'][ $modeName ] ) )
		{
			foreach( $this->configArray[ '__modes__'][ $modeName ] as $k => $v )
			{
				$this->configArray[ $k ] = $v;
			}
		}
		else
		{
			// TODO: in modalit� debug visualzzare un warning
		}
	}

    /**
     * @return void
     */
    public function destroy()
    {
        $this->configArray = [];
    }
}
