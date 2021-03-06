<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_DataGrid extends pinax_components_ComponentContainer
{
    var $_columns;
    var $_cssClass;
    var $_dataProvider;
    var $_totalRecord;
    var $_orderBy;
    var $_orderDirection;
    var $_primarykey;
    var $_versionFieldName = null;
    var $_languageFieldName = null;
    var $iterator;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('controller',    false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('cssClass',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('tableCssClass',    false,    'list',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('dataProvider',    false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('filters',        false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('label',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('paginate',        false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('query',            false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('skipOrder',        false,    false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('skipGroup',        false,    true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('drawHeader',    false,    true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('renderRow',     false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('orderBy',         false,    NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('orderDirection',false,    NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('hideTotals',     false,    false,        COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('allowEmptySearch',     false,    true, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('emptySearchLabel',     false,    '',        COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        $this->processChilds();

        // inizializza le variabili
        $sessionEx                 = &pinax_ObjectFactory::createObject('pinax.SessionEx', $this->getId());
        $this->_cssClass         = explode(',', $this->getAttribute('cssClass'));
        $this->_columns         = array();
        $filters                 = array();
        $this->_totalRecord     = NULL;

        // legge le colonne da visualizzare dai figli
        for ($i=0; $i<count($this->childComponents);$i++)
        {
            $this->_columns[] = $this->childComponents[$i]->getProperties();
        }

        // verifica se c'??? una colonna da ordinare
        $this->_orderBy         = $sessionEx->get('orderBy', $this->getAttribute('orderBy'), true, true);
        $this->_orderDirection     = $sessionEx->get('orderDirection', $this->getAttribute('orderDirection'), true, true);
    if ( empty( $this->_orderDirection ) ) $this->_orderDirection = 'ASC';

        // TODO
        // la funzione di autosalvataggio dei sessionEx non funziona ebne
        // quindi i valori li risalvo a mano
        $sessionEx->set('orderBy', $this->_orderBy);
        $sessionEx->set('orderDirection', $this->_orderDirection);


        if( $this->_orderBy && !$this->_columns[ $this->_orderBy ][ 'visible' ] )
        {
            $this->_orderBy = null;
        }


        if (is_null($this->_orderBy ))
        {
            for ($i=0; $i<count($this->_columns); $i++)
            {
                $v = $this->_columns[$i];
                if ($v['visible']===true)
                {
                    $this->_orderBy = $i;
                    break;
                }
            }
        }

        // verifica se ci sono dei filtri da applicare
        $filtersObj = &$this->getAttribute('filters');
        if (is_object($filtersObj))
        {
            $filters = $filtersObj->getFilters();
        }

        // esegue la paginazione
        $pageLimits    = NULL;
        $paginateClass    = $this->getAttribute("paginate");
        if ( is_object( $paginateClass ) )
        {
            $paginateClass->setRecordsCount();
            $pageLimits = $paginateClass->getLimits();
        }

        // legge i dati dal dataprovider
        $this->_dataProvider         = &$this->getAttribute('dataProvider');
        // if (is_null($this->_dataProvider))
        // {
        //     // TODO
        //     // visualizzare errore e uscire
        // }


        if ($this->_dataProvider instanceof pinax_components_DataProvider) {
            $ar                         = $this->_dataProvider->getNewObject();
            $this->_primarykey          = $ar->getPrimaryKeyName();

            $skipSearch = false;
            if (!$this->getAttribute('allowEmptySearch'))
            {
                $skipSearch = true;
                if (count($filters))
                {
                    foreach($filters as $k=>$v)
                    {
                        if (!empty($v))
                        {
                            $skipSearch = false;
                            break;
                        }
                    }
                }
            }

            if ( !$skipSearch )
            {
                // legge i dati dai record
                $options = array();
                $options['filters'] = $filters;
                $options['limit'] = $pageLimits;
                $options['numRows'] = true;
                if (!$this->getAttribute('skipOrder') && isset( $this->_columns[$this->_orderBy] ) && $this->_columns[$this->_orderBy]['columnName'] ) $options['order'] = array($this->_columns[$this->_orderBy]['columnName'] => $this->_orderDirection);
                if (!$this->getAttribute('skipGroup')) $options['group'] = $this->_primarykey;

                $this->iterator = $this->_dataProvider->loadQuery('', $options);
                $this->_totalRecord = $this->iterator->count();
            }
        } else if (
                $this->_dataProvider instanceof pinax_dataAccessDoctrine_SqlRecordIterator ||
                $this->_dataProvider instanceof pinax_dataAccessDoctrine_AbstractRecordIterator
            ) {

            $this->_primarykey = null;
            $this->iterator = $this->_dataProvider;

            if (!$this->getAttribute('skipOrder') && isset( $this->_columns[$this->_orderBy] ) && $this->_columns[$this->_orderBy]['columnName'] ) {
                $this->iterator->orderBy($this->_columns[$this->_orderBy]['columnName'], $this->_orderDirection);
            }

            $this->iterator->limit($pageLimits);
            $this->_totalRecord = $this->iterator->count();
        } else {
            throw new Exception('Wrong DataProvider in component '.$this->getId());
        }

        if ( is_object( $paginateClass ) )
        {
            $paginateClass->setRecordsCount($this->iterator->count());
        }

    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        if ( !$this->iterator )
        {
            $this->addOutputCode( '<p>'.$this->getAttribute( 'emptySearchLabel' ).'</p>' );
            return;
        }
        // legge le colonne da visualizzare dai figli
        // NOTA: le colonne sono gi??? state lette sul process
        // ma vengono rilette perch??? pu??? essere variata la visibilit???
        $this->_columns = array();
        for ($i=0; $i<count($this->childComponents);$i++)
        {
            $this->_columns[] = $this->childComponents[$i]->getProperties();
        }


        $addJsCode = false;
        $output = '';

        if ( $this->_totalRecord > 0 )
        {
            $class = $this->getAttribute('tableCssClass')!='' ? ' class="'.$this->getAttribute('tableCssClass').'"' : '';
            $output .= '<table id="'.$this->getId().'"'.$class.'>';
            if ($this->getAttribute('label')!='') $output .= '<caption>'.$this->getAttribute('label').'</caption>';

            if ($this->getAttribute('drawHeader'))
            {
                // disegna le colonne
                $output .= '<thead>';
                $output .= '<tr>';
                //foreach ($this->_columns as $v)

                for ($i=0; $i<count($this->_columns); $i++)
                {
                    $v = $this->_columns[$i];
                    if ($v['visible']===true)
                    {
                        $cssClass = !empty($v['cssClass']) ? ' class="'.$v['cssClass'].'"' : '';
                        $id = !empty($v['id']) ? ' id="'.$v['id'].'"' : '';
                        $width = !empty($v['width']) ? ' style="width: '.$v['width'].'px;"' : '';

                        if (!empty($v['renderCell']))
                        {
                            $renderCell = &pinax_ObjectFactory::createObject($v['renderCell'], $this->_application );
                            if (method_exists($renderCell, 'getHeader'))
                            {
                                $v['headerText'] = $renderCell->getHeader( $v['headerText'] );
                            }
                            unset( $renderCell );
                        }


                        if (!empty($v['command']))
                        {
                            $output .= '<th'.$id.$cssClass.$width.'>';
                            if ($v['command']=='publish')
                            {
                                $output .= '<input name="publishAll" value="" type="checkbox" class="js-selectall" />';
                            }
                            $output .= '</th>';
                        }
                        else
                        {
                            if (!$this->getAttribute('skipOrder') && $v['orderable'] )
                            {
                                $addJsCode = true;
                                $headerId = 'orderBy_'.$i;
                                $headerClass = 'DataGridHeader';

                                $headerImage = '';
                                if ($i==$this->_orderBy)
                                {
                                    $headerId .= '_'.($this->_orderDirection=='ASC' ? 'DESC':'ASC');
                                    $headerImage = ($this->_orderDirection=='ASC' ? '<span class="ui-icon ui-icon-triangle-1-s"></span>':'<span class="ui-icon ui-icon-triangle-1-n"></span>');
                                }
                                else $headerId .= '_'.$this->_orderDirection;

                                $output .= '<th'.$id.$cssClass.$width.'><a href="#" id="'.$headerId.'" class="'.$headerClass.'">'.$v['headerText'].'</a>'.$headerImage.'</th>';
                            }
                            else
                            {
                                $output .= '<th'.$cssClass.$width.'>'.$v['headerText'].'</th>';
                            }
                        }
                    }
                }
                $output .= '</tr>';
                $output .= '</thead>';
            }

            if (!$this->getAttribute('hideTotals'))
            {
                $output .= '<tfoot>';
				$output .= '<tr><td style="text-align: right;" colspan="'.count($this->_columns).'">'.__T('PNX_TOTAL_RECORDS').' '.$this->_totalRecord.'</td></tr>';
                $output .= '</tfoot>';
            }
            $output .= '<tbody>';

            $key = 0;
            $tempCssClass = $this->_cssClass;
            $rowCellClass = $this->getAttribute('renderRow');;
            if (!empty($rowCellClass))
            {
                $rowCellClass = &pinax_ObjectFactory::createObject($rowCellClass, $this->_application );
            }

            foreach ($this->iterator as $ar)
            {
                $v = method_exists($ar, 'getValuesAsArray') ? $ar->getValuesAsArray(true) : (array)$ar;
                $rowOutput = '';
                foreach ($this->_columns as $vv)
                {
                    if ($vv['key']) $key = $v[$vv['columnName']];
                    if ($vv['visible']===false) continue;

                    $tempOutput = '';
                    $cssClass = '';
                    if (!empty($vv['renderCell']))
                    {
                        $renderCell = &pinax_ObjectFactory::createObject($vv['renderCell'], $this->_application );
                        if ( is_object( $renderCell ) )
                        {
                            $tempOutput .= $renderCell->renderCell($key, isset($v[$vv['columnName']]) ? $v[$vv['columnName']] : '', $ar, $vv['columnName']);
                            $cssClass = !empty($vv['cssClass']) ? $vv['cssClass'] : '';
                            if (method_exists($renderCell, 'getCssClass'))
                            {
                                $cssClass = $renderCell->getCssClass($key, isset($v[$vv['columnName']]) ? $v[$vv['columnName']] : '', $v);
                            }
                        }
                    }
                    else if (!empty($vv['command']))
                    {
                        $addJsCode = true;

                        if (strtolower($this->_application->getPageId())=='usergroups')
                        {
                            if ($vv['command']=='delete' && $v['usergroup_backEndAccess']) continue;
                        }
                        switch ($vv['command'])
                        {
                            case 'edit':
                                if ($this->_user->acl($vv['aclService'], $vv['command'])) {
                                    $icon = 'pinax.datagrid.action.editCssClass';
                                    $result = true;

                                    if (!is_null($this->_versionFieldName))
                                    {
                                        if ($this->_user->acl($vv['aclService'], $vv['command']))
                                        {

                                            $ar = &$this->_dataProvider->getNewObject();
                                            $joinFields = $ar->getJoinFields();
                                            $result = $ar->find(array($joinFields['detailTable'] => $key, $this->_versionFieldName => 'PUBLISHED'));
                                        }
                                    }

                                    $tempOutput .= __Link::makeLinkWithIcon(
                                        '#',
                                        __Config::get($icon).($result ? '' : ' disabled'),
                                        array(
                                            'cssClass' => 'DataGridCommand',
                                            'title' => __T('PNX_RECORD_EDIT'),
                                            'id' => $result ? 'edit_'.$key : ''
                                        )
                                    );
                                }
                                break;
                            case 'editDraft':
                                if (!is_null($this->_versionFieldName) && $this->_user->acl($vv['aclService'], $vv['command']))
                                {
                                    $ar = &$this->_dataProvider->getNewObject();
                                    $joinFields = $ar->getJoinFields();
                                    $result = $ar->find(array($joinFields['detailTable'] => $key, $this->_versionFieldName => 'DRAFT'));

                                    $icon = 'pinax.datagrid.action.editDraftCssClass';
                                    $tempOutput .= __Link::makeLinkWithIcon(
                                        '#',
                                        __Config::get($icon).($result ? '' : ' disabled'),
                                        array(
                                            'cssClass' => 'DataGridCommand',
                                            'title' => __T('PNX_RECORD_EDIT_DRAFT'),
                                            'id' => $result ? 'editDraft_'.$key : ''
                                        )
                                    );
                                }
                                break;
                            case 'preview':

                                $tempOutput .= __Link::makeLinkWithIcon(
                                        '#',
                                        __Config::get('pinax.datagrid.action.showCssClass'),
                                        array(
                                            'cssClass' => 'DataGridCommand',
                                            'title' => __T('PNX_RECORD_DELETE'),
                                            'id' => 'preview_'.$key
                                        )
                                    );
                                break;

                            case 'delete':
                                if ($this->_user->acl($vv['aclService'], $vv['command'])) {
                                    $tempOutput .= __Link::makeLinkWithIcon(
                                        '#',
                                        __Config::get('pinax.datagrid.action.deleteCssClass'),
                                        array(
                                            'cssClass' => 'DataGridCommand',
                                            'title' => __T('PNX_RECORD_DELETE'),
                                            'id' => $result ? 'delete_'.$key : ''
                                        )
                                    );

                                }
                                break;
                            case 'publish':
                                if ($this->_user->acl($vv['aclService'], $vv['command'])) {
                                    $tempOutput .= '<input name="publish[]" value="'.$key.'" type="checkbox">';
                                }
                                break;
                        }
                    }
                    else
                    {
                        if (!is_null($this->_languageFieldName) && $v[$this->_languageFieldName]!=$this->_application->getEditingLanguageId())
                        {
                            $tempOutput .= '<em>'.$v[$vv['columnName']].'</em>';
                        }
                        else
                        {
                            $tempOutput .= $v[$vv['columnName']];
                        }
                    }
                    $cssClass = !empty($cssClass) ? ' class="'.$cssClass.'"' : '';
                    $rowOutput .= '<td style="text-align: '.$vv['align'].';"'.$cssClass.'>'.$tempOutput.'</td>';
                }

                if (!count($tempCssClass)) $tempCssClass = $this->_cssClass;
                $cssClass = array_shift($tempCssClass);
                if (!empty($rowCellClass))
                {
                    $output .= $rowCellClass->renderRow($v, $cssClass);
                }
                else
                {
                    $output .= '<tr class="'.$cssClass.'" id="row_'.$key.'">';
                }
                $output .= $rowOutput.'</tr>';
            }

            $output .= '</tbody>';
            $output .= '</table>';
        }
        else
        {
            $emptyLabel = $this->getAttribute( 'emptyLabel' );
            if ( !empty( $emptyLabel ) )
            {
                $output .= '<p class="datagridEmpty">'.$emptyLabel.'</p>';
            }
        }

        $this->addOutputCode($output);

        if ( !$addJsCode ) return;

        $jsId = $this->getId();
        $jsMessage = pinax_locale_Locale::get('PNX_RECORD_MSG_DELETE');


        // TODO
        // controllare che il valore di controller sia settato
        $controllerClass     = &$this->getAttribute('controller');
        if (is_object($controllerClass))
        {
            $jsCurrentStateUrl = $controllerClass->changeStateUrl($controllerClass->getState());
            $jsStateUrl = __Link::addParams(array('state' => ''), false, $jsCurrentStateUrl);
            $jsStateUrl = __Link::removeParams(array($controllerClass->getId().'_recordId'), $jsStateUrl);
            $controllerId = $controllerClass->getid();
        }
        else
        {
            $jsStateUrl = __Link::removeParams(array($jsId.'_orderBy', $jsId.'_orderDirection'));
            $jsCurrentStateUrl = $jsStateUrl;
            $controllerId = '';
            if (strpos($jsCurrentStateUrl, '?')===false) {
                $jsCurrentStateUrl .= '?';
            }
        }

        $output = <<<EOD
<script language="JavaScript" type="text/JavaScript">
jQuery(document).ready(function() {
    jQuery('#$jsId input.js-selectall').change(function(){
        var checked = this.checked;
        $(this).closest('table').find("input[name='publish[]']").each(function(i, el){
            el.checked = checked;
        })
    });

    jQuery(['#$jsId .DataGridCommand', '#$jsId .DataGridHeader']).each(function(index,element)
    {
        jQuery( element ).css( {cursor: 'pointer'} );
        jQuery( element ).click( function()
        {
            var command = this.id.split('_');
            var loc = "{$jsStateUrl}"+command[0]+"&{$controllerId}_recordId="+command[1];
            switch (command[0])
            {
                case 'delete':
                    this.parentNode.parentNode.oldClass2    = this.parentNode.parentNode.oldClass;
                    this.parentNode.parentNode.className2    = this.parentNode.parentNode.className;
                    this.parentNode.parentNode.className    = "ruled";
                    this.parentNode.parentNode.oldClass        = "ruled";

                    if (confirm("{$jsMessage}"))
                    {
                        location.href = loc;
                    }

                    this.parentNode.parentNode.oldClass    = this.parentNode.parentNode.oldClass2;
                    this.parentNode.parentNode.className = this.parentNode.parentNode.oldClass2;
                    break;
                case 'orderBy':
                    loc = "{$jsCurrentStateUrl}&{$jsId}_orderBy="+command[1]+"&{$jsId}_orderDirection="+command[2];
                    location.href = loc;

                default:
                    location.href = loc;
                    break;

            }
        });
    });
});
</script>
EOD;

        if ( !empty( $output ) ) $this->addOutputCode($output);
    }
}
