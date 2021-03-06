/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.datetime", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        var format = element.data('format') ? element.data('format') : PinaxLocale.datetime.format;

        this.$element.datetimepicker({
            language: 'it',
            format: format,
            autoclose: true,
            todayHighlight: true
        });
    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        this.$element.val(value);
        this.$element.datetimepicker('update');
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function () {
        document.getElementById(this.$element.attr('id')).scrollIntoView();
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
