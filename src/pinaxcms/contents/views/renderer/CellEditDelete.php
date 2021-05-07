<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_views_renderer_CellEditDelete extends pinaxcms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row, $columnName)
    {
        $this->loadAcl($key);

        $output = $this->renderEditButton($key, $row).
                    $this->renderDeleteButton($key, $row);
        return $output;
    }
}


