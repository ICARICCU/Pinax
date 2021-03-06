/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.permission", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        element.removeAttr('value');
        element.css('width', '500px');
    },

    getValue: function () {
        return this.$element.select2('val');
    },

    setValue: function (value) {
        if (value !== undefined && value.length > 0) {
            var arrayVal = []

            $.each(value, function(index, v) {
                if (typeof(v)=="object") {
                    arrayVal.push(v);
                }
                else {
                    arrayVal.push({id: v, text: v});
                }
            });

           this.$element.select2('data', arrayVal);
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
