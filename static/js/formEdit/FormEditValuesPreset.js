/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('valuesPreset', {
    __construct: function () {
        var self = $(this).data('formEdit');
        self.element = $(this);
        self.element.on('change', function() {
            var options = self.element.find('option:selected').data('options');
            var elements = self.element.data('elements');
            if (options && elements) {
                options = options.split(',');
                elements = elements.split(',');
                $(elements).each(function(index, item){
                    $('input[name='+item+']').val(options[index]).change();
                });
            }
        });

    },

    getValue: function () {
        return $(this).val();
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function () {
    }
});
