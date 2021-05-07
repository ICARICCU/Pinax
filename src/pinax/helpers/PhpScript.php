<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_helpers_PhpScript
{
	/**
	 * @param string $phpcode
	 * @return string
	 */
	public static function parse($phpcode)
	{
		$phpcode = preg_replace("/\{php\:(.*)\}/i", "$1", $phpcode);
		$phpcode = preg_replace('/(\$\w+)[.]/', '$1->',	$phpcode);
		$phpcode = str_replace('->->',			'.',	$phpcode);
		$phpcode = preg_replace('/\bnot\b/i', 	' !',	$phpcode);
		$phpcode = preg_replace('/\bne\b/i', 	' != ',	$phpcode);
		$phpcode = preg_replace('/\band\b/i', 	' && ',	$phpcode);
		$phpcode = preg_replace('/\bor\b/i', 	' || ',	$phpcode);
		$phpcode = preg_replace('/\blt\b/i', 	' < ', 	$phpcode);
		$phpcode = preg_replace('/\bgt\b/i', 	' > ', 	$phpcode);
		$phpcode = preg_replace('/\bge\b/i', 	' >= ',	$phpcode);
		$phpcode = preg_replace('/\ble\b/i', 	' <= ',	$phpcode);
		$phpcode = preg_replace('/\beq\b/i', 	' == ',	$phpcode);
		if (substr($phpcode,-1,1)!=';') $phpcode .= ';';
		if ('return '!= substr($phpcode,0,7)) $phpcode = 'return '.$phpcode;
		$phpcode = '$application = &pinax_ObjectValues::get(\'org.pinax\', \'application\'); $user = &$application->getCurrentUser(); $menu = &$application->getCurrentMenu();'.$phpcode;
		return $phpcode;
	}



	/**
	 * @param  object  $actionClass
	 * @param  string  $method
	 * @param  array  $callParams
	 * @param  boolean $allowNullParams
	 * @param  pinax_dependencyInjection_Container $container
	 * @return mixed
     *
     * @throws \BadMethodCallException
     */
	public static function callMethodWithParams( $actionClass, $method, $callParams=null, $allowNullParams=false, $container=null )
	{
		if ( is_object( $actionClass ) )
		{
			$reflectionClass = new ReflectionClass( $actionClass );
			if ( $reflectionClass->hasMethod( $method ) )
			{
				$reflectionMethod = $reflectionClass->getMethod( $method );
				$methodParams = $reflectionMethod->getParameters();
				$params = array();
				foreach( $methodParams as $k=>$v )
				{
					$defaultValue = $v->isDefaultValueAvailable() ? $v->getDefaultValue() : null;
					if (is_array($callParams) && isset($callParams[$v->name])) {
						$params[] = $callParams[$v->name];
					} else if ($container) {
						$paramClass = $v->getClass();
						$params[] = $paramClass ? $container->get($paramClass->name) : $defaultValue;
                    } else if ($allowNullParams || $defaultValue) {
                        $params[] = $defaultValue;
					} else {
						throw new \BadMethodCallException('Call '.get_class($actionClass).'::'.$method.', missing argument: '.$v->name);
					}
				}
				return call_user_func_array( array( $actionClass, $method ), $params );
			}
		}

		return false;
	}
}
