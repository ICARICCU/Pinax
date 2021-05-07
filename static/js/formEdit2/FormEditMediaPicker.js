/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.mediapicker", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    $mediaPicker: null,
    populateDataEnabled: false,
    eventPos: null,
    imageResizer: null,

    initialize: function (element, pinaxOpt) {
        element.data('instance', this);
        this.$element = element;
        this.populateDataEnabled = element.attr('data-populate_data') == 'true';
        this.imageResizer = pinaxOpt.imageResizer;

        var that = this;
        var $input = element.hide(),
            pickerType = $input.attr('data-mediatype'),
            externalFiltersOR = $input.attr('data-externalfiltersor'),
            hasPreview = $input.attr('data-preview') == 'true';

        that.$mediaPicker =
            hasPreview ? jQuery('<div id="'+element.attr('name')+'-mediapicker" class="mediaPickerSelector mediaPickerField"><div class="mediaPickerCaption"></div><div class="mediaPickerElement">' + PinaxLocale.MediaPicker.imageEmpty.replace('##STATIC##', PinaxLocale.FormEdit.static)  + '</div></div>')
            : jQuery('<input class="mediaPickerField" type="text" size="50" readonly="readonly" style="cursor:pointer" value="' + PinaxLocale.MediaPicker.imageEmptyText + '">');

        if (!$input.next().hasClass('mediaPickerField')) {
            that.$mediaPicker.insertAfter($input).click(function() {
                    var url = pinaxOpt.mediaPicker;
                    if (pickerType) {
                        url += '&mediaType=' + pickerType;
                    }
                    else if(externalFiltersOR){
                        url += '&externalFiltersOR=' + externalFiltersOR;
                    }
                    Pinax.openIFrameDialog( hasPreview ? PinaxLocale.MediaPicker.imageTitle : PinaxLocale.MediaPicker.mediaTitle,
                                            url,
                                            1400,
                                            50,
                                            50,
                                            Pinax.responder(that, that.disposeEvent));
                    Pinax.lastMediaPicker = that;
                    that.eventPos = Pinax.events.on("pinaxcms.onSetMediaPicker", Pinax.responder(that, that.onSetMediaPicker));
                });
        }

    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        if (value) {
            this.setProps(JSON.parse(value));
        }
    },

    populateData: function(values) {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');

        for (var field in values) {
            var $el = $container.find('input[data-media_picker_mapping='+field+']');
            if ($el) {
                var obj = $el.data('instance');

                if (obj) {
                    obj.setValue(values[field]);
                }
            }
        }
    },

    clearData: function() {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');
        $container.find('input[disabled=disabled]').val('');
    },

    setProps: function (props) {
        var $this = this.$mediaPicker,
            $img = $this.find('img');

        if (this.populateDataEnabled) {
            if (props) {
                this.populateData(props);
            } else {
                this.clearData();
            }
        }

        if (!props || !props.id) {
            if ($img.length) {
                $img.replaceWith(PinaxLocale.MediaPicker.imageEmpty.replace('##STATIC##', PinaxLocale.FormEdit.static));
            }
            else {
                $this.val(PinaxLocale.MediaPicker.imageEmptyText);
            }
            $this.prev().val('');
        }
        else {
            if ($img.length) {
                $img.load(function () {

                        var w = this.naturalWidth,
                            h = this.naturalHeight,
                            maxW = $this.width() -6,
                            maxH = $this.height() -6;

                        if (w > maxW) {
                            h = h * (maxW / w);
                            w = maxW;
                        }
                        if (h > maxH) {
                            w = w * (maxH / h);
                            h = maxH;
                        }
                        jQuery(this).attr({width: w, height: h})
                            .show();
                    })
                    .hide();

                var src = this.imageResizer.replace('#id#', props.id);
                $img.attr({title: props.title, src: src})
                    .data({id: props.id, fileName: props.fileName});

                if ($img[0].complete && $img[0].naturalWidth !== 0) {
                    $img.trigger('load');
                }
            }
            else {
                $this.val(props.title);
            }
            $this.prev().val( JSON.stringify(props) );
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    getPreview: function (val) {
        try {
            var props = JSON.parse(val);
            return props.title;
        } catch(e) {
            return val;
        }
    },

    disposeEvent: function()
    {
        if (this.eventPos!==null && this.eventPos!==undefined) {
            Pinax.events.unbind("pinaxcms.onSetMediaPicker", this.eventPos);
            this.eventPos = null;
        }
    },

    onSetMediaPicker: function(event)
    {
        this.disposeEvent();
        this.setProps(event.message);
        Pinax.closeIFrameDialog();
    },

    focus: function () {
        var mediaPickerId = this.$element.attr('id')+'-mediapicker';
        $('#'+mediaPickerId).addClass('GFEValidationError');
        document.getElementById(mediaPickerId).scrollIntoView();
    },

    destroy: function() {
        this.disposeEvent();
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        this.$element.addClass(className);
    },

    removeClass: function(className) {
        this.$element.removeClass(className);
    },

    isVisible: function() {
        return this.$element.parents('.control-group').is(':visible');
    }
});
