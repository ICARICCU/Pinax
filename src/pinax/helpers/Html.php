<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_Html extends PinaxObject
{
	/**
     * @param       $name
     * @param       $value
     * @param array $attributes
     *
     * @return string
     */
    public static function hidden($name, $value, $attributes=array(), $data=array())
	{
		$ouput = '<input name="'.$name.'" id="'.$name.'" type="hidden" value="'.$value.'"'.pinax_helpers_Html::renderAttributes($attributes, $data).'/>';
		return $ouput;
	}

	/**
     * @return string
     */
	public static function requestToHiddenFields()
	{
		$ouput = '';
		$requestValues = __Request::getAllAsArray();
		foreach ( $requestValues as $k=>$v )
		{
			if ( empty( $v ) ) continue;

			if ( is_array( $v ) )
			{
				for( $i = 0; $i < count( $v ); $i++ )
				{
					$ouput .= '<input name="'.$k.'[]" type="hidden" value="'.$v[ $i ].'" />';
				}
			}
			else
			{
				$ouput .= '<input name="'.$k.'" type="hidden" value="'.$v.'" />';
			}
		}

		return $ouput;
	}

	/**
     * @param        $label
     * @param string $for
     * @param bool   $wrap
     * @param string $content
     * @param array  $attributes
     * @param bool   $addBr
     *
     * @return string
     */
    public static function label($label, $for='', $wrap=false, $content='', $attributes=array(), $addBr=false)
	{
		if ( is_null( $label ) )
		{
			$ouput = $content;
		}
		else
		{
			$ouput  = '<label for="'.$for.'" '.pinax_helpers_Html::renderAttributes($attributes).'>'.$label.(!$wrap ? '</label>' : '');
			$ouput .= $content;
			$ouput .= $wrap ? '</label>' : '';
		}
		if ($addBr) $ouput .= '<br />';
		return $ouput;
	}

	/**
     * @param array $attributes
     * @param string $dataAttributes
     *
     * @return string
     */
    public static function renderAttributes($attributes=[], $dataAttributes = null, $emptyAttributes = [])
	{
		if ( $dataAttributes )
		{
			$dataAttributes = explode( ';', $dataAttributes );
			foreach ($dataAttributes as $value)
			{
				list( $name, $value ) = explode( '=', $value );
				$attributes[ 'data-'.$name ] = htmlentities($value);
			}
		}
		$output = '';
		foreach ($attributes as $k=>$v)
		{
			if ($v !== '' || $k == 'value' || in_array($k, $emptyAttributes))
			{
				if ($k == 'href' || $k == 'src')
				{
					$v = htmlspecialchars($v);
				}
				else if ($k == 'id')
				{
					$v = str_replace('@', '___', $v);
				}
				$output .= ' '.$k.'="'.$v.'"';
			}
		}
		return $output;
	}

	/**
     * @param       $tag
     * @param array $attributes
     * @param bool  $close
     * @param null  $content
     *
     * @return string
     */
    public static function renderTag($tag, $attributes=array(), $close=true, $content=NULL)
	{
		$output  = '<'.$tag;
        if (isset($attributes['dataAttributes'])) {
            $dataAttributes = $attributes['dataAttributes'];
            unset($attributes['dataAttributes']);
        } else {
            $dataAttributes = "";
        }
        $output .= pinax_helpers_Html::renderAttributes($attributes, $dataAttributes);

		if (is_null($content))
		{
			$output .= $close ? '/>' : '>';
		}
		else
		{
			$output .= '>'.$content.'</'.$tag.'>';
		}
		return $output;
	}

	/**
     * @param $tag
     *
     * @return string
     */
	public static function closeTag($tag)
	{
		return '</'.$tag.'>';
	}

	/**
     * @param      $output
     * @param      $hidden
     * @param bool $rightLabel
     * @param null $cssClass
     *
     * @return string
     */
	public static function applyItemTemplate($label, $element, $hidden, $rightLabel=false, $cssClass=NULL )
	{
		if ( $hidden )
		{
			$template = __Config::get('FORM_ITEM_HIDEN_TEMPLATE');
		}
		else if ( $rightLabel )
		{
			$template = __Config::get('FORM_ITEM_RIGHT_LABEL_TEMPLATE');
		}
		else
		{
			$template = __Config::get('FORM_ITEM_TEMPLATE');
		}
		// TODO: verificare
		// if ( !empty( $cssClass ) )
		// {
		// 	$template = preg_replace( '/(<div\s*)(class=")/i', '$1class="'.$cssClass.' ', $template ) ;
		// }

		return str_replace(array('##FORM_LABEL##','##FORM_ITEM##'), array($label, $element), $template);
	}

    /**
     * @param $text
     *
     * @return string
     */
	public static function forceP($text)
	{
		$output = '';
		$allowTag = array('strong', 'em', 'br', 'a', 'img', 'hr');
		$allowAutocloseTag = array('img', 'br', 'hr');
		preg_match_all('/\<([^>]*)\>/is', $text, $matches);
		if (count($matches[0]))
		{
			$open = false;
			$numMatches = count($matches[0]);
			$stack = array();

			for($i=0; $i<$numMatches; $i++)
			{
				$pos = strpos($text, $matches[0][$i]);
				if ($pos!==false)
				{
					$tag = preg_replace('/\<(\w*)(.*)/i', '$1', $matches[0][$i]);
					if ($pos!=0)
					{
						$part = substr($text, 0, $pos);
						$trimmed = trim($part);
						if ($open===false && !empty($trimmed))
						{
							$output .= '<p>'.$part;
							if (in_array($tag, $allowTag))
							{
								array_unshift($stack, '</p>');
							}
							else
							{
								$output .= '</p>';
							}
						}
						else
						{
							$output .= $part;
						}
					}
					else
					{
						if ($open===false)
						{
							if (in_array($tag, $allowTag))
							{
								$output .= '<p>';
								array_unshift($stack, '</p>');
							}
						}
					}

					$closeTag = '</'.$tag.'>';
					$outputText = substr($text, $pos, strlen($matches[0][$i]));

					if (strpos($matches[0][$i], '/>')===false)
					{
						if ($stack[0]==$matches[0][$i])
						{
							array_shift($stack);
							$open = count($stack)>0;
						}
						else if (strpos($matches[0][$i], '</')===false)
						{
							array_unshift($stack, $closeTag);
							$open = count($stack)>0;
						}
					}
					else
					{
						// tag with autoclose
						if (!in_array($tag, $allowAutocloseTag))
						{
							$outputText = preg_replace('/\s?\/>$/i', '>'.$closeTag, $outputText);

						}
						$open = count($stack)>0;
					}
					$output .= $outputText;
					$text = substr($text, $pos+strlen($matches[0][$i]));
				}
			}
			if (count($stack))
			{
				if (!empty($text))
				{
					$output .= $text;
					$text = '';
				}
				for ($i=0; $i<count($stack); $i++)
				{
					$output .= $stack[$i];
				}
			}

		}

		if (!empty($text)) $output .= '<p>'.$text.'</p>';
		unset ($stack);
		unset ($matches);
		return $output;
	}

    /**
     * @param        $label
     * @param string $tag
     *
     * @return string
     */
	public static function renderPageTitle( $label, $tag='h2' )
	{
		return !empty( $label ) ? '<'.$tag.'>'.$label.'</'.$tag.'>' : '';
	}
}

