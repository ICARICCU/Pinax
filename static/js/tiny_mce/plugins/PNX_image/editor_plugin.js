/**
 * TinyMCE Image plugin, based on TinyMCE-advimage plugin by Moxiecode Systems AB.
 *
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


(function() {
	tinymce.PluginManager.requireLangPack('PNX_image');

	tinymce.create('tinymce.plugins.PinaxImagePlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceGlzImage', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/image.htm',
					width : $(window).width() - 80 + parseInt(ed.getLang('PNX_image.delta_width', 0)),
					height : Math.max(530, $(window).height() - 150) + parseInt(ed.getLang('PNX_image.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('image', {
				title : 'advimage.image_desc',
				cmd : 'mceGlzImage'
			});
		},

		getInfo : function() {
			return {
				longname : 'Pinax Image (based on TinyMCE-advimage plugin by Moxiecode Systems AB)',
				author : 'PINAX',
				authorurl : '',
				infourl : '',
				version : '1.2.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('PNX_image', tinymce.plugins.PinaxImagePlugin);
})();
