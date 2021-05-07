/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.recordpicker", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),

    data: [],

    container: null,

    previewUrl: null,

    eventPos: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;

        this.data = [];
        this.container = null;
        this.previewUrl = this.$element.data('preview_url');

        /*console.log(this.$element.val())
        var value = this.$element.val();
        if (value) {
            this.data = typeof(value) === 'string' ? JSON.parse(value) : value;
        }
*/
        this.templateDefine();
        this.render();
        this.addEvents();
    },

    render: function() {
        if (!this.container) {
            this.container = $(Pinax.template.render('pinaxformedit.recordpicker.container', {})).insertAfter(this.$element);
            this.$element.hide();
        }

        var html = Pinax.template.render('pinaxformedit.recordpicker.items', {data: this.data, previewUrl: this.previewUrl});
        this.container.html($(html));

        this.container.find('.js-sortable').sortable({
            'stop': Pinax.responder(this, this.onChange)
        });

        this.storeValue();
    },

    addEvents: function() {
        var self = this;

        var container = $(this.container);

        container.on('click', 'div.js-add', function(e){
            e.preventDefault();
            self.onOpenPicker();
        });

        container.on('click', 'a.js-delete', function(e){
            e.preventDefault();
            self.onDelete(e);
        });

        container.on('click', 'a.js-preview', function(e){
            e.preventDefault();
            self.onPreview(e);
        });

    },

    onOpenPicker: function() {
        Pinax.openIFrameDialog( this.$element.attr('title'),
            this.$element.data('picker_url'),
            1400,
            50,
            50,
            null,
            Pinax.responder(this, this.disposeEvent));
        this.eventPos = Pinax.events.on("recordsPicker.set", Pinax.responder(this, this.onPickerSetValue));
    },

    onDelete: function(e) {
        this.data.splice($(e.currentTarget).data('pos'), 1);
        this.render();
    },

    onPreview: function(e) {
        var url = this.previewUrl.replace('##ID##', $(e.currentTarget).data('id'));
        window.open(url, '_blank');
    },

    disposeEvent: function() {
        if (this.eventPos !== null) {
            Pinax.events.unbind("recordsPicker.set", this.eventPos);
        }
        this.eventPos = null;
    },

    onPickerSetValue: function(event) {
        this.disposeEvent();
        Pinax.closeIFrameDialog(true);
        _.each(event.message, Pinax.responder(this, this.pushData));
        this.render();
    },

    onChange: function(event, ui) {
        var self = this;

        var newData = [];
        this.container.find('.js-delete').each(function(i, el) {
            newData.push(self.data[$(el).data('pos')]);
        });

        this.data = newData;
        this.render();
    },

    pushData: function(item){
        if (_.findWhere(this.data, {id: item.id})===undefined) {
            this.data.push(item);
        }
    },

    storeValue: function() {
        this.$element.val(JSON.stringify(this.data));
    },

    setValue: function (value) {
        this.$element.val(value);
        if (value) {
            this.data = typeof(value) === 'string' ? JSON.parse(value) : value;
        }
        this.render();
    },

    templateDefine: function() {
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
            '<a title="<%= PinaxLocale.FormEdit.preview %>" class="js-preview" href="#" data-id="<%= item.id %>"><span class="btn-icon fa fa-eye icon-eye-open"></span></a>'+
            '<% } %>'+
            '<a title="<%= PinaxLocale.FormEdit.remove %>" class="js-delete" href="#" data-pos="<%= index %>"><span class="btn-icon fa fa-trash icon-trash"></span></a>'+
            '</div>'+
            '</div>'+
            '<% }); %>'+
            '</div>'+
            '<div class="blockItem blockEmpty js-add">'+
            '<i class="icon-plus"></i>'+
            '<div class="actions"><%= PinaxLocale.FormEdit.add %></div>'+
            '</div>');
    }
});
