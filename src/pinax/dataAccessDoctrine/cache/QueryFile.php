<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_cache_QueryFile extends pinax_dataAccessDoctrine_cache_AbstractQuery
{
    private $_cacheObj;

    protected function createCacheObject()
    {
        $cacheFolder = pinax_Paths::getRealPath('CACHE_CODE');
        $options = array(
            'cacheDir' => $cacheFolder,
            'lifeTime' => !$this->lifeTime ? __Config::get('CACHE_CODE') : $this->lifeTime,
            'hashedDirectoryLevel' => __Config::get('CACHE_CODE_DIR_LEVEL'),
            'readControlType' => '',
            'fileExtension' => '.php'
        );

        $this->_cacheObj = &pinax_ObjectFactory::createObject('pinax.cache.CacheFile', $options);
    }

    /**
     * Cancella la query dalla cache
     *
     * @param  string|null $queryName
     *
     * @return int Risultato della cancellazione
     */
    public function remove($queryName=null)
    {
        $queryName = $queryName ? : $this->modelSignature;
        return $this->_cacheObj->remove($queryName, $this->group);
    }

    /**
     * @param  string $keyName
     * @return string
     */
    protected function getFromCache($keyName)
    {
        return $this->_cacheObj->get($keyName, $this->group);
    }

    /**
     * @param string $keyName
     * @param string $value
     */
    protected function setInCache($keyName, $value)
    {
        $this->_cacheObj->save($value, $keyName, $this->group);
    }
}
