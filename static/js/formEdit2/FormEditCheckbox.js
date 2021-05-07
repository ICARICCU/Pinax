/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.checkbox", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),

    getValue: function () {
		return this.$element.is(':checked');
    },

    setValue: function (value) {
        this.$element.attr('checked', value===true || value==='true' || value==='1' || value===1);

    },
});
