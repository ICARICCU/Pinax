<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_dataAccessDoctrine_cache_QueryCacheInterface
{

    /**
     * @param  string|null $queryName Nome della query, parametro opzionale
     *
     * @return pinax_dataAccessDoctrine_cache_Iterator
     */
    public function get($queryName = null);

    /**
     * Cancella la query dalla cache
     *
     * @param  string|null $queryName
     *
     * @return int Risultato della cancellazione
     */
    public function remove($queryName = null);

}
