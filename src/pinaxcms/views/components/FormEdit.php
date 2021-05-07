<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_FormEdit extends pinax_components_Form
{
    protected $data = '{}';
    protected $pageTitleModifiers = array();

    function init()
    {
        // define the custom attributes
        $this->defineAttribute( 'customValidation', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute( 'ajaxParams', false, NULL, COMPONENT_TYPE_STRING);
        $this->defineAttribute( 'newCode', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute( 'initJS', false, true, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute( 'datetimepickerInclude', false, true, COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();

        $this->setAttribute( 'addValidationJs', false );
        $this->setAttribute('cssClass', ' formEdit', true);
    }

    public function setData($data)
    {
        $this->data = is_array($data) || is_object($data) ? json_encode($data) : $data;
        $this->_content = is_object($data) ? get_object_vars($data) : $data;
    }


    public function resetPageTitleModifier()
    {
        $this->pageTitleModifiers = array();
    }

    public function addPageTitleModifier(pinaxcms_views_components_FormEditPageTitleModifierVO $modifier)
    {
        $this->pageTitleModifiers[] = $modifier;
    }

    public function process()
    {
        parent::process();
        $this->changePageTitle();
    }

    // public function render_html_onStart()
    // {
    //     $this->setAttribute( 'addValidationJs', false );
    //     $this->setAttribute('cssClass', ' formEdit', true);
    //     parent::render_html_onStart();
    // }

    public function render_html_onEnd($value='')
    {
        parent::render_html_onEnd();


        $corePath = __Paths::get('PINAX_CMS_STATIC_DIR');
        $jQueryPath = $corePath.'jquery/';

        $languageCode = $this->_application->getLanguage();
        $language = $languageCode.'-'.strtoupper($languageCode);
        $imageResizer = pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageResizeTemplateUrl(
                                        __Config::get('THUMB_WIDTH'),
                                        __Config::get('THUMB_HEIGHT'),
                                        __Config::get('ADM_THUMBNAIL_CROP'),
                                        __Config::get('ADM_THUMBNAIL_CROPPOS'));

        $googleApiKey = __Config::get('pinax.maps.google.apiKey');

        if ($this->getAttribute('newCode')) {
            $formEditPath = $corePath.'formEdit2/';

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $corePath.'underscore/underscore-min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEdit.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditStandard.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditCheckbox.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditRepeat.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditDate.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditDateTime.js' ), 'head');

            if($this->getAttribute('datetimepickerInclude'))
            {
                $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ), 'head');
                $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ), 'head');
                $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ), 'head');
            }

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditColorPicker.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditGUID.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditSelectFrom.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditTINYMCE.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditMediaPicker.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditFile.js' ), 'head');
            //$this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'fineuploader.jquery/jquery.fineuploader.js' ), 'head');
            //$this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'fineuploader.jquery/fineuploader.css' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditPermission.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditPhotoGalleryCategory.js' ), 'head');

            if ($googleApiKey) {
                $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditGoogleMaps.js' ), 'head');
                $this->addOutputCode(pinax_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?key='.$googleApiKey), 'head');
            }

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditCmsPagePicker.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditSelectPageType.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditUrl.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditModalPage.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $corePath.'locale/cms/'.$language.'.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditRecordPicker.js' ), 'head');

            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $customValidation = $this->getAttribute('customValidation');
            if ( $customValidation ) {
                $customValidation = 'customValidation: "'.$customValidation.'",';
            }

            $tinyMceUrls = json_encode($this->getTinyMceUrls());
            $tinyMceControllerName = __Config::get('pinaxcms.tinyMCE.link.controllerName');
            $staticDir = __Paths::get('STATIC_DIR');
	    $readOnly = $this->getAttribute('readOnly');


            $jsCode = <<< EOD
jQuery(function(){
    if ( Pinax.tinyMCE_options )
    {
        Pinax.tinyMCE_options.urls = $tinyMceUrls;
        Pinax.tinyMCE_options.controllerName = "$tinyMceControllerName";
    }

    PinaxLocale.FormEdit.static = "$staticDir";

    var myFormEdit = Pinax.oop.create("pinax.FormEdit", '$id', {
        AJAXAction: "$AJAXAtion",
        mediaPicker: $mediaPicker,
        imageResizer: "$imageResizer",
        formData: $this->data,
        $customValidation
        lang: PinaxLocale.FormEdit,
        readOnly: "$readOnly"
    });
});
EOD;
        } else {
            $formEditPath = $corePath.'formEdit/';

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $corePath.'underscore/underscore-min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEdit.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditTINYMCE.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditFile.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditMediaPicker.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditGUID.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditColorPicker.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditValuesPreset.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditRecordPicker.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditDate.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditDatetime.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.it.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-datetimepicker-master/css/datetimepicker.css' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'bootstrap-colorpicker/js/bootstrap-colorpicker.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'bootstrap-colorpicker/css/bootstrap-colorpicker.min.css' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $corePath.'locale/cms/'.$language.'.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'dropzone/dropzone.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'dropzone/css/basic2.css' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'jquery.validVal-packed.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditCmsPagePicker.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditSelectFrom.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditPermission.js' ), 'head');

            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ), 'head');
            $this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditCheckbox.js' ), 'head');

            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditSelectPageType.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditPhotoGalleryCategory.js' ), 'head');
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditImageHotspot.js' ), 'head');

            if ($googleApiKey) {
                $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditGoogleMaps.js' ), 'head');
                $this->addOutputCode(pinax_helpers_JS::linkJSfile( 'http://maps.google.com/maps/api/js?key='.$googleApiKey), 'head');
            }

            $id = $this->getId();

            $mediaPicker = $this->getMediaPickerUrl();
            $AJAXAtion = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

            $initJS = $this->getAttribute('initJS') ? 'true' : 'false';
            $customValidation = $this->getAttribute('customValidation');
            if ( $customValidation ) {
                $customValidation = 'customValidation: "'.$customValidation.'",';
            }

            $tinyMceUrls = json_encode($this->getTinyMceUrls());
            $tinyMceControllerName = __Config::get('pinaxcms.tinyMCE.link.controllerName');
            $staticDir = __Paths::get('STATIC_DIR');

            $jsCode = <<< EOD
jQuery(function(){
    if ( Pinax.tinyMCE_options )
    {
        Pinax.tinyMCE_options.urls = $tinyMceUrls;
        Pinax.tinyMCE_options.controllerName = "$tinyMceControllerName";
    }

    PinaxLocale.FormEdit.static = "$staticDir";

    var ajaxUrl = "$AJAXAtion";
    if (initJs = $initJS) {
        jQuery( "#$id" ).PinaxFormEdit({
            AJAXAction: ajaxUrl ? ajaxUrl : Pinax.ajaxUrl,
            mediaPicker: $mediaPicker,
            imageResizer: "$imageResizer",
            formData: $this->data,
            $customValidation
            lang: PinaxLocale.FormEdit
        });
    } else {
        jQuery( "#$id" ).hide();
    }
});
EOD;
        }

        if (empty($googleApiKey)) {
            $this->addOutputCode( pinax_helpers_JS::linkJSfile( $formEditPath.'FormEditLeafletMaps.js' ), 'head');
            $this->addOutputCode('<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>', 'head');
            $this->addOutputCode('<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>', 'head');
        }

        $this->addOutputCode(pinax_helpers_JS::JScode( $jsCode ), 'head');
    }

    public function getAjaxUrl()
    {
        $queryString = [];
        $ajaxParams = $this->getAttribute('ajaxParams');
        if ($ajaxParams) {
            $ajaxParams = explode(',', $ajaxParams);
            array_walk($ajaxParams, function($item) use (&$queryString) {
                $queryString[$item] = __Request::get($item);
            });
        }

        $queryString['pageId'] = $this->_application->getPageId();
        $queryString['ajaxTarget'] = $this->getId();
        $queryString['action'] = '';

        return 'ajax.php?'.http_build_query($queryString);
    }


    protected function getMediaPickerUrl()
    {
        return '"'.pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaPickerUrl().'"';
    }

    protected function getTinyMceUrls()
    {
        return array(
                        'ajaxUrl' => PNX_HOST.'/'.$this->getAjaxUrl(),
                        'mediaPicker' => pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaPickerUrl(),
                        'mediaPickerTiny' => pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaPickerUrl(true),
                        'imagePickerTiny' => pinaxcms_Pinaxcms::getMediaArchiveBridge()->mediaPickerUrl(true, 'IMAGE'),
                        'imageResizer' => pinaxcms_Pinaxcms::getMediaArchiveBridge()->imageResizeTemplateUrl(),
                        'root' => PNX_HOST.'/',
            );
    }


    protected function changePageTitle()
    {
        if ( method_exists( $this->_parent, "getAction" ) )
        {
            $currentAction = $this->_parent->getAction();
            foreach($this->pageTitleModifiers as $modifier)
            {
                if ($currentAction==$modifier->action) {
                    $newTitle = $modifier->label;
                    $newSubtitle = $modifier->fieldSubtitle && $this->_content[$modifier->fieldSubtitle] ? $this->_content[$modifier->fieldSubtitle] : '';
                    if ($modifier->isNew) {
                        if ($modifier->idField &&
                                ($this->_content[$modifier->idField]!='0' && isset($this->_content[$modifier->idField]))) {
                            continue;
                        }
                    }

                    if (preg_match("/\{i18n\:.*\}/i", $newTitle))
                    {
                        $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $newTitle);
                        $newTitle = pinax_locale_Locale::get($code, $newSubtitle);
                    }

                    $evt = array('type' => PNX_EVT_PAGETITLE_UPDATE, 'data' => $newTitle);
                    $this->dispatchEvent($evt);
                    break;
                }
            }
        }
    }

    function loadContent($name, $bindTo = '')
	{
        if (empty($bindTo)) {
			$bindTo = $name;
		}

		return pinax_Request::get($bindTo, isset($this->_content[$bindTo]) ? $this->_content[$bindTo] : null);
	}


    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );

        $oldcounter = $counter;
        foreach ($node->childNodes as $n ) {
            if ( $n->nodeName == "cms:pageTitleModifier" ) {
                $action = $n->hasAttribute('action') ? $n->getAttribute('action') : '';
                $label = $n->hasAttribute('label') ? $n->getAttribute('label') : '';
                $new = $n->hasAttribute('new') ? $n->getAttribute('new') : 'false';
                $field = $n->hasAttribute('field') ? $n->getAttribute('field') : '';
                $idField = $n->hasAttribute('idField') ? $n->getAttribute('idField') : '__id';
                if ( $action && $label )
                {
                    $compiler->_classSource .= '$n'.$oldcounter.'->addPageTitleModifier('.
                                'new pinaxcms_views_components_FormEditPageTitleModifierVO("'.$action.'", "'.$label.'", '.$new.', "'.$idField.'", "'.$field.'"));';
                }
            } else {
                $counter++;
                $compiler->compileChildNode($n, $registredNameSpaces, $counter, $oldcounter, $idPrefix);
            }
        }

        return false;
    }
}

class pinaxcms_views_components_FormEditPageTitleModifierVO
{
    public $action;
    public $label;
    public $isNew;
    public $idField;
    public $fieldSubtitle;

    public function __construct($action, $label, $isNew, $idField, $fieldSubtitle) {
        $this->action = $action;
        $this->label = $label;
        $this->isNew = $isNew;
        $this->idField = $idField;
        $this->fieldSubtitle = $fieldSubtitle;
    }
}


