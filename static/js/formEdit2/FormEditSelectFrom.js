/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.selectfrom", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    multiple: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;

        element.removeAttr('value');
        element.css('width', '500px');

        var fieldName = element.data('field') || element.attr('name');
        this.multiple = element.data('multiple');
        var addNewValues = element.data('add_new_values');
        var model = element.data('model');
        var query = element.data('query');
        var proxy = element.data('proxy');
        var proxyParams = element.data('proxy_params');
        if (proxyParams) {
            proxyParams = proxyParams.replace(/##/g,'"');
        }
        var placeholder = element.data('placeholder');
        var originalName = element.data('originalName');
        var getId = element.data('get_id');
        var selectedCallback = element.data('selected_callback');
    	var minimumInputLength = element.data('min_input_length') || 0;
    	var formatSelection = element.data('format_selection');
        var formatResult = element.data('format_result');

        if (originalName !== undefined && element.data('override')!==false) {
            fieldName = originalName;
        }

        element.select2({
            separator: '##s2sep##',
        	width: 'off',
            multiple: this.multiple,
            minimumInputLength: minimumInputLength,
            placeholder: placeholder === undefined ? '' : placeholder,
            allowClear: true,
            ajax: {
                url: Pinax.ajaxUrl + "&controllerName=pinaxcms.contents.controllers.autocomplete.ajax.FindTerm",
                dataType: 'json',
                quietMillis: 250,
                data: function(term, page) {
                    return {
                        fieldName: fieldName,
                        model: model,
                        query: query,
                        term: term,
                        proxy: proxy,
                        proxyParams: proxyParams,
                        getId: getId
                    };
                },
                results: function(data, page ) {
                    return { results: data.result }
                }
            },
            createSearchChoice: function(term, data) {
                if (!addNewValues) {
                    return false;
                }

                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {
                    return {id:term, text:term};
                }
            },
            formatResult: function(data) {
                return formatResult === undefined ? data.text : window[formatResult](data);
            },
            formatSelection: function(data) {
                if (selectedCallback) {
                    var term = data.text;

                    $.ajax({
                        url: Pinax.ajaxUrl+"&controllerName="+selectedCallback,
                        data: {
                            fieldName: fieldName,
                            model: model,
                            query: query,
                            term: term,
                            proxy: proxy,
                            proxyParams: proxyParams,
                            getId: getId
                        },
                        type: "POST"
                    });
                }

                return formatSelection === undefined ? data.text : window[formatSelection](data);
            },
            formatNoMatches: function () { return PinaxLocale.selectfrom.formatNoMatches; },
            formatSearching: function () { return PinaxLocale.selectfrom.formatSearching; }
        });

        element.data('linked-element-id', element[0].previousElementSibling.id);

        if (this.multiple) {
            element.parent().find("ul.select2-choices").sortable({
                containment: 'parent',
                start: function() { element.select2("onSortStart"); },
                update: function() { element.select2("onSortEnd"); }
            });
        }

    },

    getValue: function () {
        if (this.$element.data('return_object')) {
            return this.$element.select2('data');
        } else {
            return this.$element.select2('val');
        }
    },

    setValue: function (value) {
        if (!this.multiple) {
            if (value) {
                if (typeof(value)=="object") {
                    this.$element.select2('data', value);
                } else {
                    this.$element.select2('data', {id: value, text: value});
                }
            }
        }
        else if (Array.isArray(value)) {
            var arrayVal = [];

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

    focus: function () {
        this.$element.select2('focus');
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
