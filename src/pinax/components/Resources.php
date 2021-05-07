<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Resources extends pinax_components_Component
{
    private $resources = [];
    private $resourcesSignature = [];
    private $cacheObj = [];


    /**
     * Init
     *
     * @return  void
     * @access  public
     */
    function init()
    {
        parent::init();

        $this->addEventListener( pinax_events_Resources::ADD_RESOURCE, $this);
    }

    /**
     * @param string $type
     * @param string $src
     * @param string $region
     * @param boolean $minify
     * @param string $media
     */
    public function addResource($type, $src, $region, $minify=false, $media=null, $replaceValues=true)
    {
        $key = $this->keyFromTypeAndRegion($type, $region);

        if (!isset($this->resources[$key])) {
            $this->resources[$key] = [];
            $this->resourcesSignature[$key] = [];
        }
        $this->resources[$key][] = ['src' => $src, 'minify' => $minify, 'media' => $media, 'replaceValues' => $replaceValues];
        $this->resourcesSignature[$key][] = $src.$minify;
    }

    /**
     * @param string $outputMode
     * @param bool|false $skipChilds
     * @throws Exception
     */
    public function render($outputMode=NULL, $skipChilds=false)
    {
        $language = $this->_application->getLanguage();
        foreach($this->resources as $k=>$v) {
            $typeAndRegion = $this->typeAndRegionFromKey($k);
            $fileName = $this->compiledResource($typeAndRegion['type'],
                                                $language,
                                                $v,
                                                implode('', $this->resourcesSignature[$k]));

            if ($typeAndRegion['type']=='js') {
                $this->addOutputCode(pinax_helpers_JS::linkJSfile($fileName), $typeAndRegion['region']);
            } else if ($typeAndRegion['type']=='css') {
                $this->addOutputCode(pinax_helpers_CSS::linkCSSfile($fileName), $typeAndRegion['region']);
            }
        }
    }

    /**
     * @param  string $type
     * @return pinax_cache_CacheFile
     */
    private function cacheObj($type)
    {
        if (!$this->cacheObj[$type]) {
            $options = [
                'cacheDir' => pinax_Paths::get('CACHE_JS'),
                'lifeTime' => __Config::get('CACHE_CODE'),
                'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
                'readControlType' => '',
                'fileExtension' => '.'.$type
            ];

            $this->cacheObj[$type] = pinax_ObjectFactory::createObject( 'pinax.cache.CacheFile', $options);
        }

         return $this->cacheObj[$type];
    }

    /**
     * @param  string $type
     * @param  string $language
     * @param  array $files
     * @param  string $cacheSignature
     * @return string
     */
    private function compiledResource($type, $language, $files, $cacheSignature)
    {
        $debugMode = __Config::get('DEBUG');
        $cacheObj = $this->cacheObj($type);
        $cacheSignature .= $language.__Config::get('APP_VERSION');

        $jsFileName = $cacheObj->verify( $cacheSignature );
        if ($jsFileName===false || $debugMode) {
            $fileSource = '';

            foreach($files as $item) {
                $file = $item['src'];
                $file = $this->resolveLanguage($file, $language);
                $file = $this->resolveConfig($file);
                $file = $this->resolvePaths($file);

                if (!$debugMode && !$this->fileExists($file)) {
                    throw pinax_exceptions_GlobalException::resourceNotFound($item['src']);
                }

                try {
                    if (is_dir($file)) {
                        $fileSource .= PHP_EOL.$this->readFolder($file, $type, $debugMode ? false : $item['minify'], $item['media'], $item['replaceValues']);
                    } else {
                        $fileSource .= PHP_EOL.$this->readFile($file, $type, $debugMode ? false : $item['minify'], $item['media'], $item['replaceValues']);
                    }
                } catch (Exception $e) {
                    throw pinax_exceptions_GlobalException::resourceNotFound($item['src']);
                }
            }
            $cacheObj->save($fileSource, NULL, $cacheSignature);
            $jsFileName = $cacheObj->getFileName();
        }
        return $jsFileName;
    }

    /**
     * @param  string $dir
     * @param  string $type
     * @param  boolean $minify
     * @param  string $media
     * @param  boolean $replaceValues
     * @return array
     */
    private function readFolder($dir, $type, $minify, $media, $replaceValues)
    {
        $fileSource = '';
        foreach(glob($dir.'/*.'.$type) as $file) {
            $fileSource .= $this->readFile($file, $type, $minify, $media, $replaceValues);
        }
        return $fileSource;
    }

    /**
     * @param  string $src
     * @param  string $type
     * @param  boolean $minify
     * @param  string $media
     * @param  boolean $replaceValues
     * @return string
     */
    private function readFile($src, $type, $minify, $media, $replaceValues)
    {
        $isRemote = preg_match('/http:|https:/', $src);
        $fileContent = $this->getFileContentWithCache($src, $isRemote).PHP_EOL;

        // if (!$minify) {
        //    $fileContent = '/* '.$src.' */'.PHP_EOL.$fileContent;
        // }

        if ($type==='css') {
            $pathinfo = pathinfo($src);
            $pathDir = $isRemote ? $pathinfo['dirname'].'/' : PNX_HOST.'/'.$pathinfo['dirname'].'/';
            return $this->postProcessingCSS($fileContent, $minify, $media, $pathDir, $replaceValues);
        } else {
            return !$isRemote ? $this->postProcessingJS($fileContent, $minify, $replaceValues) : $fileContent;
        }
    }

    /**
     * @param string $src
     * @param boolean $isRemote
     * @return string
     */
    private function getFileContentWithCache($src, $isRemote)
    {
        $cacheEnabled = __Config::get('DEBUG');
        $fileContent = null;
        $writeCache = $isRemote;
        $cacheFile = __Paths::get('CACHE').md5($src);

        if ($isRemote && $cacheEnabled && file_exists($cacheFile)) {
            $fileContent = file_get_contents($cacheFile);
            $writeCache = false;
        }

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $fileContent = $fileContent ? : file_get_contents($src, false, stream_context_create($arrContextOptions));

        if ($cacheEnabled && $writeCache) {
            file_put_contents($cacheFile, $fileContent);
        }

        return $fileContent;
    }

    /**
     * @param  string $content
     * @param  string $minify
     * @param  boolean $replaceValues
     * @return string
     */
    private function postProcessingJS($content, $minify, $replaceValues)
    {
        if ($replaceValues) {
            $content = $this->replaceConfig($content);
            $content = $this->replaceLocale($content);
        }
        return $minify ? JSMin::minify($content) : $content;
    }

    /**
     * @param  string $content
     * @param  string $minify
     * @param  string $media
     * @param  string $pathDir
     * @param  boolean $replaceValues
     * @return string
     */
    private function postProcessingCSS($content, $minify, $media, $pathDir, $replaceValues)
    {
        if ($replaceValues) {
            $content = $this->replaceConfig($content);
        }
        $content = $this->fixUrlInCss($content, $pathDir);

        if ($media) {
            return '@media '.$media.' {'.$content.'}';
        }
        return $content;
    }

    /**
     * @param  string $content
     * @param  string $pathDir
     * @return string
     */
    private function fixUrlInCss($content, $pathDir)
    {
        preg_match_all('/url\(\s*["\']?([^(\)"\')]*)["\']?\s*\)/', $content, $matches);
        if (count($matches)) {
            $num = count($matches[0]);
            for($i=0; $i<$num; $i++) {
                $url = trim($matches[1][$i]);
                if ($url == 'none' or strpos($url, 'data:')===0) continue;
                if (preg_match('/^(http|https)/', $pathDir) and preg_match('/^(http|https)/', $url)) {
                    $newUrl = $this->relateveToAbsoluteUrl($url);
                } else {
                    $newUrl = $this->relateveToAbsoluteUrl($pathDir.$url);
                }
                $content = str_replace($matches[0][$i],
                                        'url("'.$newUrl.'")',
                                        $content);
            }
        }
        return $content;
    }

    /**
     * @param  string $src
     * @param  string $language
     * @return string
     */
    private function resolveLanguage($src, $language)
    {
        $language2 = $language.'-'.strtoupper($language);
        return str_replace(['##LANG##', '##LANG2##'], [$language, $language2], $src);
    }

    /**
     * @param  string $src
     * @return string
     */
    private function resolveConfig($src)
    {
        return $this->resolveFromRegExp('/{config:(.*)}/Ui', __Config::class, $src);
    }

    /**
     * @param  string $src
     * @return string
     */
    private function resolvePaths($src)
    {
        if (preg_match('/^{([^:]*)}$/', $src)) {
            $src = substr($src, 1, -1);
            return pinax_findClassPath($src, false, false);
        }
        return $this->resolveFromRegExp('/{path:(.*)}/Ui', __Paths::class, $src);
    }

    /**
     * @param  string $pattern
     * @param  object $function
     * @param  string $src
     * @return string
     */
    private function resolveFromRegExp($pattern, $function, $src)
    {
        preg_match_all($pattern, $src, $match);
        if (count($match) && count($match[0])) {
            for ($i=0; $i<count($match[0]); $i++) {
                $value = $function::get($match[1][$i]);
                $src = str_replace($match[0][$i], $value, $src);
            }
        }
        return $src;
    }

    /**
     * @param  string $type
     * @param  string $region
     * @return string
     */
    private function keyFromTypeAndRegion($type, $region)
    {
        return $type.':'.$region;
    }

    /**
     * @param  string $key
     * @return array
     */
    private function typeAndRegionFromKey($key)
    {
        list($type, $region) = explode(':', $key);
        return ['type' => $type, 'region' => $region];
    }

    /**
     * @param  string $url
     * @return string
     */
    private function relateveToAbsoluteUrl($url)
    {
        list($protocol, $urlPart) = explode('://', $url);
        $newUrlPart = array_reduce(explode('/', $urlPart), function($carry, $item) {
            if ($item=='..') {
                array_pop($carry);
                return $carry;
            } else if ($item=='') {
                return $carry;
            }
            $carry[] = $item;
            return $carry;
        }, []);

        return $protocol.'://'.implode('/', $newUrlPart);
    }

    /**
     * @param  string $content
     * @return string
     */
    private function replaceLocale($content)
    {
        preg_match_all('/(\{)((i18n:)([^(\'"\})]*))(\})/', $content, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0])) {
            for ($i=count($matches[0])-1; $i>=0;$i--) {
                $content = str_replace($matches[0][$i][0], __Tp($matches[4][$i][0]), $content);
            }
        }
        return $content;
    }

    /**
     * @param  string $content
     * @return string
     */
    private function replaceConfig($content)
    {
        preg_match_all('/(\{)((config:)([^(\}]*))(\})/', $content, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0])) {
            for ($i=count($matches[0])-1; $i>=0;$i--) {
                $content = str_replace($matches[0][$i][0], __Config::get($matches[4][$i][0]), $content);
            }
        }
        return $content;
    }

    /**
     * @param string $file
     * @return boolean
     */
    private function fileExists($file)
    {
        if (!preg_match('/(http:|https)/', $file)) {
            return file_exists($file);
        }

        $headers = @get_headers($file, null, null);
        return preg_match('/200|302/', $headers[0]);
    }

    /**
     * @param pinax_events_Event $event
     * @return void
     */
    public function onAddResource($event)
    {
        $this->addResource($event->data->type, $event->data->src, $event->data->region, $event->data->minify, $event->data->media, $event->data->replaceValues);
    }


    /**
     * @param  pinax_compilers_PageType $compiler
     * @param  DomNode &$node
     * @param  array &$registredNameSpaces
     * @param  integer &$counter
     * @param  string $parent
     * @param  string $idPrefix
     * @param  string $componentClassInfo
     * @param  string $componentId
     * @return boolean
     */
 	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );
        $supportedAssets = ['js', 'css'];

        foreach ($node->childNodes as $n ) {
            $type = str_replace('pnx:', '', $n->nodeName);
            if ( in_array($type, $supportedAssets)) {
                $src = $n->getAttribute('src');
                $region = $n->getAttribute('editableRegion');
                $minify = $n->hasAttribute('minify') ? $n->getAttribute('minify') : 'false';
                $media = $n->hasAttribute('media') ? $n->getAttribute('media') : '';
                $replace = $n->hasAttribute('replace') ? $n->getAttribute('replace') : 'true';

                if ( $src && $region ) {
                    $compiler->_classSource .= sprintf('$n%s->addResource("%s", "%s", "%s", %s, "%s", %s);', $counter, $type, $src, $region, $minify, $media, $replace);
                }
            }

        }
        return false;
    }
}
