/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit", {
    formId: null,
    $form: null,
    pinaxOpt: null,
    invalidFields: 0,
    customValidationInvalid: false,
    lang: null,
    fields: [],
    formDataJSON: "{}",
    readOnly: false,

    $statics: {
        fieldTypes: [],
        registerType: function (name, object) {
            this.fieldTypes[name] = object;
        }
    },

    getCurrentFormData: function() {
        var formData = {};

        this.fields.forEach(function(field) {
            if (!field.isDisabled()) {
                formData[field.getName()] = field.getValue();
            }
        });

        return formData;
    },

    hasUnmodifiedData: function() {
        return _.isEmpty(this.pinaxOpt.formData) || this.formDataJSON == JSON.stringify(this.getCurrentFormData());
    },

    updateFormData: function () {
        this.formDataJSON = JSON.stringify(this.getCurrentFormData());
    },

    initialize: function(formId, pinaxOpt) {
        var self = this;

        this.formId = formId;
        this.pinaxOpt = pinaxOpt;
        this.$form = $('#'+this.formId);
        this.$form.data('instance', this);
        this.lang = pinaxOpt.lang;
        this.readOnly = pinaxOpt.readOnly == "true" || pinaxOpt.readOnly == "1";

        $('#'+this.formId+' input[name]:not( [type="button"], [type="submit"], [type="reset"] ), '+
          '#'+this.formId+' textarea[name], '+
          '#'+this.formId+' select[name]').each(function () {
            self.createField(this);
        });

        // non unire alla selezione precedente, altrimenti gli input nel fieldset custom vengono
        // assegnati anche al formedit
        $('#'+this.formId+' fieldset[data-type]').each(function () {
            self.createField(this);
        });

        self.verifySelectWithTarget(this.$form);
        $('.js-pinaxcms-save').click(function (e) {
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, true, $(this));
        });

        $('.js-pinaxcms-cancel').click(function (e) {
            self.setFormButtonStates(false);
            window.onbeforeunload = null;
        });

        $('.js-pinaxcms-save-novalidation').click(function (e) {
            $.each(self.fields, function (index, obj) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            });
            self.setFormButtonStates(false);
            e.preventDefault();
            self.save(e.currentTarget, false, $(this));
        });

        this.initValidator();

        // aggangia anche l'evento submit per permettere la validazione dei campi
        this.$form.submit(function(event){
            if (self.$form.triggerHandler('submitForm') === false && this.invalidFields || this.customValidationInvalid) {
                self.customValidationInvalid = false;
                return false;
            } else {
                return true;
            }
        });

        window.setTimeout( //2s perché ci mette "un po'" ad aggiornare tutti i campi
            function(){
                self.updateFormData();
                 if (self.readOnly) {
                    return;
                }

                window.onbeforeunload = function exitWarning(e) {
                    if (!self.hasUnmodifiedData()) {
                        var msg = PinaxLocale.FormEdit.discardConfirmation;
                        e = e || window.event;
                        // For IE and Firefox prior to version 4
                        if (e) {
                            e.returnValue = msg;
                        }
                        // For Safari
                        return msg;
                    }
                };
            },
            2000
        );

        Pinax.events.broadcast("pinaxcms.formEdit.onReady");
        Pinax.events.on("pinaxcms.formEdit.updateFormData", function(e){
            self.updateFormData();
        });

    },

    verifySelectWithTarget: function($container) {
        var self = this;
        $container.find('select').each(function () {
            if (self.isSubComponent($(this))) {
                return;
            }
            var target = $(this).data('target');
            if ( target ) {
                $(this).change(function(e){
                    var sel = this.selectedIndex,
                        states = String($(this).data("val_"+sel)),
                        stateMap = {};
                    var t = target.split(",");
                    states = states.split(",");

                    $(t).each(function(index, val) {
                        stateMap[val] = states[index]==="1";

                        const $el = $container.find("#"+val);
                        if (stateMap[val]) {
                            if (!$el.data('linked-element-id')) {
                                $el.show();
                            }
                            $el.find("[name]").data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else {
                            if (!$el.data('linked-element-id')) {
                                $el.hide();
                            }
                            $el.find("[name]").data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });

                    $container.find("[name]").each(function(){
                        var $el = $(this);
                        var state = stateMap[$el.attr("name")];
                        if (state===true) {
                            $el.data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else if (state===false) {
                            $el.data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });
                });
                $(this).trigger("change");
            }
        });
    },

    setFormButtonStates: function(state) {
        if (state) {
            $('.js-pinaxcms-save').removeAttr('disabled');
            $('.js-pinaxcms-save-novalidation').removeAttr('disabled');
            $('.js-pinaxcms-cancel').removeAttr('disabled');
            $('.js-pinaxcms-preview').removeAttr('disabled');
        } else {
            $('.js-pinaxcms-save').attr('disabled', 'disabled');
            $('.js-pinaxcms-save-novalidation').attr('disabled', 'disabled');
            $('.js-pinaxcms-cancel').attr('disabled', 'disabled');
            $('.js-pinaxcms-preview').attr('disabled', 'disabled');
        }
    },

    // restituisce true se l'elemento è contenuto in un altro componente
    isSubComponent: function(element) {
        // se l'elemento è contenuto in altri tipi contenitori
        if ($(element).parents('[data-type]').length !== 0) {
            return true;
        } else {
            return false;
        }
    },

    createField: function(element) {
        if (this.isSubComponent(element)) {
            return;
        }

        var type = $(element).data('type') || 'standard';
        var obj = Pinax.oop.create("pinax.FormEdit."+type, $(element), this.pinaxOpt, this.$form);
        if (obj) {
        var value = this.pinaxOpt.formData[obj.getName()];
            if (value !== undefined) {
                obj.setValue(value);
            }

            this.fields.push(obj);
        }
    },

    initValidator: function() {
        var self = this;
        var firstInvalidObj = null;

        function testInvalidation(obj) {
            if (obj && !obj.isValid() && obj.isVisible()) {
                obj.addClass('GFEValidationError');
                obj.getElement().closest('.control-group').addClass('GFEValidationError');
                self.invalidFields++;
            }
        }

        function testValidation(obj) {
            if (obj && obj.isValid()) {
                obj.removeClass('GFEValidationError');
                obj.getElement().closest('.control-group').removeClass('GFEValidationError');
            }
        }

        self.$form.validVal({
            validate: {
                fields: {
                    hidden: true
                }
            },
            fields: {
                onInvalid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testInvalidation(obj);
                },
                onValid: function( $form, language ) {
                    var obj = $(this).data('instance');
                    testValidation(obj);
                    testInvalidation(obj);
                }
            },
            form: {
                onValidate: function () {
                    var error, fieldVals = {};

                    firstInvalidObj = null;

                    $('#'+self.formId+' fieldset[data-type]').each(function () {

                        // se l'elemento è contenuto in altro componente
                        if (self.isSubComponent($(this))) {
                            return;
                        }

                        var obj = $(this).data('instance');
                        if (!obj.isValid()) {
                            obj.addClass('GFEValidationError');
                            obj.getElement().closest('.control-group').addClass('GFEValidationError');
                            if (!self.customValidationInvalid) {
                                firstInvalidObj = obj;
                            }
                            self.customValidationInvalid = true;
                        } else {
                            obj.removeClass('GFEValidationError');
                            obj.getElement().closest('.control-group').removeClass('GFEValidationError');
                        }
                    });

                    if (self.pinaxOpt.customValidation && typeof(window[self.pinaxOpt.customValidation]) == 'function') {
                        jQuery(this).find('input:not( [type="button"], [type="submit"], [type="reset"] ), textarea, select').each(function () {
                            if (this.name) fieldVals[this.name] = jQuery(this).val();
                        });
                        if (error = window[self.pinaxOpt.customValidation](fieldVals)) {
                            alert(error);
                            Pinax.events.broadcast("pinax.message.showError", {"title": error, "message": ""});
                            self.customValidationInvalid = true;
                        }
                    }
                },
                onInvalid: function( field_arr, language ) {
                    var $invalidEl = field_arr.first();
                    var obj = $invalidEl.data('instance');

                    var linkedValidationElementId = $invalidEl.data('linked-element-id');
                    if (linkedValidationElementId) {
                        $invalidEl = $('#'+linkedValidationElementId);
                    }

                    if (!$invalidEl.is(":visible")) {
                        return true;
                    }

                    obj.focus();

                    self.invalidFields = $invalidEl.length;

                    $invalidEl.addClass('GFEValidationError');
                    $invalidEl.closest('.control-group').addClass('GFEValidationError');

                    if (!self.customValidationInvalid) {
                        var inTab = $invalidEl.closest('div.tab-pane');
                        if (inTab.length) {
                            $('a[data-target="#'+inTab.attr('id')+'"]').tab('show');
                        }
                    }
                },
                onValid: function() {
                    if (self.customValidationInvalid && firstInvalidObj) {
                        firstInvalidObj.focus();
                    }
                }
            }
        });
    },

    save: function (el, enableValidation, $saveButton) {
        var formData = this.getCurrentFormData();
        var self = this;

        if (enableValidation) {
            self.$form.triggerHandler('submitForm');

            if (self.invalidFields || self.customValidationInvalid) {
                self.customValidationInvalid = false;
                self.setFormButtonStates(true);
                self.invalidFields = 0;
                Pinax.events.broadcast("pinax.message.showError", {"title": self.lang.errorValidationMsg, "message": ""});
                return;
            }
        }

        var triggerAction = $(el).data("trigger");

        // return;

        jQuery.ajax(this.pinaxOpt.AJAXAction, {
            data: jQuery.param({action: $(el).data("action"), data: JSON.stringify(formData)}),
            type: "POST",
            success: function (data) {

                if (data.errors) {

                    // TODO localizzare
                    var errorMsg = '<p>' + PinaxLocale.FormEdit.unableToSave + '</p><ul>';
                    $.each(data.errors, function(id, value) {
                        errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                    });
                    Pinax.events.broadcast("pinax.message.showError", {"title": self.lang.errorValidationMsg, "message": errorMsg});

                } else if (data.errorFields) {
                    // TODO localizzare
                    var errorMsg = '<p>'+PinaxLocale.FormEdit.unableToSave+'</p><ul>';
                    $.each(data.errorFields, function(id, value) {
                        errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                        $(`#${id}`).parent().parent().addClass('GFEValidationError');
                    });
                    Pinax.events.broadcast("pinax.message.showError", {"title": self.lang.errorValidationMsg+' '+errorMsg, "message": ""});
                } else {
                    self.updateFormData();
                    Pinax.events.broadcast("pinaxcms.formEdit.onSaved", {data: formData});

                    if (data.evt) {

                        window.parent.Pinax.events.broadcast(data.evt, data.message);
                    } else if (data.url) {

                        if (data.target == 'window') {
                            parent.window.location.href = data.url;
                        } else {
                            document.location.href = data.url;
                        }

                    } else if (data.set) {

                        $.each(data.set, function(id, value){
                            $('#'+id).val(value);
                        });
                        Pinax.events.broadcast("pinax.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        if (triggerAction) {
                            triggerAction('click', formData);
                        }

                    } else if (data.callback) {

                        window[data.callback](data);

                    } else {

                        if (triggerAction) {
                            triggerAction('click', formData);
                        } else {
                            Pinax.events.broadcast("pinax.message.showSuccess", {"title": self.lang.saveSuccessMsg, "message": ""});
                        }

                    }
                }

                Pinax.events.broadcast("pinaxcms.formEdit.onSaved", {data: self.getCurrentFormData()});
                self.setFormButtonStates(true);
            }
        });
    }
});


