<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dataAccessDoctrine_cache_QueryRedis extends pinax_dataAccessDoctrine_cache_AbstractQuery
{
    /** @var \Predis\Client $redis */
    protected $redis;

    protected function createCacheObject()
    {
        $this->redis = pinax_redis_Client::getConnection();
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
        $result = 0;
        // se key ha l'asterisco, cancella tutte le chiavi che iniziano per il prefisso in $key
        if (strpos($queryName, '*') !== false) {
            foreach ($this->redis->keys($queryName) as $k) {
                $result += $this->redis->del($k);
            }
        } else {
            $result += $this->redis->del($queryName);
        }
        return $result;
    }

    /**
     * @param  string $keyName
     * @return string
     */
    protected function getFromCache($keyName)
    {
        $data = $this->redis->get($keyName);
        if ($this->lifeTime != -1) {
            $this->redis->expire($keyName, $this->lifeTime);
        }
        return $data;
    }

    /**
     * @param string $keyName
     * @param string $value
     */
    protected function setInCache($keyName, $value)
    {
        $a = $this->redis->set($keyName, $value);
        if ($this->lifeTime != -1) {
            $this->redis->expire($keyName, $this->lifeTime);
        }
    }
}
