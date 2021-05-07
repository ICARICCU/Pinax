<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinaxcms_views_components_ModuleRecordPicker extends pinax_components_ComponentContainer
{
    function process()
    {
        $value = $this->_parent->loadContent($this->getId());

        $speakingUrlManager = $this->_application->retrieveProxy('pinaxcms.speakingUrl.Manager');
        $resolveVO = $speakingUrlManager->resolve($value);

        $this->_content = $resolveVO && $resolveVO->refObj ? $resolveVO : null;

        parent::process();
    }

    function getContent()
    {
        if (count($this->childComponents))
        {
            for ($i=0; $i<count($this->childComponents);$i++)
            {
                $id = preg_replace('/^'.$this->getId().'\-/', '', $this->childComponents[$i]->getId());
                $r = $this->childComponents[$i]->getContent();
                $this->_content->refObj->{$id} = $r;
            }
        }

        return $this->_content;
    }

    function loadContent($id, $bindTo = '')
    {
        $id = preg_replace('/^'.$this->getId().'\-/', '', $id);
        return $this->_content ? $this->_content->refObj->{$id} : null;
    }


    public static function translateForMode_edit($node) {
        $ajaxController = $node->hasAttribute('ajaxController') ? $node->getAttribute('ajaxController') : 'pinaxcms.contents.controllers.autocomplete.ajax.PagePicker';
        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['required'] = $node->getAttribute('required');
        $attributes['data'] = 'type=CmsPagePicker;controllername='.$ajaxController.
                                ';pagetype='.$node->getAttribute('type').
                                ';multiple='.($node->getAttribute('multiple') ? 'true':'false').
                                ';protocol='.$node->getAttribute('protocol');
        $attributes['xmlns:pnx'] = "pinax.components.*";

        if (count($node->attributes)) {
            foreach ( $node->attributes as $index=>$attr ) {
                if ($attr->prefix=="adm") {
                    $attributes[$attr->name] = $attr->value;
                }
            }
        }

        return pinax_helpers_Html::renderTag('pnx:Input', $attributes);
    }

}
