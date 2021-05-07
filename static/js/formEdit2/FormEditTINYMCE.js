/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.tinymce", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    elementId: null,
    editor: null,

    $statics: {
        id: 0
    },

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;

        if (!element.attr('id')) {
            this.$self.id++;
            this.elementId = element.attr('name') + this.$self.id;
            element.attr('id', this.elementId);
        } else {
            this.elementId = element.attr('id');
        }

		var options = Pinax.tinyMCE_options;

		options.mode = "exact";
		options.elements = this.elementId;
		options.document_base_url = Pinax.tinyMCE_options.urls.root;
		tinyMCE.init( options );
    },

    save: function () {
        return tinyMCE.get(this.elementId).save();
    },

    getValue: function () {
        try {
            return tinyMCE.get(this.elementId).getContent();
        } catch(err){
            return this.$element.val();
        }
    },

    setValue: function (value) {
        this.$element.val(value);
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function()
    {
        $('html, body').animate({ scrollTop: this.$element.parent().offset().top - this.$element.parent().prop('scrollHeight') }, 'slow');
    },

    destroy: function() {
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        try {
            var container = tinyMCE.get(this.elementId).getContainer();
            $(container).find('.mceLayout').addClass(className);
        } catch(err){}
    },

    removeClass: function(className) {
        try {
            var container = tinyMCE.get(this.elementId).getContainer();
            $(container).find('.mceLayout').removeClass(className);
        } catch(err){}
    }
});
