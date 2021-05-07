<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_dataAccessDoctrine_cache_AbstractQuery implements pinax_dataAccessDoctrine_cache_QueryCacheInterface
{
    protected $iterator;
    protected $modelSignature;
    protected $group;
    protected $lifeTime;

    /**
     * @param pinax_dataAccessDoctrine_AbstractRecordIterator $iterator
     * @param int                                              $lifeTime
     */
    function __construct(Iterator $iterator, $lifeTime=null)
    {
        $this->iterator = $iterator;
        $this->lifeTime = $lifeTime;
        $this->modelSignature = $this->iterator->querySignatureForCaching();
        $this->group = $this->iterator->queryGroupForCaching();
        $this->createCacheObject();
    }

    /**
     * @param  string|null $queryName Nome della query, parametro opzionale
     *
     * @return pinax_dataAccessDoctrine_cache_Iterator
     */
    public function get($queryName = null)
    {
        $queryName = $queryName ? : $this->modelSignature;
        $data = $this->getFromCache($queryName);
        if ($data===false || is_null($data)) {
            $data = array();
            $isAR = null;
            foreach ($this->iterator as $ar) {
                if (is_null($isAR)) {
                    $isAR = method_exists($ar, 'getValuesAsArray');
                }
                $data[] = $isAR ? $ar->getValuesAsArray() : (array)$ar;
            }
            $this->setInCache($queryName, $this->serialize($data));
        } else {
            $data = $this->unserialize($data);
        }

        return new pinax_dataAccessDoctrine_cache_Iterator($data);
    }

    /**
     * Cancella la query dalla cache
     *
     * @param  string|null $queryName
     *
     * @return int Risultato della cancellazione
     */
    abstract public function remove($queryName = null);

    /**
     * @param  $data
     * @return
     */
    private function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @param  $data
     * @return
     */
    private function unserialize($data)
    {
        return unserialize($data);
    }

    abstract protected function createCacheObject();

    /**
     * @param  string $keyName
     * @return string
     */
    abstract protected function getFromCache($keyName);

    /**
     * @param string $keyName
     * @param string $value
     */
    abstract protected function setInCache($keyName, $value);
}
