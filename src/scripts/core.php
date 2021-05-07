<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


define('PNX_CORE_VERSION', '3.0.1');


// directory contente tutti i file del core
define('PNX_LOADED', true);
define('PNX_CORE_DIR', realpath(dirname(__FILE__)).'/');

// directory contente tutti i file del core
define('PNX_CLASSES_DIR', realpath(dirname(__FILE__)).'/classes/');

// directory contentente le librerie
define('PNX_LIBS_DIR', realpath(dirname(__FILE__)).'/libs/');

define('PNX_COMPILER_NEWLINE', ";\n");
define('PNX_COMPILER_NEWLINE2', "\n");

define('PNX_SCRIPNAME', $_SERVER['PHP_SELF']);
define('PNX_ERR_EMPTY_APP_PATH', 'The application path can\'t be empty');

// eventi
define('PNX_EVT_BEFORE_CREATE_PAGE', 'beforeCreatePage');
define('PNX_EVT_START_PROCESS', 'onProcessStart');
define('PNX_EVT_END_PROCESS', 'onProcessEnd');
define('PNX_EVT_START_RENDER', 'onRenderStart');
define('PNX_EVT_END_RENDER', 'onRenderEnd');
define('PNX_EVT_CALL_CONTROLLER', 'onCallController');
define('PNX_EVT_START_COMPILE_ROUTING', 'startCompileRouting');
define('PNX_EVT_LISTENER_COMPILE_ROUTING', 'listenerCompileRouting');
define('PNX_EVT_USERLOGIN', 'login');
define('PNX_EVT_USERLOGOUT', 'onLogout');
define('PNX_EVT_AR_UPDATE', 'update');
define('PNX_EVT_AR_UPDATE_PRE', 'preUpdate');
define('PNX_EVT_AR_INSERT', 'insert');
define('PNX_EVT_AR_INSERT_PRE', 'preInsert');
define('PNX_EVT_AR_DELETE', 'delete');
define('PNX_EVT_SITEMAP_UPDATE', 'siteMapUpdate');
define('PNX_EVT_BREADCRUMBS_ADD', 'onBreadcrumbsAdd');
define('PNX_EVT_BREADCRUMBS_UPDATE', 'onBreadcrumbsUpdate');
define('PNX_EVT_PAGETITLE_UPDATE', 'onPageTitleUpdate');
define('PNX_EVT_CACHE_CLEAN', 'cacheClean');
define('PNX_EVT_DUMP_EXCEPTION', 'onDumpException');
define('PNX_EVT_DUMP_404', 'onDump404');

define('PNX_REQUEST_ALL', 0);
define('PNX_REQUEST_GET', 1);
define('PNX_REQUEST_POST', 2);
define('PNX_REQUEST_ROUTING', 3);
define('PNX_REQUEST_AUTH', 4);
define('PNX_REQUEST_VALUE', 0);
define('PNX_REQUEST_TYPE', 1);

define('PNX_SESSION_EX_VOLATILE', 1);
define('PNX_SESSION_EX_PERSISTENT', 2);
define('PNX_SESSION_EX_PREFIX', 'session_ex');

if (!defined('PNX_LOG_EVENT')) 		define('PNX_LOG_EVENT', 'logByEvent');
if (!defined('PNX_LOG_DEBUG')) 		define('PNX_LOG_DEBUG', 1);
if (!defined('PNX_LOG_SYSTEM')) 	define('PNX_LOG_SYSTEM', 2);
if (!defined('PNX_LOG_INFO')) 		define('PNX_LOG_INFO', 4);
if (!defined('PNX_LOG_WARNING')) 	define('PNX_LOG_WARNING', 8);
if (!defined('PNX_LOG_ERROR')) 		define('PNX_LOG_ERROR', 16);
if (!defined('PNX_LOG_FATAL')) 		define('PNX_LOG_FATAL', 32);
if (!defined('PNX_LOG_ALL')) 		define('PNX_LOG_ALL', 255);

if (!defined('E_STRICT')) define('E_STRICT', 2048);
if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);


date_default_timezone_set( 'Europe/Rome' );

$errorlevel=error_reporting();
error_reporting( $errorlevel & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT );


/**
 * @return string
 */
function pinax_charset()
{
	return defined('PNX_CHARSET') ? PNX_CHARSET : 'utf-8';
}

/**
 * @return void
 */
function pinax_defineBaseHost()
{
	if ( !defined( 'PNX_HOST' ) )
	{
		$host = __Config::get( 'PNX_HOST', '' );
		if ( !$host )
		{
			if (isset($_SERVER['HTTP_HOST'])) {
				$protocol = @$_SERVER["HTTPS"] === true || @$_SERVER["HTTPS"] === 'on' ? 'https://' : 'http://';
				$host = $protocol.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
				$host = substr( $host, 0, strrpos( $host, '/' ) );
			} else {
				$host = 'console://';
			}
        }

        __Config::set( 'PNX_HOST', $host );
		define('PNX_HOST', $host);
	} else {
        $host = PNX_HOST;
    }

	if ( !defined( 'PNX_HOST_ROOT' ) )
	{
		define('PNX_HOST_ROOT', str_replace( '/admin', '', $host ) );
	}
}

/**
 * @param string $dir
 * @param string $default
 *
 * @return void
 */
function pinax_require_once_dir($dir, $default='')
{
	$dir = rtrim($dir, "*");
	$retArray = null;
	if ($default!='')
	{
		if (!is_array($default))
		{
			$default = array($default);
		}

		foreach ($default as $value)
		{
			$retArray[] = "$dir/$value";
			require_once("$dir/$value");
		}
	}

	if ($dir_handle = @opendir($dir))
	{
		while ($file_name = readdir($dir_handle))
		{
			if ($file_name!="." &&
				$file_name!=".." &&
				$file_name!=$default &&
				!is_dir("$dir/$file_name") &&
				substr($file_name, -3)=='php' &&
				strpos($file_name, '._') === false )
			{
				require_once("$dir/$file_name");
			}
		}
		closedir($dir_handle);
	}
	else
	{
		echo "Could not open directory $dir";
	}
}

/**
 * @param array $path
 * @param string $classPath
 * @return string
 */
function pinax_resolvePsr4Path($path, $classPath)
{
	$pos = $path['psr-4'] ? strpos($classPath, str_replace('\\', '/', $path['psr-4'])) : false;
	return $path['path'].($pos===0 ? substr($classPath, strlen($path['psr-4'])) : $classPath);
}

/**
 * @param        $classPath
 * @param array  $classToReadFirst
 * @param string $path
 *
 * @return bool
 */
function pinax_import($classPath, $classToReadFirst=array(), $path='')
{
	static $loadedClass = array();
	$classPath 			= str_replace(['.', '\\'], '/', $classPath);
	$classPath 			= rtrim($classPath, '*');
	$origClassPath 		= $classPath;

	if (in_array($classPath, $loadedClass)) return true;

	if (empty($path))
	{
		if (class_exists('pinax_Paths') && !is_null(pinax_Paths::get('APPLICATION_CLASSES')))
		{
			$path = NULL;
			$searchPath = pinax_Paths::getClassSearchPath();
			foreach($searchPath as $p)
			{
				$fileToCheck = pinax_resolvePsr4Path($p, $classPath);
				if (file_exists($fileToCheck) || file_exists($fileToCheck.'.php')) {
					$path = $p['path'];
					break;
				}
			}
		}
		else
		{
			$path = realpath(dirname(__FILE__)).'/classes/';
		}
	}

	if (substr($classPath, -1, 1)=='/' || $classPath=='')
	{
		// import all file in the folder
		$classPath = rtrim($classPath, '/');
		pinax_require_once_dir($path.$classPath, $classToReadFirst);
		pinax_loadLocale( $classPath );
	}
	else
	{
		// import a single file
		if (file_exists($path.$classPath.'.php'))
		{
			require_once($path.$classPath.'.php');
		}
		else
		{
			return false;
		}
	}

	$loadedClass[] = $origClassPath;
	return true;
}

/**
 * @param string $classPath
 *
 * @return void
 */
function pinax_loadLocale( $classPath )
{
	if (!class_exists('pinax_ObjectValues')) {
		return;
	}

    /** @var pinax_application_Application $application */
	$application = &pinax_ObjectValues::get('org.pinax', 'application');
	if (is_object($application) ) {
		$application->loadModuleLocale($classPath);
	}
}

/**
 * @param string $path
 * @param string $language
 *
 * @return bool
 */
function pinax_loadLocaleReal( $path, $language )
{
	$pathLang = $path.'/locale/'.$language.'.php';
	$pathEn = $path.'/locale/en.php';
	if ( file_exists($pathLang) ) {
		require( $pathLang );
		return true;
	} else if ( file_exists($pathEn) ) {
		require( $pathEn );
		return true;
	}

	return false;
}



/**
 * @param string $classPath
 * @param boolean $dotPaths
 * @param boolean $onlyFile
 *
 * @return null|string
 */
function pinax_findClassPath($classPath, $dotPaths=true, $onlyFile=false)
{
	if (!class_exists('pinax_Paths')) return NULL;
	$extensionsToCheck = ['', '.xml', '.php'];
	$classPath = $dotPaths ? str_replace(['.', '*', '\\'], '/', $classPath) :
							 str_replace(['\\'], '/', $classPath);

	$path = NULL;
	$searchPath = pinax_Paths::getClassSearchPath();
	foreach($searchPath as $p) {
		$baseFileToCheck = pinax_resolvePsr4Path($p, $classPath);
		foreach ($extensionsToCheck as $value) {
			$fileToCheck = $baseFileToCheck.$value;
			if (file_exists($fileToCheck) && (!$onlyFile || ($onlyFile && !is_dir($fileToCheck)))) {
				return $fileToCheck;
			}
		}
	}
	return $path;
}

/**
 * @param $path
 *
 * @return null
 */
function pinax_importLib($path)
{
	if (!class_exists('pinax_Paths')) return NULL;
	require_once(pinax_Paths::get('CORE_LIBS').$path);
}

/**
 * @param $path
 *
 * @return null
 */
function pinax_importApplicationLib($path)
{
	if (!class_exists('pinax_Paths')) return NULL;
	require_once(pinax_Paths::get('APPLICATION_LIBS').$path);
}

/**
 * @param $output
 *
 * @return mixed
 */
function pinax_encodeOutput($output)
{
	if (!$output) return $output;
	if (is_array($output))
	{
		return pinax_encodeOutputArray($output);
	}
	else return pinax_htmlentities($output);
}

/**
 * @param $output
 *
 * @return mixed
 */
function pinax_encodeOutputArray($output)
{
	$keys = array_keys($output);
	$count = count($output);
	for ($i = 0; $i < $count; $i++)
	{
		if (is_array($output[$keys[$i]]))
		{
			$output[$keys[$i]] = pinax_encodeOutputArray($output[$keys[$i]]);
		}
		else
		{
			$output[$keys[$i]] = pinax_htmlentities($output[$keys[$i]]);
		}
	}
	return $output;
}

/**
 * @param $text
 *
 * @return mixed
 */
function pinax_htmlentities( $text )
{
	if (!$text) return $text;
	$charset = pinax_charset();
	$tempText = @htmlentities( $text, ENT_COMPAT | ENT_SUBSTITUTE , $charset );
	if (!$tempText) {
		$tempText = @htmlentities( $text, ENT_COMPAT);
	}

	if ($tempText) {
		$text = $tempText;
	}

	return str_replace('&amp;#', '&#', $text);
}

/**
 * @param $psw
 *
 * @return string
 */
function pinax_password($psw)
{
	switch (__Config::get('PSW_METHOD'))
	{
		case 'MD5':
			return md5($psw);
        case 'SHA1':
            return sha1($psw);
        case 'SHA1OFMD5':
			return sha1(md5($psw));
		case 'SHA256':
			return hash('sha256', $psw);
		default:
			return $psw;
	}
}

/**
 * @param $name
 *
 * @return mixed
 */
function pinax_basename($name)
{
	return preg_replace('/.php/', '', basename($name));
}


/**
 * @param $value
 *
 * @return bool
 */
function pinax_empty($value)
{
	if ( strpos( $value, '<img') !== false ) return false;
	$value = is_string($value) ? strip_tags($value) : $value;
	return empty($value);
}
/**
 * @param $value
 *
 * @return mixed
 */
function pinax_localeDate2ISO( $value )
{
	if (!is_string($value)) return $value;
	$type = strlen( $value ) <= 10 ? 'date' : 'datetime';
	$reg = __T( $type == 'date' ? 'PNX_DATE_TOISO_REGEXP' : 'PNX_DATETIME_TOTIME_REGEXP' );
	if ( is_array( $reg ) && preg_match( $reg[0], $value ) )
	{
		$value = preg_replace( $reg[0], $reg[1], $value );
	}
	return $value;
}

/**
 * @param $value
 *
 * @return mixed
 */
function pinax_localeDate2default( $value )
{
	if (!is_string($value)) return $value;
	$type = strlen( $value ) <= 10 ? 'date' : 'datetime';
	$reg = __T( $type == 'date' ? 'PNX_DATE_TOTIME_REGEXP' : 'PNX_DATETIME_TOTIME_REGEXP' );
	if ( is_array( $reg ) && preg_match( $reg[0], $value ) )
	{
		$value = preg_replace( $reg[0], $reg[1], $value );
	}
	return $value;
}

/**
 * @param string $format
 * @param $value
 *
 * @return bool|string
 */
function pinax_defaultDate2locale( $format, $value )
{
    list( $d, $t ) = explode( ' ', $value . ' ');
	list( $y, $m, $day ) = explode( '-', $d );
    if (!$t) $t = '00:00:00';
	list( $hh, $mm, $ss ) = explode( ':', $t );
	return date( $format, mktime( intval( $hh ), intval( $mm ), intval( $ss ), intval( $m ), intval( $day ), intval( $y ) ) );
}


/*
	code borrowed By Stewart Rosenberger
	http://www.stewartspeak.com/headings/
	convert embedded, javascript unicode characters into embedded HTML
	entities. (e.g. '%u2018' => '&#8216;'). returns the converted string.
*/

/*
APL Quote	"	&quot;	&#34;	&#x22;
Double Quote (left) 	“	&ldquo;	&#8220;	&#x201C;
Double Quote (right)	”	&rdquo;	&#8221;	&#x201D;
Single Quote (left)	‘	&lsquo;	&#8216;	&#x2018;
Single Quote (right)	’	&rsquo;	&#8217;	&#x2019;
Prime	'	&prime;	&#8242;	&#x2032;
Double Prime	?	&Prime;	&#8243;	&#x2033;
Em Dash		&mdash;	&#8212;	&#x2013;
En Dash	–	&ndash;	&#8211;	&#x2013;
Minus	-	&minus;	&#8722;	&#x2212;
Multiplication Symbol	×	&times;	&#215;	&#xD7;
Division Symbol	÷	&divide;	&#247;	&#xF7;
Ellipsis	…	&hellip;	&#8230;	&#x2026;
Copyright Symbol	©	&copy;	&#169;	&#xA9;
Trademark	™	&trade;	&#8482;	&#x2122;
Registered Trademark	®	&reg;	&#174;	&#xAE;

*/

/**
 * @param string $text
 *
 * @return string mixed
 */
function javascript_to_html($text)
{
	$matches = null ;

	preg_match_all('/%u([0-9A-F]{4})/i',$text,$matches) ;
	if(!empty($matches))
	{
		$convTable = array(
							'2026' => "…",
							'201C' => "“",
							'201D' => "”",
							'2018' => "‘",
							'2019' => "’",
							'2032' => "'",
							'2013' => "—",
							'2212' => "–"
						);
		for($i=0;$i<sizeof($matches[0]);$i++)
		{
			if (isset($convTable[$matches[1][$i]]))
			{
				$text = str_replace($matches[0][$i], $convTable[$matches[1][$i]], $text);
			}
			else
			{
				$text = str_replace($matches[0][$i], '&#'.hexdec($matches[1][$i]).';',$text);
			}
		}
	}

	preg_match_all('/\&#([0-9A-F]{4});/i',$text,$matches) ;
	if(!empty($matches))
	{
		$convTable = array(
							'8230' => "…",
							'8220' => "“",
							'8221' => "”",
							'8216' => "‘",
							'8217' => "’",
							'8242' => "'",
							'8211' => "—",
							'8212' => "-",
							'8722' => "–",
							'8364' => "€"
						);
		for($i=0;$i<sizeof($matches[0]);$i++)
		{
			if (isset($convTable[$matches[1][$i]]))
			{
				$text = str_replace($matches[0][$i], $convTable[$matches[1][$i]], $text);
			}
		}
	}
	return $text;
}

/**
 * @param int $len
 *
 * @return string
 */
function pinax_makePass( $len = 7)
{
	$pass = "";
	$salt = "abchefghjkmnpqrstuvwxyz0123456789";
	srand((double)microtime()*1000000);
	$i = 0;
	while ($i <= $len )
	{
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$pass = $pass . $tmp;
		$i++;
	}
	return $pass;
}

/**
 * @param int  $len
 * @param null $id
 *
 * @return string
 */
function pinax_makeConfirmCode( $len=7, $id=NULL )
{
       $convTable = array( 0 => "f", 1 => "x", 2 => "r", 3 => "i", 4 => "d",5 => "g", 6 => "n", 7 => "k", 8 => "h", 9 => "o" );

       // creazione password normale
       $code = pinax_makePass( $len );
       // codifica di cesare per id
       $s_id = sprintf("%d",$id);
       for( $i=0; $i<strlen($id); $i++ )
       {
               // accodiamo la conversione dell'id al codice
               $code .= $convTable[ $s_id[$i] ];
       }
       return $code;
}

/**
 * @param        $str
 * @param int    $maxlen
 * @param string $elli
 * @param int    $maxoverflow
 *
 * @return string
 */
function pinax_strtrim($str, $maxlen=200, $elli='...', $maxoverflow=15)
{
	$str = trim(html_entity_decode(strip_tags($str), ENT_NOQUOTES, 'UTF-8'));
	$originalStrLen = strlen($str);
	if ( $originalStrLen <= $maxlen) {
		return $str;
	}

	$output = '';
	$body = explode(" ", $str);
	$body_count = count($body);

	$i=0;
	do {
		$output .= $body[$i]." ";
		$thisLen = strlen($output);
		$cycle = ($thisLen < $maxlen && $i < $body_count-1 && ($thisLen+strlen($body[$i+1])) < $maxlen+$maxoverflow?true:false);
		$i++;
	} while ($cycle);

	if ($originalStrLen > strlen($output)-1) {
		$output .= $elli;
	}
	return $output;
}

/**
 * https://stackoverflow.com/questions/1193500/truncate-text-containing-html-ignoring-tags
 * @param  string  $html
 * @param  integer $maxLength
 * @param  boolean $isUtf8
 * @param  string $elli
 * @return string
 */
function pinax_htmlTrim($html, $maxLength = 300, $isUtf8 = true, $elli='...')
{
	$trimmed = '';
	$printedLength = 0;
	$position = 0;
	$tags = array();

	// For UTF-8, we need to count multibyte sequences as one character.
	$re = $isUtf8
		? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
		: '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

	while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
	{
		list($tag, $tagPosition) = $match[0];

		// Print text leading up to the tag.
		$str = substr($html, $position, $tagPosition - $position);
		if ($printedLength + strlen($str) > $maxLength)
		{
			$trimmed .= substr($str, 0, $maxLength - $printedLength);
			$printedLength = $maxLength;
			break;
		}

		$trimmed .= $str;
		$printedLength += strlen($str);
		if ($printedLength >= $maxLength) break;

		if ($tag[0] == '&' || ord($tag) >= 0x80)
		{
			// Pass the entity or UTF-8 multibyte sequence through unchanged.
			$trimmed .= $tag;
			$printedLength++;
		}
		else
		{
			// Handle the tag.
			$tagName = $match[1][0];
			if ($tag[1] == '/')
			{
				// This is a closing tag.

				$openingTag = array_pop($tags);
				assert($openingTag == $tagName); // check that tags are properly nested.

				$trimmed .= $tag;
			}
			else if ($tag[strlen($tag) - 2] == '/')
			{
				// Self-closing tag.
				$trimmed .= $tag;
			}
			else
			{
				// Opening tag.
				$trimmed .= $tag;
				$tags[] = $tagName;
			}
		}

		// Continue after the tag.
		$position = $tagPosition + strlen($tag);
	}

	// Print any remaining text.
	if ($printedLength < $maxLength && $position < strlen($html))
		$trimmed .= substr($html, $position, $maxLength - $printedLength);

	// Close any open tags.
	while (!empty($tags))
		$trimmed .= sprintf(count($tags) == 1 ? $elli : '' . '</%s>', array_pop($tags));

	return $trimmed;
}

/**
 * @return string
 */
function pinax_hostName()
{
	return PNX_HOST.'/';
}

/**
 * @param $string
 *
 * @return string
 */
function pinax_htmlWithUnicodeToUtf8( $string )
{
	return utf8_encode( pinax_htmlWithUnicodeDecode( $string ) );
}

/**
 * @param $string
 *
 * @return string
 */
function pinax_htmlWithUnicodeDecode( $string )
{
	return html_entity_decode( preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $string ), ENT_NOQUOTES, 'UTF-8');
}

/**
 * @param $title
 *
 * @return string
 */
function pinax_sanitizeUrlTitle($title, $force=false) {
	if (!$title) return $title;
	if ( __Config::get( 'SANITIZE_URL' ) || $force )
	{
		$title = pinax_slugify($title, true);
	} else {
		$title = str_replace(' ', '%20', $title);
	}

	return $title;
}

/**
 * http://stackoverflow.com/questions/10152894/php-replacing-special-characters-like-%C3%A0-a-%C3%A8-e
 * @param $title
 * @param $strict
 *
 * @return string
 */
function pinax_slugify($text, $strict = false) {
	if ($text) {
	    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	    // replace non letter or digits by -
	    $text = preg_replace('~[^\\pL\d.]+~u', '-', $text);

	    // trim
	    $text = trim($text, '-');
	    setlocale(LC_CTYPE, 'en_GB.utf8');
	    // transliterate
	    if (function_exists('iconv')) {
	       $transliterateText = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	       if ($transliterateText) {
	       	// in some *nix iconv//TRANSLIT can fail
	       	$text = $transliterateText;
	       }
	    }

	    // lowercase
	    $text = strtolower($text);
	    // remove unwanted characters
	    $text = preg_replace('~[^-\w.]+~', '', $text);
	    if (empty($text)) {
	       return 'empty_$';
	    }
	    if ($strict) {
	        $text = str_replace(".", "_", $text);
	    }
	}
    return $text;
}

/**
 * @param string $text
 * @param bool $html
 *
 * @return string
 */
function pinax_stringToJs($text, $html=false) {
	if ($html) {
		$text = str_replace(array("\n","\r"), '', $text);
	}
	$text = addslashes($text);
	return $text;
}

/**
 * @return void
 */
function pinax_closeApp()
{
    PinaxClassLoader::unregister();
    PinaxErrorHandler::unregister();
}

/**
 * @param string $string
 * @param boolean $inArray
 *
 * @return array|object|string
 */
function pinax_maybeJsonDecode($string, $inArray) {
	$result = $string;
	if (is_string($string)) {
   		$json = json_decode($string, $inArray);
   		if ((is_object($json) || is_array($json)) && json_last_error() === JSON_ERROR_NONE) {
   			$result = $json;
   		}
   	}
   	return $result;
}

/**
 * @param  string $classNameSpace
 * @return string
 */
function pinax_classNSToClassName($classNameSpace)
{
    return str_replace('.', '_', $classNameSpace);
}

/**
 * @param  string  $filename
 * @param  integer $nestLevel
 * @param  string  $path
 * @param  string  $prefix
 * @return string
 */
function pinax_nestedCachePath($filename, $nestLevel=3, $path='', $prefix='')
{
    $nestLevel = max(intval($nestLevel), 0);

    if ($nestLevel>0) {
        $hash = md5($filename);
        for ($i=0 ; $i<$nestLevel; $i++) {
            $path = $path.$prefix.substr($hash, 0, $i + 1) . '/';
        }
    }

    return $path;
}



if ( !function_exists( "dd" ) )
{
	/**
	 * @param mixed $var
	 *
	 * @return void
	 */
	function dd($var)
	{
		array_map(function ($x) { var_dump($x); }, func_get_args());
		die;
	}
}

if ( !function_exists( "stripos" ) )
{
    /**
     * @param $str
     * @param $needle
     *
     * @return int
     */
	function stripos($str,$needle)
	{
		return strpos(strtolower($str),strtolower($needle));
	}
}
