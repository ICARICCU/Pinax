/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.standard", {
    $element: null,
    readyToEdit: true,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
    },

    getElement: function () {
        return this.$element;
    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        this.$element.val(value);
        if (this.$element.prop("tagName")==='SELECT' && this.$element.val()!==value) {
            this.$element.prop("selectedIndex", 0);
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
    },

    isValid: function() {
        if (this.$element.hasClass('required') && this.$element.data('skip-validation')!==true) {
            return this.getValue() != '';
        } else {
            return true;
        }
    },

    isVisible: function() {
        return this.$element.is(":visible");
    },

    isReadyToEdit: function() {
        return this.readyToEdit;
    },

    setRequired: function() {
        this.$element.data('skip-validation', false);
        this.$element.parents('div.form-group,div.control-group').find('label').addClass('required');
        return this;
    },

    setOptional: function() {
        this.$element.data('skip-validation', true);
        this.$element.parents('div.form-group,div.control-group').find('label').removeClass('required');
        return this;
    },

    setReadOnly: function() {
        this.$element.attr('disabled', true);
        return this;
    },

    setEditable: function() {
        this.$element.attr('disabled', false);
        return this;
    }
});
