/**
 * TinyMCE Image plugin, based on TinyMCE-advlink plugin by Moxiecode Systems AB.
 *
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function() {
	tinymce.PluginManager.requireLangPack('PNX_links');

	tinymce.create('tinymce.plugins.PinaxLinkPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceGlzLink', function() {
				var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;

				ed.windowManager.open({
					file : url + '/link.htm',
					width : 420 + parseInt(ed.getLang('PNX_links.delta_width', 0)),
					height : 560 + parseInt(ed.getLang('PNX_links.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('link', {
				title : 'advlink.link_desc',
				cmd : 'mceGlzLink'
			});

			ed.addShortcut('ctrl+k', 'advlink.link_desc', 'mcePNX_links');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('link', co && n.nodeName != 'A');
				cm.setActive('link', n.nodeName == 'A' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'Pinax link (based on TinyMCE-advlink plugin by Moxiecode Systems AB)',
				author : 'PINAX',
				authorurl : '',
				infourl : '',
				version : '1.2.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('PNX_links', tinymce.plugins.PinaxLinkPlugin);
})();
