/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.photogallerycategory", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
    },

    getValue: function () {
        var galleryType = $('#galleryType').val();
        var galleryImages = $('#gallery-images').select2('val');
        return {'galleryType': galleryType, 'gallery-images': galleryImages};
    },

    setValue: function (value) {
        if (value) {
            $('#galleryType').val(value['galleryType']);

            var arrayVal = []

            $.each(value['gallery-images'], function(index, v) {
                if (typeof(v)=="object") {
                    arrayVal.push(v);
                }
                else {
                    arrayVal.push({id: v, text: v});
                }
            });

            $('#gallery-images').select2('data', arrayVal);
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function()
    {
        this.$element.focus();
    },

    destroy: function() {
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        this.$element.addClass(className);
    },

    removeClass: function(className) {
        this.$element.removeClass(className);
    }
});
