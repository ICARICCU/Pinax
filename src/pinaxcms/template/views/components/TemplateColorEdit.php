<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_views_components_TemplateColorEdit extends pinax_components_Component
{
    private $idParent;

    public function render_html()
    {
        $templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
        $templateRealPath = $templateProxy->getTemplateRealpath();

        if ($templateRealPath) {
            $idParent = $this->_parent->_parent->getId().'-';
            $data = json_decode(file_get_contents($templateRealPath.'/colors.json'));
            if ($data) {
                $output = $this->addFieldset($data, $idParent);
                $this->addOutputCode($output);
            }
        }
    }

    private function addFieldset(&$data, $idParent)
    {
        $id = $this->getId();
        $output = '';
        $presets = array();
        $ids = array();
        $label = __T('Preset');
        foreach($data as $k=>$v) {
            $result = $this->addColorPicker($v, $idParent);
            $output .= '<fieldset><legend>'.__T($k).'</legend>'.
                        implode('', $result['output']).
                        '</fieldset>';
            $presets = array_merge($presets, $result['presets']);
            $ids = array_merge($ids, $result['id']);
        }

        $presetOptions = $this->getPresetOptions($presets, $label);

        $presets = implode(',', $presets);
        $ids = implode(',', $ids);
        $output = <<<EOD
    <div class="control-group">
        <label class="control-label " for="{$idParent}{$id}">{$label}</label>
        <div class="controls">
            <select data-elements="{$ids}" data-type="valuesPreset" class="span11" name="{$id}" id="{$idParent}{$id}"><option value="">-</option>$presetOptions</select>
        </div>
    </div>
    {$output}
EOD;

        return $output;
    }

    private function getPresetOptions($presets, $label)
    {
        $presetCount = count($presets[0]);
        for ($i = 0; $i < $presetCount; $i++) {
            $presetValues = $this->getPresetValues($presets, $i);
            $presetOptions .= '<option data-options="' . $presetValues . '" value="' . $i . '">' . $label . ' ' . ($i + 1) . '</option>';
        }

        return $presetOptions;
    }

    private function getPresetValues($presets, $i)
    {
        $values = array();
        foreach ($presets as $p) {
            $values[] = $p[$i];
        }

        return implode(',', $values);
    }

    private function addColorPicker(&$data, $idParent)
    {
        $result = array('output' => array(), 'presets' => array(), 'id' => array());
        $presets = '';
        foreach($data as $k=>$v) {
            $label = __T($k);
            $id = $v->id[0];
            $result['id'][] = $id;
            $result['presets'][] = $v->preset;
            $result['output'][] = <<<EOD
<div class="control-group">
    <label for="{$idParent}{$id}"  class="control-label ">{$label}</label>
    <div class="controls">
        <input id="{$idParent}{$id}" name="{$id}" title="{$label}" class="span11 " type="text" value="" data-type="colorPicker"/>
    </div>
</div>
EOD;
        }
        return $result;
    }
}
