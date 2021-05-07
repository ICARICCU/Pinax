/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var PinaxcmsSiteTree = dejavu.Class.declare({
    treeId: null,
    tree: null,
    addPageId: null,
    ajaxUrl: null,
    treeOffset: null,
    treeLoaded: false,

    initialize: function(treeId, addPageId) {
        var self = this;
        this.treeId = treeId;
        this.addPageId = addPageId;
        this.tree = $(this.treeId);
        this.ajaxUrl = this.tree.data('ajaxurl');
        var dnd = this.tree.data('dnd') ? 'dnd' : '';

        // initialize the tree
        this.tree.jstree({
            // List of active plugins
            dots     : false,
            "plugins" : [ "themes","json_data","ui","crrm","cookies",dnd,"types","contextmenu"],
            "json_data" : {
                "ajax" : {
                    "url" : this.ajaxUrl+"GetSiteTree",
                    "data" : function (n) {
                        self.onResize();
                        return {
                            "id" : n.attr ? n.attr("id").replace("node_","") : 0
                        };
                    }
                }
            },

            "themes": {
                dots: false
            },
            "cookies" : {
                "save_selected" : false
            },
            "contextmenu" : {
                "items" : this.customMenu
            },
            "crrm" : {
                "move" : {
                    "check_move" : this.onCheckMove
                }
            }
        })
        .bind("select_node.jstree", this.onNodeSelect)
        .bind("remove.jstree", this.onNodeRemove)
        .bind("move_node.jstree", this.onNodeMove)
        .bind("loaded.jstree", this.onTreeLoaded);
        this.tree.css("overflow", "auto");
        this.treeOffset = this.tree.offset();
        // add page button
        $(addPageId).click(this.onAddPageClick);
        $('a.js-pinaxcmsSiteAddMenu').click(this.onAddMenuClick);

        // Listen events
        Pinax.events.on("pinaxcms.pageAdded", this.onPageAdded);
        Pinax.events.on("pinaxcms.refreshTree", this.onRefreshTree);
        Pinax.events.on("pinaxcms.renameTitle", this.onRefreshTree);
        Pinax.events.on("pinaxcms.treeCallAjaxForMenu", this.onTreeAjaxCall);

        $(window).resize(this.onResize);
        this.onResize();
    },


    onResize: function (event) {
        var h = $(window).height() - this.treeOffset.top;
        this.tree.height(h);
    }.$bound(),

    onTreeLoaded: function (event, data) {
        this.tree.find('li a').first().trigger('click');
        this.tree.jstree("open_node", this.tree.find('li a').first());
        this.treeLoaded = true;
    }.$bound(),

    /**
     * Tree node selected, dispatch the event with the menuId param
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeSelect: function (event, data) {
        if (data.rslt.e) {
            if (!data.rslt.obj.data('edit')) {
                if (!this.treeLoaded) return;
                Pinax.events.broadcast("pinax.message.showError", {"title": PinaxLocale.NO_EDIT, "message": ""});
                return;
            };

            Pinax.events.broadcast("pinaxcms.pageEdit", {"menuId": data.rslt.obj.attr("id")});
            if (data.rslt.obj.data('add')) {
                $(this.addPageId).show();
            } else {
                $(this.addPageId).hide();
            }
        }
    }.$bound(),

    /**
     * Tree node delete, send a ajax request to delete the node,
     * after deleted the tree is refreshed.
     * The ajax call Delete command with:
     *    menuId: id to delete
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeRemove: function (event, data) {
        var self = this;
        data.rslt.obj.each(function () {
            if (!data.rslt.obj.data('delete')) return;

            $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+"Delete",
                data : {
                    "menuId" : this.id.replace("node_","")
                },
                success : function (r) {
                    if(!r.status) {
                        data.inst.refresh();
                    }
                }
            });
        });
    }.$bound(),

    /**
     * Tree node move, send a ajax request to move the node,
     * after the mode the tree is refreshed.
     * The ajax call Move command with:
     *    menuId: id to move
     *    parentId: id of parent node
     *    position: a new position
     * @param  event event DOM event
     * @param  object data  Treeview data object
     */
    onNodeMove: function (event, data) {
        var self = this;
        data.rslt.o.each(function (i) {
            if (!$(this).data('edit') || !data.rslt.np.data('edit')) {
                $.jstree.rollback(data.rlbk);
                return;
            }

            $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+"Move",
                data : {
                    "menuId" : $(this).attr("id").replace("node_",""),
                    "parentId" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
                    "position" : data.rslt.cp + i
                },
                success : function (r) {
                    if(!r.status) {
                        $.jstree.rollback(data.rlbk);
                    }
                    else {
                        $(data.rslt.oc).attr("id", "node_" + r.id);
                        if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    }.$bound(),

    onAddPageClick: function(e){
        e.preventDefault();
        Pinax.events.broadcast("pinaxcms.pageAdd", {"href": e.currentTarget.href+(e.currentTarget.href.indexOf('?') > -1 ? '&' : '?')+'menuId='+this.tree.jstree('get_selected').attr("id")});
    }.$bound(),

    onAddMenuClick: function(e){
        e.preventDefault();

        if ($(e.currentTarget).data('mode')=='modal') {
            Pinax.closeIFrameDialog(true);
            Pinax.openIFrameDialog(
                e.currentTarget.title,
                e.currentTarget.href,
                750,
                75,
                75 );
        }

    }.$bound(),

    onPageAdded: function(e){
        Pinax.events.broadcast("pinaxcms.pageEdit", {"menuId": e.message.menuId});
        var node = this.tree.find("#"+e.message.parentId);
        var tree = jQuery.jstree._reference(this.treeId);
        tree.refresh(node);
    }.$bound(),

    onRefreshTree: function(e){
        var tree = jQuery.jstree._reference(this.treeId);
        var currentNode = tree._get_node(null, false);
        var parentNode = tree._get_parent(currentNode);
        tree.refresh(parentNode);
    }.$bound(),

    onTreeAjaxCall: function(e){
        var self = this;
        $.ajax({
                async : false,
                type: 'POST',
                url: self.ajaxUrl+e.message.action,
                data : {
                    "menuId" : e.message.menuId
                },
                success : function (r) {
                    Pinax.events.broadcast("pinaxcms.refreshTree");
                }
            });
    }.$bound(),

    onCheckMove: function(m) {
        var pagetype = m.np.data('pagetype');
        var acceptparent = m.o.data('acceptparent');
        if (pagetype && acceptparent) {
            acceptparent = acceptparent.split(',');
            return acceptparent.indexOf(pagetype)!==-1;
        }
        return true;
    }.$bound(),


    customMenu: function(node) {
        var self = this;
        var menu = {};
        if (node.data("edit") == "1") {
            menu.modify = {
                "label": "{i18n:Edit}",
                "icon": "icon-pencil",
                "action":  function(obj) {
                     Pinax.events.broadcast("pinaxcms.pageEdit", {"menuId": obj.attr("id")});
                }
            };
        }
        if (node.data("draft") == "1" && node.data("isDraft") == "1") {
            menu.modifyDraft = {
                "label": "{i18n:Edit draft}",
                "icon": "icon-pencil",
                "action":  function(obj) {
                     Pinax.events.broadcast("pinaxcms.pageEdit", {"menuId": obj.attr("id")});
                }
            };
        }
        if (node.data("publish") == "1" && node.data("isDraft") == "1") {
            menu.publish = {
                "label": "{i18n:Publish}",
                "icon": "icon-check",
                "action":  function(obj) {
                }
            };
        }
        if (node.data("delete") == "1") {
            menu.remove = {
                "label": "{i18n:Delete}",
                "icon": "icon-remove",
                "action":  function(obj) {
                    Pinax.confirm("{i18n:pinaxcms.confirm.pageDelete}", [], function(success){
                        if (success) self.remove(obj);
                    });
                }
            };
        }
        if (node.data("show") == "1") {
            if (node.data("isShown") == "1") {
                menu.show = {
                    "label": "{i18n:Hide}",
                    "icon": "icon-eye-close",
                    "action":  function(obj) {
                         Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "hide", "menuId": obj.attr("id")});
                    }
                };
            } else {
                menu.show = {
                    "label": "{i18n:Show}",
                    "icon": "icon-eye-open",
                    "action":  function(obj) {
                         Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "show", "menuId": obj.attr("id")});
                    }
                };
            }
        }
        if (node.data("lock") == "1") {
            if (node.data("isLocked") == "1") {
                menu.lock = {
                    "label": "{i18n:Change to public page}",
                    "icon": "icon-lock",
                    "action":  function(obj) {
                         Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "unlock", "menuId": obj.attr("id")});
                    }
                };
            } else {
                menu.lock = {
                    "label": "{i18n:Change to private page}",
                    "icon": "icon-unlock",
                    "action":  function(obj) {
                         Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "lock", "menuId": obj.attr("id")});
                    }
                };
            }
        }
        if (node.data("preview") == "1") {
            menu.preview = {
                "label": "{i18n:Preview}",
                "icon": "icon-desktop",
                "action":  function(obj) {
                     Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "hide", "menuId": obj.attr("id")});
                }
            };
        }
        if (node.data("duplicatePage") == "1") {
            menu.duplicatePage = {
                "label": "{i18n:Duplicate page}",
                "icon": "icon-copy",
                "action":  function(obj) {
                    Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "duplicatePage", "menuId": obj.attr("id")});
               }
            };
        }
        if (node.data("duplicateBranch") == "1") {
            menu.duplicateBranch = {
                "label": "{i18n:Duplicate branch}",
                "icon": "icon-folder-open",
                "action":  function(obj) {
                    Pinax.events.broadcast("pinaxcms.treeCallAjaxForMenu", {"action": "duplicateBranch", "menuId": obj.attr("id")});
               }
            };
        }
        return menu;
    }
});
