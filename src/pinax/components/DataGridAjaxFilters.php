<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_DataGridAjaxFilters extends pinax_components_ComponentContainer
{
    private $fieldNumbers = null;

    function init()
    {
        // define the custom attributes
        $this->defineAttribute('dataGrid', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('fieldNumbers', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('forceChangeOnLoad', false, 'false', COMPONENT_TYPE_STRING);
        $this->defineAttribute('submitOnChange', false, true, COMPONENT_TYPE_BOOLEAN);

        parent::init();
    }

    function addOutputCode($output, $editableRegion='', $atEnd=false)
    {
        if (!$this->fieldNumbers) {
            $this->fieldNumbers = explode(',', $this->getAttribute('fieldNumbers'));
        }
        $fieldNumber = array_shift($this->fieldNumbers);
        $dataGridId = $this->getAttribute('dataGrid');
        $cssClass = $this->getAttribute('cssClass');
        $submitOnChange = $this->getAttribute('submitOnChange') ? 'true' : 'false';
        $forceChangeOnLoad = $this->getAttribute('forceChangeOnLoad');
        $id = $this->childComponents[0]->getId();

        $newOutput = <<<EOD
<div id="{$id}_cont" class="{$cssClass}" style="display: none; float: left">{$output}</div>
<script type="text/javascript">
(function ($) {
    $(function () {
        var table = $('#$dataGridId').data('dataTable'),
            ooSettings = table.fnSettings();

        $("#{$id}_cont").appendTo("#{$dataGridId}_filter").show();
        $("#$id").val(ooSettings.aoPreSearchCols[$fieldNumber].sSearch);

        if ($submitOnChange) {
            $("#$id").change(function () {
                table.fnFilter($(this).val(), $fieldNumber);
            });
        }

        if ('false' != '$forceChangeOnLoad') {
            // force change event to synchronize the filter at the document load
            $('#$id').change();
        }

    });
})(jQuery);
</script>
EOD;

        parent::addOutputCode($newOutput, $editableRegion, $atEnd);
    }
}
