/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

PinaxApp.pages[ 'pinaxcms.contents.views.ContentsEdit' ] = function( state, routing ) {
    $(function(){
        if ('index'==state) {
            var tree = new PinaxcmsSiteTree("#js-pinaxcmsSiteTree", "#js-pinaxcmsSiteTreeAdd");
            // Pinax.module('cms.SiteTree').run();
            Pinax.module('cms.PageEditIframe').run();
        } else  if ('edit'==state) {
            Pinax.module('pinax.BlockEdit').run();
        }
    });
}
