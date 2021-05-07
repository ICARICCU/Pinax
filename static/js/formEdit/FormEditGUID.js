/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('inputguid', {
    __construct: function () {
        var self = $(this).data('formEdit');
        self.element = $(this);
        if (''==self.element.val() || 'NaN'==self.element.val()) {
            self.element.val(self.element.data('base')+(new Date().getTime()));
        }
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
