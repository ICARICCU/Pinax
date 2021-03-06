/**
 * TinyMCE Image plugin, based on TinyMCE-advimage plugin by Moxiecode Systems AB.
 *
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


var eventPos;

$(window).unload(function(){
    Pinax.events.unbind("pinaxcms.onSetMediaPicker", eventPos);
});


function convertURL(url, node, on_save) {
    return url;
}
function trimSize(size) {
    return size.replace(/([0-9\.]+)px|(%|in|cm|mm|em|ex|pt|pc)/, '$1$2');
}

function init() {
    var f = document.forms[0], formObj = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, elm = ed.selection.getNode();
    var $picker = $("#picker");
    var pickerHeight = Math.max($picker.parent().height(), $(window.top).height() - 250);
    $picker.height(pickerHeight);
    $picker.attr("src", parent.Pinax.tinyMCE_options.urls.imagePickerTiny);

    eventPos = Pinax.events.on("pinaxcms.onSetMediaPicker", function(event){
        var message = event.message;
        update( message.id, message.fileName, message.title, message.thumbnail || message.src, message.width, message.height );
    });

    tinyMCEPopup.resizeToInnerSize();
    renderImageStyle();
    renderImageSized();

    if (elm.nodeName == 'IMG') {
        var src = dom.getAttrib(elm, 'src');
        src = convertURL(src, elm, true);
        // Setup form data
        var style = dom.getAttrib(elm, 'style');
        var search = src.split("?")[1];
        var els = search.split("&");
        formObj.imgid.value = els[0].split("=")[1];
        var tn = formObj.src.value = src;
        formObj.alt.value    = dom.getAttrib(elm, 'data-caption') || dom.getAttrib(elm, 'title');
        formObj.title.value  = dom.getAttrib(elm, 'title');
        formObj.zoom.checked = dom.getAttrib(elm, 'data-zoom') == '1';
        formObj.caption.checked = dom.getAttrib(elm, 'data-caption') != '';

        var border = dom.getStyle(elm, 'border' );
        if (border!='')
        {
            formObj.border.value = trimSize(border.split(' ')[0]);
        }
        else
        {
            formObj.border.value = '';
        }

        var margin = getStyle(elm, 'margin');
        if (margin!='')
        {
            margin = margin.split(' ');
            formObj.vspace.value = trimSize(margin[0]);
            formObj.hspace.value = trimSize(margin[margin.length > 1 ? 1 : 0]);
        }
        else
        {
            formObj.vspace.value = '';
            formObj.hspace.value = '';
        }

        formObj.cssclass.value = dom.getAttrib(elm, 'class');
        var w = formObj.orw.value = formObj.width.value  = trimSize(dom.getStyle(elm, 'width'));
        var h = formObj.orh.value = formObj.height.value = trimSize(dom.getStyle(elm, 'height'));
        formObj.style.value  = style;
        with (f.thumbnail) {
            width = 100;
            height = 100;
            src = tn.indexOf('http://')>-1 ? tn : parent.Pinax.tinyMCE_options.urls.root+tn;
        }

        selectByValue(f, 'align', dom.getStyle(elm, 'float'));

        updateStyle();
    }
}



function update(imgid,url,t,tn,w,h) {
    var formObj = document.forms[0];
    formObj.imgid.value = imgid;
    with (formObj) {
        title.value = t;
        alt.value = t;
        width.value = orw.value = w;
        height.value = orh.value = h;
        with (thumbnail) {
            if (w/h > 1) {
                width = 100;
                height = h/w*100;
            }
            else {
                height = 100;
                width = w/h*100;
            }
            src = tn.indexOf('http')===0 ? tn : parent.Pinax.tinyMCE_options.urls.root+tn;
        }
    }
    updateStyle();
}

function setAttrib(elm, attrib, value) {
    var ed = tinyMCEPopup.editor, dom = ed.dom;
    var formObj = document.forms[0];
    var valueElm = formObj.elements[attrib];

    if (typeof(value) == "undefined" || value == null) {
        value = "";

        if (valueElm)
            value = valueElm.value;
    }

    if (value != "") {
        dom.setAttrib(elm, attrib, value);
    } else
        elm.removeAttribute(attrib);
}

function makeAttrib(attrib, value) {
    var formObj = document.forms[0];
    var valueElm = formObj.elements[attrib];

    if (typeof(value) == "undefined" || value == null) {
        value = "";

        if (valueElm)
            value = valueElm.value;
    }

    if (value == "")
        return "";
    return ' ' + attrib + '="' + value + '"';
}

function insertAction() {
    var ed = tinyMCEPopup.editor, dom = ed.dom;
    var formObj = document.forms[0];
    tinyMCEPopup.restoreSelection();

    // Fixes crash in Safari
    if (tinymce.isWebKit)
        ed.getWin().focus();

    if (formObj.imgid.value) {
        var src = parent.Pinax.tinyMCE_options.urls.imageResizer
                        .replace('#id#', formObj.imgid.value)
                        .replace('#w#', formObj.width.value)
                        .replace('#h#', formObj.height.value);
        formObj.alt.value = formObj.alt.value || formObj.title.value;
        src = convertURL(src, tinyMCE.imgElement);

        var elm = ed.selection.getNode();
        if (elm && elm.nodeName == 'IMG') {
            setAttrib(elm, 'src', src);
            setAttrib(elm, 'alt', formObj.title.value);
            setAttrib(elm, 'title');
            setAttrib(elm, 'style');
            setAttrib(elm, 'class', formObj.cssclass.value);
            setAttrib(elm, 'data-zoom', formObj.zoom.checked ? '1' : '');
            setAttrib(elm, 'data-caption', formObj.caption.checked ? formObj.alt.value : '');
        } else {
            var html = "<img";

            html += makeAttrib('src', src);
            html += makeAttrib('alt', formObj.title.value);
            html += makeAttrib('title');
            html += makeAttrib('style');
            html += makeAttrib('class', formObj.cssclass.value);
            html += makeAttrib('data-zoom', formObj.zoom.checked ? '1' : '');
            html += makeAttrib('data-caption', formObj.caption.checked ? formObj.alt.value : '');
            html += " />";

            ed.execCommand("mceInsertContent", false, html, {skip_undo : 1});
            ed.undoManager.add();
        }
    }

    tinyMCEPopup.editor.execCommand('mceRepaint');
    tinyMCEPopup.editor.focus();
    tinyMCEPopup.close();
}

function cancelAction() {
    tinyMCEPopup.close();
}



function changeCssClass(elm) {
    if (elm.selectedIndex !=0)
    {
        var formObj = document.forms[0];
        formObj.cssclass.value = elm.value;
        elm.selectedIndex = 0;
    }
}

function updateStyle() {
    var ed = tinyMCEPopup.editor, dom = ed.dom;
    var formObj = document.forms[0];
    var st = dom.parseStyle(formObj.style.value);

    if (ed.settings.inline_styles) {
        st['width'] = formObj.width.value == '' ? '' : formObj.width.value + "px";
        st['height'] = formObj.height.value == '' ? '' : formObj.height.value + "px";
        st['border'] = formObj.border.value == '' ? '' : formObj.border.value + "px solid";
        if (formObj.vspace.value != '' || formObj.hspace.value != '')
        {
            st['margin'] = formObj.vspace.value == '' ? '0' : formObj.vspace.value + "px"
            st['margin'] += formObj.hspace.value == '' ? ' 0' : ' '+formObj.hspace.value + "px";
            st['margin'] += formObj.vspace.value == '' ? ' 0' : ' '+formObj.vspace.value + "px"
            st['margin'] += formObj.hspace.value == '' ? ' 0' : ' '+formObj.hspace.value + "px";
        }
        else
        {
            delete st['margin'];
        }

    } else {
        st['width'] = st['height'] = st['border'] = null;

        if (st['margin-top'] == st['margin-bottom'])
            st['margin-top'] = st['margin-bottom'] = null;

        if (st['margin-left'] == st['margin-right'])
            st['margin-left'] = st['margin-right'] = null;
    }

    formObj.style.value = dom.serializeStyle(st);
}

function styleUpdated() {
    var ed = tinyMCEPopup.editor, dom = ed.dom;
    var formObj = document.forms[0];
    var st = dom.parseStyle(formObj.style.value);

    if (st['width'])
        formObj.width.value = st['width'].replace('px', '');

    if (st['height'])
        formObj.height.value = st['height'].replace('px', '');

    if (st['margin-top'] && st['margin-top'] == st['margin-bottom'])
        formObj.vspace.value = st['margin-top'].replace('px', '');

    if (st['margin-left'] && st['margin-left'] == st['margin-right'])
        formObj.hspace.value = st['margin-left'].replace('px', '');

    if (st['border-width'])
        formObj.border.value = st['border-width'].replace('px', '');
}

function changeHeight() {
    var formObj = document.forms[0];

    var temp = (formObj.width.value / formObj.orw.value) * formObj.orh.value;
    formObj.height.value = temp.toFixed(0);
    updateStyle();
}

function changeWidth() {
    var formObj = document.forms[0];

    var temp = (formObj.height.value / formObj.orh.value) * formObj.orw.value;
    formObj.width.value = temp.toFixed(0);
    updateStyle();
}

function changeSize(elm) {
    if (elm.selectedIndex !=0)
    {
        var formObj = document.forms[0];
        var values = elm.value.split(',');
        if (values[0] && values[1]) {
            formObj.width.value = values[0];
            formObj.height.value = values[1];
            updateStyle();
        } else if (values[0]) {
            formObj.width.value = values[0];
            changeHeight()
        } else if (values[1]) {
            formObj.height.value = values[1];
            changeWidth()
        }
        elm.selectedIndex = 0;
    }
}

function getSelectValue(form_obj, field_name) {
    var elm = form_obj.elements[field_name];

    if (elm == null || elm.options == null)
        return "";

    return elm.options[elm.selectedIndex].value;
}

function renderImageStyle() {
    var styles = getImageStyles();

    $('#cssClassList').append(renderSelectOptions(styles));
}

function getImageStyles()
{
    var ed = tinyMCEPopup.editor;
    return parent.Pinax.tinyMCE_imgStyles ? parent.Pinax.tinyMCE_imgStyles : [
        {"value":"left", "label": ed.translate('PNX_image.css_left')},
        {"value":"right", "label": ed.translate('PNX_image.css_right')},
        {"value":"center", "label": ed.translate('PNX_image.css_center')},
        {"value":"left noBorder", "label": ed.translate('PNX_image.css_left_noborder')},
        {"value":"right noBorder", "label": ed.translate('PNX_image.css_right_noborder')},
        {"value":"center noBorder", "label": ed.translate('PNX_image.css_center_noborder')}
    ]
}

function renderImageSized() {
    var sizes = parent.Pinax.tinyMCE_imgSizes;
    if (sizes) {
        $('#sizeList').append(renderSelectOptions(sizes));
    } else {
        $('#sizeList').hide();
    }
}

function renderSelectOptions(items) {
    var aSel = [];
    aSel.push('<option value="">-</option>');
    var styles = getImageStyles();
    $(items).each(function(index, el){
        aSel.push('<option value="'+el.value+'">'+el.label+'</option>');
    });
    return aSel.join("");
}


tinyMCEPopup.onInit.add(init, null);
