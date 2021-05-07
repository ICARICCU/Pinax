<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Search extends pinax_components_Form
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('label',            false,     pinax_locale_Locale::get('PNX_SEARCH_LABEL'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('buttonLabel',    false,     pinax_locale_Locale::get('PNX_SEARCH_BUTTON'),    COMPONENT_TYPE_STRING);
        $this->defineAttribute('comment',        false,     pinax_locale_Locale::get('PNX_SEARCH_COMMENT'),    COMPONENT_TYPE_STRING);
        $this->defineAttribute('skipFormTag',    false,     false,    COMPONENT_TYPE_BOOLEAN);
        parent::init();
        $this->setAttribute('method', 'get');
    }

    function process()
    {
        if ( $this->_application->isAdmin() ) {
            return;
        }

        $this->_content = new StdClass;
        $this->_content->label        = $this->getAttribute('label');
        $this->_content->buttonLabel  = $this->getAttribute('buttonLabel');
        $this->_content->comment      = $this->getAttribute('comment');
        $this->_content->comment1     = pinax_locale_Locale::get('PNX_SEARCH_RESULT');
        $this->_content->value        = pinax_Request::get('search', '');
        $this->_content->result       = null;

        if (strlen($this->_content->value)>=3)
        {
            $pluginObj = &pinax_ObjectFactory::createObject('pinax.plugins.Search');
            $this->_content->result = $pluginObj->run([
                            'search' => $this->_content->value,
                            'languageId' => $this->_application->getLanguageId()
                            ]);
            pinax_helpers_Array::arrayMultisortByLabel($this->_content->result, '__weight__', true);
        }

        $resultCount = !is_null($this->_content->result) ? count($this->_content->result) : 0;

        $this->_content->total =  pinax_locale_Locale::get('PNX_SEARCH_RESULT_TOTAL').' '.$resultCount;
    }


    function render($outputMode = NULL, $skipChilds = false)
    {
        if ( $this->_application->isAdmin() ) {
            return;
        }

        if (!$this->getAttribute('skipFormTag')) {
            parent::render_html_onStart();
        }

        parent::render($outputMode, $skipChilds);

        if (!$this->getAttribute('skipFormTag')) {
            parent::render_html_onEnd();
        }
    }
}

class pinax_components_Search_render extends pinax_components_render_Render
{
    function getDefaultSkin()
    {
        $skin = <<<EOD
<tal:block>
    <div class="formItem">
        <label for="search" tal:content="Component/label" />
        <input type="text" name="search" id="search" value="" size="20" tabindex="22" class="long" tal:attributes="value Component/value"/>
        <input type="submit" class="submitButton" value="cerca" tal:attributes="value Component/buttonLabel"/>
        <p tal:content="structure Component/comment" />
    </div>

    <tal:block tal:condition="php: !is_null(Component.result)" >
        <div id="searchResult" tal:condition="php: count(Search.result)">
            <h2 tal:content="structure Component/comment1"/>
            <p tal:content="structure Component/total"/>
            <ul>
                <li tal:repeat="item Component/result"><strong tal:content="structure item/__url__"></strong>
                <p class="small" tal:content="structure item/description" />
                </li>
            </ul>
        </div>
        <p tal:condition="php: !count(Component.result)" tal:content="php:__T('MW_NO_RECORD_FOUND')" />
    </tal:block>
</tal:block>
EOD;
        return $skin;
    }
}
