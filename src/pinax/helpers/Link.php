<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Link extends PinaxObject
{
	/**
	 * @return string
	 */
	public static function getDefaultUrl()
	{
		return PNX_SCRIPNAME;
	}

	/**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     *
     * @return mixed|string
     */
    public static function makeURL($route='', $queryVars=array(), $addParam=array())
	{
		return __Routing::makeURL( $route, $queryVars, $addParam );
	}


	/**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     * @param string $onclick
     * @param bool   $encode
     *
     * @return string
     */
    public static function makeLink($route='', $queryVars=array(), $addParam=array(), $onclick='', $encode = true )
	{
		$cssClass = '';
		if (isset($queryVars['cssClass']))
		{
			$cssClass = $queryVars['cssClass'];
			unset($queryVars['cssClass']);
		}
		$rel = '';
		if (isset($queryVars['rel']))
		{
			$rel = $queryVars['rel'];
			unset($queryVars['rel']);
		}
		$icon = '';
		if (isset($queryVars['icon']) && $queryVars['icon']) {
			$icon = '<i class="'.$queryVars['icon'].'"></i> ';
		}
        $dataAttributes = '';
        if (isset($queryVars['dataAttributes'])) {
            $dataAttributes = $queryVars['dataAttributes'];
            unset($queryVars['dataAttributes']);
        }

		$label = isset($queryVars['label']) ? $queryVars['label'] : $queryVars['title'];
		unset( $queryVars['label'] );
		$url = pinax_helpers_Link::makeURL($route, $queryVars, $addParam);
		$target = isset($queryVars['target']) ? $queryVars['target'] : '';
		return pinax_helpers_Html::renderTag(	'a',
													array(	'href' => $url,
                                                            'class' => $cssClass,
															'id' => isset($queryVars['id']) ? $queryVars['id'] : '',
															'title' => pinax_encodeOutput( $queryVars['title'] ),
															'target' => $target,
                                                            'rel' => $rel,
                                                            'dataAttributes' => $dataAttributes,
															'onclick' => $onclick),
													true,
													$icon.($encode ? pinax_encodeOutput( $label ) : $label ));
	}


	/**
     * @param       $route
     * @param array $options
     * @param array $attributes
     *
     * @return string
     */
    public static function makeLink2($route, $options=array(), $attributes=array())
	{
		if (isset($options['id']))
		{
			$attributes['id'] = $options['id'];
		}
		$attributes['title'] 	= pinax_encodeOutput(isset($options['title']) ? $options['title'] : $options['label']);
		$attributes['href'] 	= isset($options['url']) ? $options['url'] : pinax_helpers_Link::makeURL($route, $options);
		$label = $options['label'];
		if ($options['icon']) {
			$label = '<i class="'.$options['icon'].'"></i> '.$label;
		}

		$output = pinax_helpers_Html::renderTag('a', $attributes, true, $label);
		return $output;
	}

	/**
     * @param string $route
     * @param array  $queryVars
     * @param array  $addParam
     *
     * @return string
     */
    public static function makeJSLocation($route='', $queryVars=array(), $addParam=array())
	{
		$url = pinax_helpers_Link::makeURL($route, $queryVars, $addParam);
		return 'location.href = \''.$url.'\'';
	}



	/**
     * @param        $label
     * @param        $url
     * @param string $title
     * @param string $cssClass
     * @param string $rel
     * @param string $target
     *
     * @return string
     */
    public static function makeSimpleLink($label, $url, $title='', $cssClass='', $rel='', $attributes=array())
	{
		if (empty($title)) $title = $label;
		$attributes['href'] = $url;
		$attributes['class'] = $cssClass;
		$attributes['title'] = $title;
		$attributes['rel'] = $rel;
		if (!empty($attributes['icon'])) {
			$label = '<i class="'.$attributes['icon'].'"></i> '.$label;
			unset($attributes['icon']);
		}
		return pinax_helpers_Html::renderTag(	'a',
													$attributes,
													true,
													$label);
	}


	/**
     * @param      $email
     * @param null $label
     *
     * @return string
     */
	public static function makeEmailLink($email, $label=NULL)
	{
		if (empty($label)) $label = $email;
		return '<a href="mailto:'.$email.'" title="'.pinax_encodeOutput($email).'">'.pinax_encodeOutput($label).'</a>';
	}


	/**
     * @param       $route
     * @param array $options
     * @param array $attributes
     *
     * @return string
     */
    public static function imageLink($route, $options=array(), $attributes=array())
	{
		$options['title'] 	= pinax_encodeOutput($options['title']);
		$attributes['title'] = $options['title'];
		$attributes['alt'] 	= $options['title'];
		$attributes['src'] 	= $options['src'];
		$attributes['width'] 	= @$options['width'];
		$attributes['height'] 	= @$options['height'];
		unset($options['src']);
		$url = isset($options['url']) ? $options['url'] : pinax_helpers_Link::makeURL($route, $options);

		$output  = pinax_helpers_Html::renderTag('a',
													array(	'href' => $url,
															'class' => $options['cssClass'],
															'title' => $options['title'],
															'target' => @$options['target']),
													true,
													pinax_helpers_Html::renderTag('img', $attributes));
		return $output;
	}

	/**
     * @param        $iconName
     * @param        $title
     * @param        $routing
     * @param        $params
     * @param string $confirm
     *
     * @return string
     */
	public static function assetsLink( $iconName, $title, $routing, $params, $confirm='' )
	{
		$params[ 'label' ] = __Assets::getIcon( $iconName, $title );
		$params[ 'title' ] = $title;
		return pinax_helpers_Link::makeLink( $routing, $params, array(), $confirm );
	}


    /**
     * @param array $params
     * @param bool  $absolute
     * @param string  $url
     *
     * @return string
     */
    public static function addParams($params=array(), $absolute=false, $url = null)
    {
        $nullUrl = is_null( $url );
        if ( $nullUrl )
        {
            $url = __Routing::$queryString;
        }

        if (count($params))
        {
            foreach($params as $k=>$v)
            {
                if (preg_match('/'.$k.'=/', $url))
                {
                    $url = preg_replace('/('.$k.'=)([^\&]*)/', $k.'='.$v, $url);
                }
                else
                {
                    $connector = (!$nullUrl && !strpos($url, '?')) ? '?' : '&';
                    $url .= $connector.$k.'='.$v;
                }

            }
        }
        return $nullUrl ? __Routing::scriptUrlWithParams( $url, $absolute ) : $url;
    }

	/**
     * @param array $params
     *
     * @return string
     */
	public static function addParamsJS( $params=array() )
	{
		$url = pinax_helpers_Link::addParams( $params, true );
		return 'location.href = \''.$url.'\'';
	}


	/**
     * @param array $params
     * @param null  $url
     *
     * @return string
     */
    public static function removeParams($params=array(), $url = null)
	{
        $nullUrl = is_null( $url );
		if ( $nullUrl )
		{
			$url = __Routing::$queryString;
		}

		if (count($params))
		{
			foreach($params as $v)
			{
                $v = str_replace(array('[', ']'), array('\[', '\]'), $v);
				if (preg_match('/('.$v.'=)([^\&]*)(\&?)/', $url))
				{
					$url = preg_replace('/('.$v.'=)([^\&]*)(\&?)/', '', $url);
				}
			}
		}
		$url = trim($url, '&');
		return $nullUrl ? __Routing::scriptUrlWithParams( $url ) : $url;
	}

	/**
     * @param array $params
     *
     * @return string
     */
	public static function removeParamsJS( $params=array() )
	{
		$url = pinax_helpers_Link::removeParams( $params, true );
		return 'location.href = \''.$url.'\'';
	}

	/**
     * @param $language
     * @param $languageCode
     *
     * @return string
     */
	public static function makeLanguageSwitch($language, $languageCode)
	{
		$scriptUrl = $_SERVER["QUERY_STRING"];
		if (empty($scriptUrl)) $scriptUrl = pinax_Paths::get('PAGE_INDEX').'?'.$languageCode.'/1/home';
		else $scriptUrl = pinax_Paths::get('PAGE_INDEX').'?'.preg_replace('/([^\/]*)(\/.*)$/', $languageCode.'$2', $scriptUrl );
		return pinax_helpers_Link::makeSimpleLink($language, $scriptUrl);
	}



	/**
     * @param bool $removeGetParams
     *
     * @return string
     */
	public static function scriptUrl($removeGetParams=false)
	{
		if ( __Config::get('SEF_URL') == false)
		{
			if (!$removeGetParams) {
				return  pinax_Paths::get('PAGE_INDEX').'?'.$_SERVER["QUERY_STRING"];
			} else {
				list($url) = explode('&', $_SERVER["QUERY_STRING"]);
				return  pinax_Paths::get('PAGE_INDEX').'?'.$url;
			}
		}
		else
		{
			if (!$removeGetParams) {
				return __Request::get('__url__');
			} else {
				list($url) = explode('?', __Request::get('__url__'));
				return $url;
			}
		}
	}

	/**
     * @return string
     */
	public static function getUrlFromRequest()
	{
		$requestValues = __Request::getAllAsArray();
		$url = array();

		foreach ( $requestValues as $k=>$v )
		{
			if ( empty( $v ) ) continue;

			if ( is_array( $v ) )
			{
				for( $i = 0; $i < count( $v ); $i++ )
				{
					$url[] = $k.'[]='.$v[ $i ];
				}
			}
			else
			{
				$url[] = $k.'='.$v;
			}
		}
		return pinax_Paths::get('PAGE_INDEX').'?'.implode( '&', $url );
	}


	/**
     * @param      $text
     * @param bool $absolute
     *
     * @return mixed
     */
	public static function parseInternalLinks($text, $absolute = false )
	{
		$serverUrl = $absolute ? PNX_HOST_ROOT.'/' : '';
		preg_match_all('/<a.*href=["\'](internal:)(\d+)(\?.+)?(#\w+)?["\'].*/Ui', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
            $application = pinax_ObjectValues::get('org.pinax', 'application' );
            $siteMap = $application->getSiteMap();

			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
                $menu = $siteMap->getNodeById($internalLinks[2][$i]);
                $link = $menu->url ? $menu->url : pinax_helpers_Link::makeURL('link', array('pageId' => $internalLinks[2][$i]));

				$originaLink = $internalLinks[0][$i];
                $newLink = str_replace('internal:'.$internalLinks[2][$i], $serverUrl.$link, $originaLink);
				$text = str_replace($originaLink, $newLink, $text);
			}
		}

		preg_match_all('/<a.*href=["\'](glossary\:)(.*)["\'].*/Ui', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
				$link = $serverUrl.pinax_helpers_Link::makeURL('glossary', array('glossarydetail_term2' => '' )).urlencode($internalLinks[2][$i] );
				$originaLink = $internalLinks[0][$i];
				$newLink = str_replace('glossary:'.$internalLinks[2][$i], $link, $originaLink);
				$text = str_replace($originaLink, $newLink, $text);
			}
		}

		preg_match_all('/<a.*href=["\'](media\:)(.*)(\:)(.*)["\'].*/Ui', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
				$link = pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaByIdUrl($internalLinks[2][$i]);
				$originaLink = $internalLinks[0][$i];
				$newLink = str_replace('media:'.$internalLinks[2][$i].":".$internalLinks[4][$i], $link, $originaLink);
				$text = str_replace($originaLink, $newLink, $text);
			}
		}

		preg_match_all('/<a[^>]+href=["\'](?!https?:\/\/)([\w-]+):([0-9]+)["\']/i', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
			$speakingUrlManager = __ObjectFactory::createObject('pinaxcms.speakingUrl.Manager');
			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
				$module = $internalLinks[1][$i] . ':';
				$recordID = $internalLinks[2][$i];
				$url = $speakingUrlManager->makeUrl($module . $recordID);

				$originalLink = $internalLinks[0][$i];
				$newLink = str_replace($module . $recordID, $url, $originalLink);
				$text = str_replace($originalLink, $newLink, $text);
			}
		}

		preg_match_all('/{{route:(.*)}}/Ui', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
				$link = __Link::makeUrl($internalLinks[1][$i]);
				$text = str_replace($internalLinks[0][$i], $link, $text);
			}
		}
		preg_match_all('/{{config:(.*)}}/Ui', $text, $internalLinks);
		if (count($internalLinks) && count($internalLinks[0]))
		{
			for ($i=0; $i<count($internalLinks[0]); $i++)
			{
				$link = __Config::get($internalLinks[1][$i]);
				$text = str_replace($internalLinks[0][$i], $link, $text);
			}
		}

		// non è il massimo ma la regexp su testi lunghi crasha
		$text = str_replace( array( 'href="#', 'href=\'#', '<p></p>' ), array( 'href="'.__Routing::scriptUrl().'#', 'href=\''.__Routing::scriptUrl().'#', '<p>&nbsp;</p>'), $text );
		// $text = preg_replace("/<(.*?)(href)\s*=\s*(\'|\")#(.*?)(\'|\")(.*?)>/si", "<$1$2=$3".__Routing::scriptUrl()."#$4$5$6>", $text);

        	$text = self::formatImagesInText($text, $absolute);

		return $text;
	}

	/**
     * @param        $link
     * @param null   $label
     * @param null   $content
     * @param string $cssClass
     *
     * @return mixed|string
     */
	public static function formatLink($link, $label=NULL, $content=NULL, $cssClass='')
	{
        if (is_null($label)) {
            $label = $link;
        }
        if (is_null($content)) {
            $content = $label;
        }
        if ($cssClass) {
            $cssClass = ' class="' . $cssClass . '"';
        }

		$initialProtocol = 'http://';
		if (preg_match('/^https/', $link, $matches)) {
			$initialProtocol = 'https://';
		}

		$link = preg_replace("/^(http|https):\/\//", "", $link);
		$link = preg_replace('/&(?!amp;)/i', '&amp;', $link);
		$protocol = ((strpos($link, "index.php")==0 && strpos($link, "index.php")!==false) || (strpos($link, "?")==0 && strpos($link, "?")!==false)) ? "" : $initialProtocol;
		$target = ((strpos($link, "index.php")==0 && strpos($link, "index.php")!==false) || (strpos($link, "?")==0 && strpos($link, "?")!==false)) ? "internal":"external";


		$link = '<a href="'.$protocol.$link.'" rel="'.$target.'" title="'.$label.'"'.$cssClass.'>'.$content.'</a>';
		return $link;
	}

	/**
     * @param        $link
     * @param null   $label
     * @param null   $content
     * @param string $cssClass
     * @param string $target
     *
     * @return string
     */
	public static function formatInternalLink($link, $label=NULL, $content=NULL, $cssClass='', $target='internal')
	{
      	if (is_null($label)) {
            $label = $link;
        }
        if (is_null($content)) {
            $content = $label;
        }
        if (!is_null($cssClass)) {
            $cssClass = ' class="' . $cssClass . '"';
        }

		$link = '<a href="'.$link.'" rel="'.$target.'" title="'.$label.'"'.$cssClass.'>'.$content.'</a>';
		return $link;
	}

    /**
     * @param  string  $text
     * @param  boolean $absolute
     * @return string
     */
    public static function formatImagesInText($text, $absolute = false)
    {
        $serverUrl = $absolute ? PNX_HOST_ROOT.'/' : '';
        preg_match_all('/<img[^>]*\ssrc="getImage\.php\?id=([^&]*)(&[^>]*)?"[^>]*\sdata-zoom="1"[^>]*\s\/>/Ui', $text, $match);

        $isImgWxHZoomDefined = false;
        if (__Config::get( 'IMG_WIDTH_ZOOM' ) && __Config::get( 'IMG_HEIGHT_ZOOM' )) {
            $isImgWxHZoomDefined = true;
        }

        $numMatch = count($match[0]);
        if (count($match) && $numMatch) {
            for($i=0; $i<$numMatch; $i++) {
                preg_match_all('/(title|data-caption)="([^"]*)"/Ui', $match[0][$i], $matchTitle);
                $title = array(
                    $matchTitle[1][0] => $matchTitle[2][0],
                    $matchTitle[1][1] => $matchTitle[2][1]
                );

                $attributes = array();
                $attributes['title'] = $title['data-caption'] ? $title['data-caption'] : $title['title'];
                $attributes['class'] = 'js-lightbox-image';
                $attributes['href'] = $isImgWxHZoomDefined ? pinaxcms_helpers_Media::getImageUrlById($match[1][$i], __Config::get( 'IMG_WIDTH_ZOOM' ), __Config::get( 'IMG_HEIGHT_ZOOM' )) : pinaxcms_helpers_Media::getUrlById($match[1][$i]);
                $attributes['data-type'] = 'image';
                $attributes['rel'] = 'lightbox';
                $html = pinax_helpers_Html::renderTag( 'a', $attributes, true, $match[0][$i]);
                $text = str_replace($match[0][$i], $html, $text);
            }
        }

        if ($absolute) {
            $text = str_replace( 'src="getImage', 'src="'.$serverUrl.'getImage', $text);
            $text = str_replace( 'src="cache/', 'src="'.$serverUrl.'cache/', $text);
        }

        return $text;
    }

	/**
	 * @param string $routeUrl
	 * @param string $iconName
	 * @param array $params
	 * @param string $deleteMsg
	 * @param array $addParam
	 * @return string
	 */
	public static function makeLinkWithIcon($routeUrl, $iconName, $params, $deleteMsg=NULL, $addParam=array())
	{
		$params['icon'] = $iconName;
		$params['label'] = '';
		$deleteJs = '';
		if ( !is_null( $deleteMsg ) )
		{
			$deleteJs = 'if (!confirm(\''.addslashes( $deleteMsg ).'\')){return false;}';
		}
		return self::makeLink( $routeUrl, $params, $addParam, $deleteJs, false );
	}
}

