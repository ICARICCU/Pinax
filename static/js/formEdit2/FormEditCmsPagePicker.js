/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.CmsPagePicker", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    $element: null,
    $relationLink: null,
    controllerName: null,
    multiple: null,
    ajaxUrl: null,
    ready: true,

    initialize: function (element) {
        var self = this;
        element.data('instance', this);
        this.$element = element;
        this.$relationLink = this.$element.parent().find('div.js-relation').first();

        element.removeAttr('value');

        this.controllerName = element.data('controllername');
        var filterMenuType = element.data('menutype') || '';
        var filterPageType = $(this).data('pagetype') || '';
        var protocol = element.data('protocol') || '';
        var minimumInputLength = element.data('min_input_length') || 3;
        var params = element.data('params') || '';
        if (params) {
            params = '&'+decodeURIComponent(this.params);
        }
        this.ajaxUrl = Pinax.ajaxUrl + "&controllerName="+this.controllerName+"&menutype="+filterMenuType+"&pagetype="+filterPageType+"&protocol="+protocol+params;

        this.multiple = element.data('multiple');

        element.select2({
            placeholder: '',
            allowClear: true,
            multiple: this.multiple,
            minimumInputLength: minimumInputLength,
            ajax: {
                url: this.ajaxUrl,
                dataType: 'json',
                quietMillis: 100,
                data: function(term) {
                    return {
                        term: term
                    };
                },
                results: function(data, page ) {
                    return { results: data }
                }
            },
            formatResult: function(data) {
                return data.text+'<br><small>'+data.path+'</small>';
            },
            formatSelection: function(data) {
                return data.text+' <small>'+data.path+'</small>';
            }
        }).on('change', function(e){
            self.updateRelationIcon();
        });

        element.data('linked-element-id', element[0].previousElementSibling.id);

        if (this.multiple) {
            element.parent().find("ul.select2-choices").sortable({
                containment: 'parent',
                start: function() { element.select2("onSortStart"); },
                update: function() { element.select2("onSortEnd"); }
            });
        }

        this.$relationLink.click(function(e){
            e.preventDefault();
            self.onClickRelation();
        })

         this.updateRelationIcon();
    },

    getValue: function () {
        return this.ready ? this.$element.val() : this.$element.data('original-value');
    },

    setValue: function (value) {
        if (value) {
            this.ready = false;
            var self = this;
            this.$element.data('original-value', JSON.stringify(value));

            $.ajax({
                url: this.ajaxUrl,
                dataType: 'json',
                data: {id: value},
                success: function(data) {
                    self.ready = true;
                    self.$element.select2('data', self.multiple ? data : data[0]);
                    self.updateRelationIcon();
                }
            });
        } else {
            this.updateRelationIcon();
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function()
    {
        this.$element.focus();
    },

    destroy: function () {
        this.$element.select2('destroy');
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

    updateRelationIcon: function() {
        var data = this.$element.select2('data');
        this.$relationLink.toggleClass('disabled', !(data && data.url));
    },

    onClickRelation: function() {
        var data = this.$element.select2('data');
        if (data && data.url) {
            Pinax.events.broadcast('pinaxcms.formEdit2.CmsPagePicker.relation', data);
        }
    }
});
