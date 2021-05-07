<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_views_renderer_RecordTranslationStatus extends pinax_components_render_RenderCell
{
    private $languages;
    private $labelOK;
    private $labelMissinTranslation;

    function __construct($application)
	{
        parent::__construct($application);
        $this->languages = pinax_ObjectValues::get('org.pinax', 'languagesId');
        $this->labelOK  = __T('PNX_RECORD_TRANSLATION_OK');
        $this->labelMissinTranslation = __T('PNX_RECORD_TRANSLATION_MISSING');
    }

    function renderCell($key, $value, $row, $columnName)
    {
        $output = '';
        $diff = array_diff($this->languages, $row->translatedLanguages());

        return !count($diff) ? '<i class="icon-ok-sign text-success" title="'.$this->labelOK.'"></i>' :
                                '<i class="icon-question-sign text-error" title="'.$this->labelMissinTranslation.'"></i>';
    }
}


