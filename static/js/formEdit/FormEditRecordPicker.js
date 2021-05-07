/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('recordpicker', {

    __construct: function () {
        var self = this;
        self.element = $(this);
        self.data = [];
        self.container = null;
        self.previewUrl = self.element.data('preview_url');

        self.render = function() {
            var self = this;
            if (!self.container) {
                self.container = $(Pinax.template.render('pinaxformedit.recordpicker.container', {})).insertAfter(self.element);
                self.element.hide();
            }

            var html = Pinax.template.render('pinaxformedit.recordpicker.items', {data: self.data, previewUrl: self.previewUrl});
            self.container.html($(html));

            self.container.find('.js-sortable').sortable({
                'stop': function(event, ui) {
                    self.onChange(event, ui);
                }
            });

            self.storeValue();
        };


        self.addEvents = function() {
            $(self.container).on('click', 'div.js-add', function(e){
                e.preventDefault();
                self.onOpenPicker();
            });

            $(self.container).on('click', 'a.js-delete', function(e){
                e.preventDefault();
                self.onDelete(e);
            });

            $(self.container).on('click', 'a.js-preview', function(e){
                e.preventDefault();
                self.onPreview(e);
            });

        };

        self.onOpenPicker = function() {
            Pinax.openIFrameDialog( self.element.attr('title'),
                                                self.element.data('picker_url'),
                                                1400,
                                                50,
                                                50,
                                                null,
                                                Pinax.responder(self, self.disposeEvent));
            self.eventPos = Pinax.events.on("recordsPicker.set", Pinax.responder(self, self.onPickerSetValue));
        };

        self.onDelete = function(e) {
            self.data.splice($(e.currentTarget).data('pos'), 1);
            self.render();
        };

        self.onPreview = function(e) {
            var url = self.previewUrl.replace('##ID##', $(e.currentTarget).data('id'));
            window.open(url, '_blank');
        };

        self.disposeEvent = function() {
            if (self.eventPos !== null) {
                Pinax.events.unbind("recordsPicker.set", self.eventPos);
            }
            self.eventPos = null;
        };

        self.onPickerSetValue = function(event) {
            self.disposeEvent();
            Pinax.closeIFrameDialog(true);
            _.each(event.message, function(item){
                if (_.findWhere(self.data, {id: item.id})===undefined) {
                    self.data.push(item);
                }
            });
            self.render();
        };


        self.onChange = function(event, ui) {
            var newData = [];
            self.container.find('.js-delete').each(function(i, el) {
                newData.push(self.data[$(el).data('pos')]);
            });

            self.data = newData;
            self.render();
        };

        self.storeValue = function() {
            self.element.val(JSON.stringify(self.data));
        };

        self.templateDefine = function() {
            Pinax.template.define('pinaxformedit.recordpicker.container',
                '<div class="pinax-formedit-recordpicker">'+
                '</div>');

            Pinax.template.define('pinaxformedit.recordpicker.items',
                '<div class="js-sortable">'+
                '<% _.each(data, function(item, index) { %>'+
                '<div class="blockItem">'+
                '<h3><%= item.title %></h3>'+
                '<div class="actions">'+
                '<% if (previewUrl) { %>'+
                '<a title="<%= PinaxLocale.FormEdit.preview %>" class="js-preview" href="#" data-id="<%= item.id %>"><span class="btn-icon icon-eye-open"></span></a>'+
                '<% } %>'+
                '<a title="<%= PinaxLocale.FormEdit.remove %>" class="js-delete" href="#" data-pos="<%= index %>"><span class="btn-icon icon-trash"></span></a>'+
                '</div>'+
                '</div>'+
                '<% }); %>'+
                '</div>'+
                '<div class="blockItem blockEmpty js-add">'+
                '<i class="icon-plus"></i>'+
                '<div class="actions"><%= PinaxLocale.FormEdit.add %></div>'+
                '</div>');
        };

        var value = self.element.data('origValue');
        if (value) {
            self.data = typeof(value)=='string' ? JSON.parse(value) : value;
        }

        self.templateDefine();
        self.render();
        self.addEvents();
    },

    getValue: function () {
        return $(this).val();
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function () {
        if (this.disposeEvent) {
            this.disposeEvent();
        }
    },

    focus: function() {
        if (this.focus) {
            this.focus();
        }
    }
});
