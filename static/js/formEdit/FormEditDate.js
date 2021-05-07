/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('date', {

	__construct: function () {
        var format = $(this).data('format') ? $(this).data('format') : PinaxLocale.date.format;

        $(this).datetimepicker({
            language: 'it',
            minView: 'month',
            format: format,
            autoclose: true,
            todayHighlight: true
        });

        $(this).attr('autocomplete', 'off');
	},

	getValue: function () {
        return $(this).val();
	},

	setValue: function (value) {
        $(this).val(value);
    },

	destroy: function () {
	},

    focus: function () {
        document.getElementById($(this).attr('id')).scrollIntoView();
    }
});
