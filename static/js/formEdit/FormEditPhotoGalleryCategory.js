/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.PinaxRegisterType('photogallerycategory', {

	__construct: function () {
	    var value = jQuery(this).data('origValue');
        if (value) {
            $('#galleryType').val(value['galleryType']);

            var arrayVal = []

            $.each(value['gallery-images'], function(index, v) {
                if (typeof(v)=="object") {
                    arrayVal.push(v);
                }
                else {
                    arrayVal.push({id: v, text: v});
                }
            });

            $('#gallery-images').select2('data', arrayVal);
        }
    },

	getValue: function () {
        var galleryType = $('#galleryType').val();
        var galleryImages = $('#gallery-images').select2('val');
        return {'galleryType': galleryType, 'gallery-images': galleryImages};
	},

	setValue: function (value) {
	},

	destroy: function () {
	}
});
