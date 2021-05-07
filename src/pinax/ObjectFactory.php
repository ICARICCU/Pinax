<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_ObjectFactory
{
    /**
	 * @return null|object
	 *
	 * @throws Exception
	 */
	static function createObject()
    {
        $args = func_get_args();

        // Retrieve class from object name string
        $classPath = array_shift($args);
        if (substr($classPath, -1, 1) == '*') {
           throw new \Exception(sprintf('%s: can\'t create class with * (%s)', __METHOD__, $classPath));
        }

        $classPath = self::resolveClass($classPath);
        $className = str_replace('.', '_', $classPath);

        if (!$className) {
            // for compatibility
            // NOTE: in the next version replace with Exception
            return null;
        } else if (!class_exists($className)) {
            throw new \Exception(sprintf('%s: class not found: %s', __METHOD__, $className));
        }

        if (empty($args)) {
            return new $className();
        }

        $reflectionClass = new \ReflectionClass($className);

        if (null === $reflectionClass->getConstructor()) {
            throw new \Exception(sprintf('%s: class %s does not have constructor', __METHOD__, $className));
        }

        // Can be removed if we are sure constructor parameters are not passed by reference
        $rewriteArgs = array();
        $reflectionMethod = new ReflectionMethod($className,  '__construct');
        $construcParameters = $reflectionMethod->getParameters();
        $numArgs = count($args);
        foreach ($construcParameters as $key => $param) {
            if ($param->isDefaultValueAvailable()) {
                $rewriteArgs[$key] = $param->getDefaultValue();
            }
            if ($key < $numArgs) {
                if ($param->isPassedByReference()) {
                    $rewriteArgs[$key] = &$args[$key];
        } else {
                    $rewriteArgs[$key] = $args[$key];
                }
            }
        }
        return $reflectionClass->newInstanceArgs($rewriteArgs);
    }


    /**
     * @param string $className
     * @param pinax_application_Application $application
     * @param pinax_components_Component $parent
     * @param string $tagName
     * @param string $id
     * @param string $originalId
     * @param bool $skipImport
     * @param string $mode
     *
     * @return mixed
     */
    static function &createComponent($className, &$application, &$parent, $tagName, $id, $originalId='', $skipImport=false, $mode='')
    {
        $className = self::resolveClass($className);
        $componentClassName = str_replace('.', '_', $className);

        if (!class_exists($componentClassName))
        {
            if (!strpos($componentClassName, '\\')) {
                // controlla se il file className.xml esiste sia nelle classi dell'applicazione
                // si in quelle di sistema
                // se esiste:
                // deve compilarlo e caricarlo
                // se non esiste
                // deve dare un messaggio di errore

                // TODO
                // in questo modo non carica eventuali classi dal core
                //
                // TODO
                // deve essere prevista anche la compilazione dei models se sono in PHP e non in XML
                //
                $fileName = pinax_findClassPath($className);
                $pathInfo = pathinfo($fileName);
                if (empty($pathInfo['basename']))
                {
                    trigger_error($className.': component file not found', E_USER_ERROR);
                }
                if ($pathInfo['extension']=='xml')
                {
                    /** @var pinax_compilers_Component $compiler */
                    $compiler = self::createObject('pinax.compilers.Component');
                    $compiledFileName = $compiler->verify($fileName, array('originalClassName' => $className, 'mode' => $mode));
                    require_once($compiledFileName);
                    $componentClassName = pinax_basename($compiledFileName);
                }
                else
                {
                    require_once($fileName);
                }
            }

            $newObj = new $componentClassName($application, $parent, $tagName, $id, $originalId, $skipImport);
            return $newObj;
        }

        $newObj =  new $componentClassName($application, $parent, $tagName, $id, $originalId, $skipImport);
        return $newObj;
    }

    /**
     * @param string $classPath
     * @param integer $connectionNumber
     *
     * @return pinax_dataAccessDoctrine_AbstractActiveRecord
     *
     * @throws pinax_compilers_CompilerException
     */
    static function &createModel($classPath, $connectionNumber=null)
    {
        $classInfo = self::resolveClassNew($classPath);
        if (isset($classInfo['path'])) {
            $compiler             = self::createObject('pinax.compilers.Model');
            $compiledFileName     = $compiler->verify($classInfo['path'], array('originalClassName' => $classInfo['originalClassName']));
            require_once($compiledFileName);
            $className = pinax_basename($compiledFileName);
            $newObj = $connectionNumber ? new $className($connectionNumber) : new $className();

            $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
            $classMap[$classPath] = $className;
        } else if (isset($classInfo['class'])) {
            $newObj = $connectionNumber ? new $classInfo['class']($connectionNumber) : new $classInfo['class']();
        } else {
            throw pinax_compilers_CompilerException::fileNotFound($classPath);
        }
        return $newObj;
    }

    /**
     * @param string $classPath
     * @param string $queryName
     * @param array $options
     *
     * @return pinax_dataAccessDoctrine_AbstractRecordIterator
     *
     * @throws pinax_compilers_CompilerException
     */
    static function &createModelIterator($classPath, $queryName=null, $options=array())
    {
        /** @var pinax_dataAccessDoctrine_ActiveRecord $ar */
        $ar = self::createModel($classPath);
        if ($ar instanceof Iterator) {
            $it = $ar;
        } else {
            $it = $ar->createRecordIterator();
        }

        if ($queryName) {
            $it->load($queryName, isset($options['params']) ? $options['params'] : null);

            if (isset($options['filters'])) {
                $it->setFilters($options['filters']);
            }

            if (isset($options['order'])) {
                $it->setOrderBy($options['order']);
            }

            if (isset($options['limit'])) {
                $it->limit($options['limit']);
            }
        }

        return $it;
    }

    /**
     * @param mixed $it
     * @param int   $lifeTime
     *
     * @return pinax_dataAccessDoctrine_cache_QueryCacheInterface
     */
    static function &createQueryCache($it, $lifeTime = -1)
    {
        $queryClassName = __Config::get('pinax.database.caching') == 'redis' ? 'QueryRedis' : 'QueryFile';
        return self::createObject('pinax.dataAccessDoctrine.cache.'.$queryClassName, $it, $lifeTime);
    }


    /**
     * @param pinax_application_Application $application
     * @param string $pageType
     * @param string $path
     * @param array $options
     *
     * @return mixed
     */
    static function &createPage(&$application, $pageType, $path=NULL, $options=NULL)
    {
        $pageType = self::resolvePageType($pageType);
        $options['pageType'] = $pageType.'.xml';
        $options['path'] = is_null($path) ? pinax_Paths::getRealPath('APPLICATION_PAGE_TYPE') : $path;
        $fileName = $options['path'].$options['pageType'];

        if (isset($options['pathTemplate']) && isset($options['mode'])) {
            $verifyFileName = $options[ 'pathTemplate' ].'/pageTypes/'.$options[ 'pageType' ];
            if (file_exists($verifyFileName)) {
                $options['verifyFileName'] = $verifyFileName;
            }
        }

        if ( !file_exists( $fileName ) ) {
            $fileName = pinax_findClassPath( $pageType, true, true);
            if ( !$fileName ) {
                throw new Exception( 'PageType not found '.$pageType );
            }
        }

        $compiler = self::createObject('pinax.compilers.PageType');
        $compiledFileName = $compiler->verify($fileName, $options);

        // TODO verificare se la pagina è stata compilata
        require_once($compiledFileName);

        $idPrefix = isset($options['idPrefix']) ? $options['idPrefix'] : '';
        $className = pinax_basename($compiledFileName);
        $newObj = new $className($application, isset($options['skipImport']) ? $options['skipImport'] : false, $idPrefix);
        return $newObj;
    }

    /**
     * @param pinax_components_Component $component
     * @param pinax_application_Application $application
     * @param string $pageType
     * @param string $path
     * @param array $options
     * @param string $remapId
     * @param bool $atTop
     *
     * @return void
     */
    static function attachPageToComponent($component, $application, $pageType, $path, $options, $remapId, $atTop=true)
    {
        $originalRootComponent = $application->getRootComponent();
        $originalChildren = $component->childComponents;
        $component->childComponents = array();
        self::createPage($application, $pageType, $path, $options);
        $rootComponent = $application->getRootComponent();
        $rootComponent->init();

        for($i=0; $i<count($rootComponent->childComponents); $i++)
        {
            $rootComponent->childComponents[$i]->remapAttributes($remapId);
        }

        $rootComponent->execDoLater();
        $application->_rootComponent = &$originalRootComponent;

        for($i=0; $i<count($rootComponent->childComponents); $i++)
        {
            $component->addChild($rootComponent->childComponents[$i]);
            $rootComponent->childComponents[$i]->_parent = &$component;
        }

        $component->childComponents = $atTop ? array_merge($component->childComponents, $originalChildren) :
                                               array_merge($originalChildren, $component->childComponents);
    }

    /**
     * @param string $orig
     * @param string $dest
     *
     * @return void
     */
    static function remapClass($orig='', $dest='')
    {
        $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
        $classMap[$orig] = $dest;
    }

    /**
     * @return void
     */
    static function resetRemapClass()
    {
        pinax_ObjectValues::set('pinax.ObjectFactory', 'ClassMap', null);
    }

    /**
     * @param string $classPath
     * @return mixed
     */
    static function resolveClass($classPath)
    {
        $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
        return isset($classMap[$classPath]) ? $classMap[$classPath] : $classPath;
    }

    /**
     * @param string $classPath
     *
     * @return array
     */
    static function resolveClassNew($classPath)
    {
        $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
        $newClassPath = isset($classMap[$classPath]) ? $classMap[$classPath] : $classPath;
        $className = str_replace('.', '_', $newClassPath);
        if (!class_exists($className)) {
            return array('originalClassName' => $classPath, 'path' => pinax_findClassPath($newClassPath));
        } else {
            return array('originalClassName' => $classPath, 'class' => $className);
        }
    }

    /**
     * @param  string $orig
     * @return string
     */
    public static function getRemapClass($orig)
    {
        $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
        return isset($classMap[$orig]) ? $classMap[$orig] : null;
    }
    
    /**
     * @param  string $orig
     * @return void
     */
    public static function removeRemapClass($orig)
    {
        $classMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'ClassMap', array());
        unset($classMap[$orig]);
    }

    /**
     * @param string $orig
     * @param string $dest
     *
     * @return void
     */
    static function remapPageType($orig='', $dest='')
    {
        $orig = preg_replace('/\.xml$/i', '', $orig);
        $dest = preg_replace('/\.xml$/i', '', $dest);
        $pageTypeMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'PageTypeMap', array());
        $pageTypeMap[$orig] = $dest;
    }

    /**
     * @param $pageTypePath
     * @param string $pageTypePath
     *
     * @return mixed
     */
    static function resolvePageType($pageTypePath)
    {
        $pageTypeMap = &pinax_ObjectValues::get('pinax.ObjectFactory', 'PageTypeMap', array());
        return isset($pageTypeMap[$pageTypePath]) ? $pageTypeMap[$pageTypePath] : $pageTypePath;
    }

    /**
     * @param string $cachedFile
     * @param string $fileName
     *
     * @return void
     */
    static function requireComponent( $cachedFile, $fileName )
    {
        if ( !file_exists( $cachedFile ) )
        {
            /** @var pinax_compilers_Component $compiler */
            $compiler = self::createObject('pinax.compilers.Component');
            $cachedFile = $compiler->verify($fileName);
        }
        require_once( $cachedFile );
    }
}
