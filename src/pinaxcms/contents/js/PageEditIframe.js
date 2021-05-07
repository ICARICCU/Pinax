/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.module('cms.PageEditIframe', function(){
    this.iframe = null;
    this.emptySrc = null;
    this.editSrc = null;
    this.iframeOffset = null;

    this.run = function() {
        var self = this;
        this.iframe = $("#js-pinaxcmsPageEdit");
        this.iframeOffset = this.iframe.offset();
        this.emptySrc = this.iframe.data("emptysrc");
        this.editSrc = this.iframe.data("editsrc");
        this.changeUrl(null, this.emptySrc);

        Pinax.events.on("pinaxcms.pageEdit", function(e){
            self.changeUrl(e.message.menuId);
        });

        Pinax.events.on("pinaxcms.pageAdd", function(e){
            self.changeUrl(null, e.message.href);
        });

        $('body').css('overflow', 'hidden');
        $(window).resize(Pinax.responder(this, this.onResize));
        this.onResize();
    };

    this.changeUrl = function(menuId, href) {
        if (menuId) {
            jQuery("#modalDiv").remove();
            this.iframe.attr("src", this.editSrc+menuId+"&_"+(new Date()).getTime());
        } else {
            this.iframe.attr("src", href);
        }
    };

    this.onResize = function() {
        var h = $(window).height() - this.iframeOffset.top;
        this.iframe.height(h);
    };
});
