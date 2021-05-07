<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface pinax_components_dataGridAjax_interfaces_Search
{
    public function search($options, $columns, $filters, $ordering, $paging);
}
