<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_roleManager_views_Permissions extends pinax_components_Input
{
    protected static $ACTIONS = array('all', 'edit', 'editDraft', 'new', 'delete', 'publish', 'visible');

    function process()
    {
        parent::process();
        if (is_string($this->_content)) {
            $this->_content = unserialize($this->_content);
            if (!$this->_content) $this->_content = array();
        }
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        $output = <<<EOD
<script>
$(function(){
    var updateCheckboxInRow = function(currentTarget) {
        $(currentTarget).parent().parent().find('input').each(function(item, el){
            if (el==currentTarget) return;
            $(el).attr('disabled', currentTarget.checked);
            if (currentTarget.checked) {
                el.checked = false;
            }
        });
    }

    $("input[data-action='all']").click(function(evt){
        updateCheckboxInRow(evt.currentTarget);
    }).each(function(item, el){
        updateCheckboxInRow(el);
    });
})
</script>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th></th>
            <th>Consenti tutto</th>
            <th>Modifica</th>
            <th>Modifica bozza</th>
            <th>Nuovo</th>
            <th>Cancellazione</th>
            <th>Pubblicazione</th>
            <th>Visualizzazione</th>
        </tr>
    </thead>
    <tbody>
EOD;
        $siteMap = $this->_application->getSiteMap();

        $siteMapIterator = &pinax_ObjectFactory::createObject('pinax.application.SiteMapIterator', $siteMap);

        $cssClass = '';
        $row = 0;
        $perms = array('a', 'm', 'b', 'n', 'c', 'p', 'v');
        $modules = array();

        while (!$siteMapIterator->EOF) {
            $n = $siteMapIterator->getNode();
            $title = $n->getAttribute('adm:aclLabel') ? __T($n->getAttribute('adm:aclLabel')) : $n->getAttribute('title');
            $acl = $n->getAttribute('adm:acl');
            if ($title && $acl) $modules[$title] = $n;
            $siteMapIterator->moveNext();
        }
        ksort($modules);

        foreach($modules as $title=>$n) {
            $cssClass = $cssClass == 'odd' ? 'even' : 'odd';
            $output .= '<tr class="'.$cssClass.'"><td>'.$title.'</td>';
            $acl = $n->getAttribute('adm:acl');

            if ($acl == '*') {
                $v = '1111111';
            }
            else {
                $v = '';
                $acl = array_flip(explode(',', $acl));
                foreach ($perms as $p) {
                    $v .= isset($acl[$p]) ? '1' : '0';
                }
            }

            $id = $n->getAttribute('id');
            $this->drawCheckox($id, $v, $row, 0, $output);
            $this->drawCheckox($id, $v, $row, 1, $output);
            $this->drawCheckox($id, $v, $row, 2, $output);
            $this->drawCheckox($id, $v, $row, 3, $output);
            $this->drawCheckox($id, $v, $row, 4, $output);
            $this->drawCheckox($id, $v, $row, 5, $output);
            $this->drawCheckox($id, $v, $row, 6, $output);
            $output .= '</tr>';

            $aclPageTypes = $n->getAttribute('adm:aclPageTypes');

            if ($aclPageTypes) {
                $output .= '<input type="hidden" name="aclPageTypes['.$id.']" value="'.$aclPageTypes.'" />';
            }

            $row++;
        }

        $output .= '</tbody></table>';
        $this->addOutputCode($output);
    }

    private function drawCheckox($id, $flags, $row, $pos, &$output)
    {
        $checked = @$this->_content[$id][self::$ACTIONS[$pos]] == '1' ? 'checked="checked"' : '';
        if ($flags{$pos}=='1') $output .= '<td style="text-align: center"><input type="checkbox" name="permissions['.$id.']['.self::$ACTIONS[$pos].']" value="1" '.$checked.' data-action="'.self::$ACTIONS[$pos].'" /></td>';
        else $output .= '<td></td>';
    }

}
