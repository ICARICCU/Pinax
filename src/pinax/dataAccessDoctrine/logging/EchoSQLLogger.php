<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Doctrine\DBAL\Connection,
    \Doctrine\DBAL\Query\Expression\CompositeExpression;

class pinax_dataAccessDoctrine_logging_EchoSQLLogger extends \Doctrine\DBAL\Logging\EchoSQLLogger
{
    public $start = null;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);

        $stackTrace = array_slice(debug_backtrace(), 3);
        printf('%s:%s<br />%s:%s<br /><br />', $stackTrace[0]['file'], $stackTrace[0]['line'], $stackTrace[1]['file'], $stackTrace[1]['line']);

        $replacedSql = $sql;
        if ($params) {
            $params = array_reverse($params);
            foreach ($params as $param => $value) {
                $value = is_string($value) ? "'".$value."'" : $value;
                $replacedSql = str_replace($param, $value, $replacedSql);
            }
        }

        echo SqlFormatter::format($replacedSql).'<br /><br />';
        parent::startQuery($sql, $params, $types);
    }

     /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        echo '<strong>query time: '.round((microtime(true)-$this->start), 3).'s </strong></br></br>';
    }
}
