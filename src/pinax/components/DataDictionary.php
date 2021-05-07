<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_DataDictionary extends pinax_components_Component
{
	var $iterator;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('recordClassName',	true, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('query', 			false,	NULL,	COMPONENT_TYPE_STRING);
	    $this->defineAttribute('queryParams', 		false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('field', 			false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('skipEmpty', 		false,	true,	COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('delimiter',         false,  '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('useCache', 		    false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('distinctValues', 	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('defaultLanguageIfNotAvailable', false, __Config::get('pinaxcms.content.defaultLanguageIfNotAvailable'), COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$classPath 	= $this->getAttribute('recordClassName');
		if (is_null($classPath))
		{
			throw new Exception(sprintf("DataProvider: record class don't found: %s", $this->getAttributeString('recordClassName')));
		}
		else
		{
			$this->iterator = &pinax_ObjectFactory::createModelIterator($classPath);
			if ($this->iterator === false)
			{
				throw new Exception(sprintf("DataProvider: iterator class don't found: %s", $classPath));
			}

			if ($this->getAttribute('defaultLanguageIfNotAvailable') and method_exists($this->iterator, 'whereLanguageIs')) {
				$this->iterator->whereLanguageIs(__ObjectValues::get('org.pinax', 'languageId'), false);
			}
		}
	}

	function getItems()
	{
        $oldCacheValue = __Config::get('QUERY_CACHING');
        __Config::set('QUERY_CACHING', $this->getAttribute('useCache'));

		if ( is_null( $this->iterator ) )
		{
			$this->process();
		}
		$items = pinax_ObjectValues::get('pinax.components.DataDictionary', $this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'));
		if (is_null($items))
		{
			$items = __Session::get($this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query') );
		}

		if (is_null($items) )
		{
			$items = $this->loadDictionary(	$this->getAttribute('field'),
											$this->getAttribute('query'),
                                            unserialize($this->getAttribute('queryParams')),
											$this->getAttribute('skipEmpty'),
											$this->getAttribute('delimiter'),
											$this->getAttribute('distinctValues') );
    		pinax_ObjectValues::set('pinax.components.DataDictionary', $this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'), $items);
			if ( $this->getAttribute('delimiter') != '' )
			{
				__Session::set($this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'), $items);
			}
		}

        __Config::set('QUERY_CACHING', $oldCacheValue);
		return $items;
	}

    function loadDictionary($field, $queryName = null, $queryParams = null, $skipEmpty = false, $delimiter = '', $distinctValues = false)
    {
        if ($queryName) {
            $this->iterator->load($queryName, $queryParams);
            $k = 'k';
            $v = 'v';
        }
        else {
            $field = explode(',', $field);

            if (count($field) == 1) {
                $k = $field[0];
                $v = $field[0];
            } else {
                $k = $field[0];
                $v = $field[1];
            }

            if (method_exists($this->iterator, 'selectDistinct')) {
                $this->iterator->selectDistinct($v, [$k]);
            }

            $this->iterator->orderBy($v);
        }

        $result = array();
        $usedKeys = array();
		$valuesDistinct = array();

        foreach ($this->iterator as $ar) {
            $key = $ar->$k;
            $value = $ar->$v;

            if ($skipEmpty && empty($value)) {
                continue;
            }

			if($distinctValues)
			{
				if(in_array($value, $valuesDistinct))
				{
					continue;
				}
				$valuesDistinct[] = $value;
			}

            if (!$delimiter) {
                $result[] = array('key' => $key, 'value' => $value);
            } else {
                $kk = explode( $delimiter, $key );
				$vv = explode( $delimiter, $value );
				$l = count( $kk );
				for( $i = 0; $i < $l; $i++ ) {
					if ( !in_array( $kk[ $i ], $usedKeys ) )
					{
					 	$usedKeys[] = $kk[ $i ];
					 	$result[] = array('key' => $kk[ $i ], 'value' => $vv[ $i ] );
					}
				}
            }
        }

        if ($delimiter) {
			pinax_helpers_Array::arrayMultisortByLabel( $result, 'value' );
		}

        return $result;
	}
}
