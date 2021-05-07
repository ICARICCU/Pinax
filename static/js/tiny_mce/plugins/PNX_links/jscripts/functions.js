/**
 * TinyMCE Image plugin, based on TinyMCE-advlink plugin by Moxiecode Systems AB.
 *
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/* Functions for the advlink plugin popup */

var currentSize = {w: 420, h: 530 };
var eventPos;

$(window).unload(function(){
    Pinax.events.unbind("pinaxcms.onSetMediaPicker", eventPos);
});


function renderRepeaterLinks() {
    var aSel = [];
    aSel.push('<select id="repeaterLink" style="width: 100%; display:none">');
    aSel.push('<option value="">---</option>');
    parent.$("fieldset[data-anchor=true]").each(function(index, el){
        var $el = $(el);
        var id = $(el).attr("id");
        var title = $el.find("legend").text();
        var titlePrefix = "";
        var $parent = $el.parent().closest('fieldset');
        if ($parent.prop('tagName')==="FIELDSET" && $parent.data("type")==="repeat") {
            titlePrefix = $parent.find("legend").first().text()+": "+(index+1)+" > ";
        }
        $el.find('div.GFERowContainer').each(function(index, el){
            var $el = $(el);
            var itemCode = index+1;
            var $elTitle = $el.find("[data-anchor-title]");
            var itemTitle = title + ": " + ($elTitle.length ? $elTitle.val() : itemCode);
            var $elCode = $el.find("[data-anchor-code]");
            itemCode = id+":"+($elCode.length ? $elCode.val() : itemCode);
            aSel.push('<option value="repeater:'+itemCode+'">'+titlePrefix+itemTitle+'</option>');
        });
    });
    aSel.push('</select>');
    $('#internalLinks').append(aSel.join(""));
}

function renderInternalLinks(callback) {
    if ( parent.Pinax.tinyMCE_options.pnx_links) {
        var intLinks = parent.Pinax.tinyMCE_options.pnx_links;
        var aSel = ['<select id="internalLink" style="width: 100%" disabled="disabled">'];
    	aSel.push('<option value="">---</option>');
    	for (var i=0;i<intLinks.internal.length;i++) {
    	 aSel.push('<option value="'+intLinks.internal[i].link+'">'+intLinks.internal[i].name+'</option>');
    	}
    	aSel.push('</select>');

        $('#internalLinks').append(aSel.join(""));
        callback()
    } else {
        loadInternalLinks(callback);
    }
}

function loadInternalLinks(callback) {
    $.ajax(parent.Pinax.tinyMCE_options.urls.ajaxUrl + "&controllerName=" + parent.Pinax.tinyMCE_options.controllerName, {
        dataType: 'json',
        success: function (intLinks) {
            parent.Pinax.tinyMCE_options.pnx_links = intLinks;
            renderInternalLinks(callback);
        }
    });
}

function setLinkType(type) {
	var wSize = {w: 420, h: 530 }
	if (type=="1")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("internalLink").disabled = "";
        if (parent.Pinax.tinyMCE_queryStringEnabled) {
            document.getElementById("anchorQueryGroup").style.display = "table-row";
            document.getElementById("anchor").disabled = "";
        } else {
            document.getElementById("anchorQueryGroup").style.display = "none";
            document.getElementById("anchor").disabled = "disabled";
        }
        document.getElementById("repeaterLink").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
	}
	else if (type=="2")
	{
		document.getElementById("protocol").disabled = "";
		document.getElementById("linkUrl").disabled = "";
        document.getElementById("anchorQueryGroup").style.display = "none";
        document.getElementById("anchor").disabled = "disabled";
        document.getElementById("repeaterLink").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
	}
	else if (type=="3")
	{
        var $picker = $("#picker");
        if (!$picker.attr("src")) {
            $picker.attr("src", parent.Pinax.tinyMCE_options.urls.mediaPickerTiny);
            eventPos = Pinax.events.on("pinaxcms.onSetMediaPicker", function(event){
                var message = event.message;
                update( message.id, message.title );
            });

        }

		document.getElementById("pickerPanel").style.display = "block";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("anchor").disabled = "disabled";
        document.getElementById("anchorQueryGroup").style.display = "none";
        document.getElementById("repeaterLink").style.display = "none";
		document.getElementById("protocol").disabled = "disabled";
        wSize = {w: $(parent.window).width() - 80, h: 530 }
        $('#pickerPanel').width(wSize.w-430);
	}
	else if (type=="4")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("anchor").disabled = "disabled";
        document.getElementById("anchorQueryGroup").style.display = "none";
		document.getElementById("repeaterLink").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
	}
	else if (type=="5")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "disabled";
		document.getElementById("internalLink").style.display = "none";
		document.getElementById("anchor").disabled = "disabled";
        document.getElementById("anchorQueryGroup").style.display = "none";
		document.getElementById("glossaryLinks").style.display = "inline";
		document.getElementById("pickerPanel").style.display = "none";
	}
	else if (type=="6")
	{
		document.getElementById("protocol").disabled = "disabled";
		document.getElementById("linkUrl").disabled = "";
		document.getElementById("internalLink").disabled = "disabled";
		document.getElementById("anchor").disabled = "disabled";
        document.getElementById("anchorQueryGroup").style.display = "none";
		document.getElementById("repeaterLink").style.display = "none";
		document.getElementById("pickerPanel").style.display = "none";
	}
    else if (type=="7")
    {
        document.getElementById("protocol").disabled = "disabled";
        document.getElementById("linkUrl").disabled = "disabled";
        document.getElementById("internalLink").style.display = "none";
		document.getElementById("anchor").disabled = "disabled";
        document.getElementById("anchorQueryGroup").style.display = "none";
		document.getElementById("repeaterLink").style.display = "inline";
        document.getElementById("pickerPanel").style.display = "none";
    }

	resizeMe( wSize );
}

function resizeMe( newSize ) {
    var winID = tinyMCEPopup.id;
    var wm = tinyMCEPopup.editor.windowManager;

    wm.resizeBy(newSize.w - currentSize.w, newSize.h - currentSize.h, winID);
	var left = parseInt( tinymce.DOM.getStyle( winID, 'left' ).replace( 'px', '' ) );
	tinymce.DOM.setStyle( winID, 'left', ( left - (newSize.w - currentSize.w) / 2 ) + 'px' );
	currentSize = newSize;
}

function init() {
	tinyMCEPopup.resizeToInnerSize();
    renderRepeaterLinks();
	var formObj = document.forms[0];
	var inst = tinyMCEPopup.editor;
	var elm = inst.selection.getNode();
	var action = "insert";
    var linkType = 0;

	elm = inst.dom.getParent(elm, "A");
	if (elm != null && elm.nodeName == "A")
		action = "update";

	formObj.insert.value = tinyMCEPopup.getLang(action, 'Insert', true);

    if (action == "update") {
		var href = inst.dom.getAttrib(elm, 'href');
		href = convertURL(href, elm, true);

		// Setup form data

		var protocol = 'http://';
		if (href.indexOf("internal:")==0)
		{
			linkType = 1;
			setFormValue('linkUrl', '');
		}
		else if (href.indexOf("media:")==0)
		{
			linkType = 3;
			var mediaInfo = href.split(':');
			setFormValue('linkUrl', mediaInfo[2]);
			setFormValue('mediaId', mediaInfo[1]);
			setFormValue('mediaTitle', mediaInfo[2]);
		}
		else if (href.indexOf("#")==0)
		{
			linkType = 4;
			setFormValue('linkUrl', href);
		}
		else if (href.indexOf("repeater:")==0)
		{
			linkType = 7;
			setFormValue('linkUrl', '');
            setFormValue('repeaterLink', href);
		}
		else
		{
			var regExp = new RegExp("(http://|https://|ftp://|mailto:)(.*)", "gi");
			linkType = href.match( regExp ) ? 2  : 6 ;
			if ( linkType == 2 )
			{
				protocol = href.replace(regExp, "$1");
			    setFormValue('linkUrl', href.replace(protocol,""));
            }
			else
			{
				setFormValue('linkUrl', href );
			}
		}



		setFormValue('linkType', String(linkType));

        setFormValue('protocol', protocol);
		// setFormValue('internalLink', linkType==1 ? href:"");
        // setLinkType(String(linkType));
		setFormValue('title', inst.dom.getAttrib(elm, 'title'));
		setFormValue('id', inst.dom.getAttrib(elm, 'id'));
		setFormValue('style', inst.dom.getAttrib(elm, 'style'));
		setFormValue('cssclass', inst.dom.getAttrib(elm, 'class'));
		setFormValue('dir', inst.dom.getAttrib(elm, 'dir'));
		setFormValue('hreflang', inst.dom.getAttrib(elm, 'hreflang'));
		setFormValue('lang', inst.dom.getAttrib(elm, 'lang'));
		setFormValue('charset', inst.dom.getAttrib(elm, 'charset'));
        if (parent.Pinax.tinyMCE_allowLinkTarget) {
            document.forms[0].elements['relExternal'].checked = tinymce.DOM.getAttrib(elm, 'target') == "_blank";
        } else {
			document.forms[0].elements['relExternal'].checked = tinymce.DOM.getAttrib(elm, 'rel') == "external";
        }
		setFormValue('tabindex', inst.dom.getAttrib(elm, 'tabindex', typeof(elm.tabindex) != "undefined" ? elm.tabindex : ""));
		setFormValue('accesskey', inst.dom.getAttrib(elm, 'accesskey', typeof(elm.accesskey) != "undefined" ? elm.accesskey : ""));
	}

    var href = linkType==1 ? href:""
    var callback = function() {
		if (href.indexOf('?') != -1 || href.indexOf('#') != -1) {
			var splitChar = href.indexOf('?') != -1 ? '?' : '#';
			var parts = href.split(splitChar);
			href = parts[0];
			$('#anchor').val(splitChar+parts[1]);
		}
		setFormValue('internalLink', linkType==1 ? href:"");
        setTimeout(function(){
            setLinkType(String(linkType));
        }, 100);
    }

    renderInternalLinks(callback);
	window.focus();
}

function setFormValue(name, value) {
	document.forms[0].elements[name].value = value;
}



function convertURL(url, node, on_save) {
	var userURL = $('#linkUrl').val();
	var protocol = userURL.match(/^(http|https|ftp|mailto):\/\//);
	if (protocol != null) {
		userURL = userURL.replace(protocol[0], '');
		$('#protocol').val(protocol[0]);
		$('#linkUrl').val(userURL);

		return protocol[0] + userURL;
	}

	return url;
	//return eval("tinyMCEPopup.windowOpener." + tinyMCE.settings['urlconverter_callback'] + "(url, node, on_save);");
}

function setAttrib(elm, attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib.toLowerCase()];
	var dom = tinyMCEPopup.editor.dom;

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	// Clean up the style
	if (attrib == 'style')
		value = dom.serializeStyle(dom.parseStyle(value), 'a');

	dom.setAttrib(elm, attrib, value);
}

function insertAction() {
	var inst = tinyMCEPopup.editor;
	var elm, elementArray, i;

	elm = inst.selection.getNode();
	elm = inst.dom.getParent(elm, "A");

	tinyMCEPopup.execCommand("mceBeginUndoLevel");

	// Create new anchor elements
	if (elm == null) {
		inst.getDoc().execCommand("unlink", false, null);
		tinyMCEPopup.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});

		elementArray = tinymce.grep(inst.dom.select("a"), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
		for (i=0; i<elementArray.length; i++)
			setAllAttribs(elm = elementArray[i]);
	} else
		setAllAttribs(elm);

	// Don't move caret if selection was image
	if (elm && (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG')) {
		inst.focus();
		inst.selection.select(elm);
		inst.selection.collapse(0);
		tinyMCEPopup.storeSelection();
	}

	tinyMCEPopup.execCommand("mceEndUndoLevel");
	tinyMCEPopup.close();
}



function setAllAttribs(elm) {
	var formObj = document.forms[0];
	var linkUrlObj = document.getElementById("linkUrl");
    var href;
    switch (formObj.linkType.value) {
    case "1":
		href = formObj.internalLink.value+formObj.anchor.value;
        break;
    case "2":
        href = formObj.protocol.value+linkUrlObj.value;
        break;
    case "3":
        href = 'media:'+formObj.mediaId.value+":"+formObj.mediaTitle.value;
        break;
	 case "4":
        href = (linkUrlObj.value.indexOf('#')!=0 ? '#' : '')+linkUrlObj.value;
        break;
    case "6":
        href = linkUrlObj.value;
        break;
    case "7":
        href = formObj.repeaterLink.value;
        break;
    }

	if (href) {
        href = convertURL(href, elm);
        setAttrib(elm, 'href', href);
        setAttrib(elm, 'title');
        setAttrib(elm, 'id');
        setAttrib(elm, 'style');
        setAttrib(elm, 'class', formObj.cssclass.value);
        if (parent.Pinax.tinyMCE_allowLinkTarget) {
            setAttrib(elm, 'target', formObj.elements['relExternal'].checked ? "_blank" : "" );
        } else {
        	setAttrib(elm, 'rel', formObj.elements['relExternal'].checked ? "external" : "" );
        }
        setAttrib(elm, 'charset');
        setAttrib(elm, 'hreflang');
        setAttrib(elm, 'dir');
        setAttrib(elm, 'lang');
        setAttrib(elm, 'tabindex');
        setAttrib(elm, 'accesskey');

        // Refresh in old MSIE
        if (tinyMCE.isMSIE5)
            elm.outerHTML = elm.outerHTML;
    }
    else {
        setAttrib(elm, 'href', null);
    }
}


function getSelectValue(form_obj, field_name) {
	var elm = form_obj.elements[field_name];

	if (elm == null || elm.options == null)
		return "";

	return elm.options[elm.selectedIndex].value;
}


function update(i,t) {
	var linkUrlObj = document.getElementById("linkUrl");
	var formObj = document.forms[0];
    with (formObj) {
        mediaId.value = i;
        mediaTitle.value = t;
        title.value = t;
    }
	linkUrlObj.value = t;
}


tinyMCEPopup.onInit.add(init);
