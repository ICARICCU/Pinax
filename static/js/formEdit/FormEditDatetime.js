/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('datetime', {

	__construct: function () {
        var format = $(this).data('format') ? $(this).data('format') : PinaxLocale.datetime.format;

        $(this).datetimepicker({
            language: 'it',
            format: format,
            autoclose: true,
            todayHighlight: true
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
