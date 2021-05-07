<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_RecordDetail extends pinax_components_ComponentContainer
{
    protected $recordId;
    protected $ar;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('dataProvider',    true,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('idName',        false,     'id',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('routeUrl',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('ogTitle',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('ogDescription',         false,    NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('modifyBreadcrumbs',         false,    true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('processCell',     false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('processCellParams',    false,    NULL,        COMPONENT_TYPE_STRING);

        parent::init();
    }


    function process()
    {
        $dataProvider = &$this->getAttribute('dataProvider');
        if (!$dataProvider) {
            throw pinax_exceptions_GlobalException::missingAttributeInComponent($this->_tagname, $this->getId(), 'dataProvider');
        }

        $this->recordId = pinax_Request::get($this->getAttribute('idName'), NULL);
        $this->ar = $dataProvider->load($this->recordId);

        $processCell = pinax_ObjectFactory::createObject($this->getAttribute('processCell'), $this->_application);
        if ($processCell) {
            $ar = &$this->ar;
            call_user_func_array(array($processCell, 'renderCell'), array($ar, $this->getAttribute('processCellParams')));
        }
        $this->_content = pinax_ObjectFactory::createObject('pinax.components.RecordDetailVO', $this->ar);

        $ogTitle = $this->getAttributeFieldValue('ogTitle');
        if ($ogTitle) {
            $title = html_entity_decode(strip_tags(str_replace('<br', ' <br', $this->ar->{$ogTitle})));
            pinax_ObjectValues::set('pinax.og', 'title', $title);
            if ($this->getAttribute('modifyBreadcrumbs')) {
                $evt = array('type' => PNX_EVT_BREADCRUMBS_UPDATE, 'data' => $title);
                $this->dispatchEvent($evt);

                $evt = array('type' => PNX_EVT_PAGETITLE_UPDATE, 'data' => $title);
                $this->dispatchEvent($evt);
            }
        }

        $ogDescription = $this->getAttributeFieldValue('ogDescription');
        if ($ogDescription) {
            $description = html_entity_decode(strip_tags(str_replace('<br', ' <br', $this->ar->{$ogDescription})));
            pinax_ObjectValues::set('pinax.og', 'description', $description);
        }

        $this->_content->__url__ = !is_null( $this->getAttribute( 'routeUrl' ) ) ? pinax_helpers_Link::makeURL( $this->getAttribute( 'routeUrl' ), $this->_content) : '';
        parent::process();
    }

    private function getAttributeFieldValue($field)
    {
        $attribute = $this->getAttribute($field);
        if (is_null($attribute)) {
            return null;
        }

        $fields = explode(',', $this->getAttribute($field));
        foreach ($fields as $f) {
            $value = $this->ar->{$f};
            if (!empty($value)) {
                return $f;
            }
        }

        return null;
    }

    function getContent()
    {
        if (count($this->childComponents))
        {
            for ($i=0; $i<count($this->childComponents);$i++)
            {
                $id = preg_replace('/^'.$this->getId().'\-/', '', $this->childComponents[$i]->getId());
                $r = $this->childComponents[$i]->getContent();
                $this->_content->{$id} = $r;
            }
        }

        return $this->_content;
    }


    function loadContent($id, $bindTo = '')
    {
        $id = preg_replace('/^'.$this->getId().'\-/', '', $id);
        return $this->_content->{$id};
    }

    public function getRecordId()
    {
        return $this->recordId;
    }

    public function getRecord()
    {
        return $this->ar;
    }
}

if (!class_exists('pinax_components_RecordDetailVO', false)) {
    class pinax_components_RecordDetailVO
    {
        private $content;
        function __construct( $content )
        {
            $this->content = $content;
        }

        public function __get($name)
        {
            $value = $this->content->{$name};
            if (is_string($value) && strrpos($value, '<')!==false) {
                // TODO migliorare
                $value = pinax_helpers_Link::parseInternalLinks($value);
            }
            return $value;
        }

        public function __isset($name)
        {
            $filedType = $this->content->getFieldType($name);
            return !empty($filedType);
        }
    }
}

if (!class_exists("pinax_components_RecordDetail_render", false))
{
    class pinax_components_RecordDetail_render extends pinax_components_render_Render
    {
        function getDefaultSkin()
        {
        $skin = <<<EOD
<span>ERROR: custom skin required<br /></span>
EOD;
        return $skin;
        }
    }
}
