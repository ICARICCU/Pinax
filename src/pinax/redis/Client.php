<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_redis_Client extends PinaxObject
{
    /** @var Predis\Client $predis */
    private static $predis;
    private static $currentRedisDB = null;
    private $redisDB;

    function __construct($redisDB)
    {
        $this->redisDB = $redisDB;
    }

    public function __call($method, $args)
    {
        if (self::$currentRedisDB != $this->redisDB) {
            self::$predis->select($this->redisDB);
            self::$currentRedisDB = $this->redisDB;
        }
        return call_user_func_array([self::$predis, $method], $args);
    }

    /**
     * @param int $redisDB
     * @return \Predis\Client
     */
    public static function getConnection($redisDB = 0)
    {
        if (is_null(self::$predis)) {
            $host = __Config::get('pinax.database.caching.redis');
            self::$predis = new Predis\Client($host ? $host : 'tcp://127.0.0.1:6379');
        }

        return new self($redisDB);
    }
}
