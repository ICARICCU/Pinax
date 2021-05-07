/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Pinax.oop.declare("pinax.FormEdit.googlemaps", {
    $extends: Pinax.oop.get('pinax.FormEdit.standard'),
    map: null,
    marker: null,
    elementMap: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        this.render();
    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        this.$element.val(value);
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function()
    {
        this.$element.focus();
    },

    render: function() {
        var self = this;

        if (this.$element.data('isInit')!==true) {
            var name = this.$element.attr('name'),
            html = '<input id="'+name+'-search" class="btn map-search" type="button" value="'+PinaxLocale.GoogleMap.search+'"/>';
            this.$element.after(jQuery(html));
            this.$element.addClass("span10");
            this.$element.data('isInit', true);
        }

        if (jQuery("#PinaxFormEditgooglemaps").length == 0) {
            window.PinaxFormEditgooglemaps = {};
            html = '<div id="PinaxFormEditgooglemaps" class="mapPicker" style="width: 600px; height: 400px; background: #fff; border: 1px solid #ccc; padding: 5px; position: absolute; z-index: 3000; display: none; overflow: hidden"><div id="leafletmap" style="max-width: 100%; min-width: 471px; height: 400px;"></div></div>';
            jQuery('body').append(html);
        }

        this.elementMap = jQuery("#PinaxFormEditgooglemaps");

        this.elementMap.click(function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
        } );

        this.$element.click(function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
        } );

        jQuery(document).click( function( e ) {
            self.closeMap();
        } );

        this.$element.next().click( function( e ) {
            if ( e.stopPropagation ) {
                e.stopPropagation();
            }
            e.cancelBubble = true;
            self.search();
        } );
    },

    trim: function (str)
    {
       var str = str.replace(/^\s\s*/, ''),
                ws = /\s/,
                i = str.length;
        while (ws.test(str.charAt(--i)));
        return str.slice(0, i + 1);
    },

    roundDecimal: function( num, decimals )
    {
        var mag = Math.pow(10, decimals);
        return Math.round(num * mag)/mag;
    },

    getDefaultCurrentPosition: function()
    {
        var posStr = this.$element.val();
        if(posStr != "")
        {
            var posArr = posStr.split(",");
            if(posArr.length == 2 || posArr.length == 3 )
            {
                var lat = this.trim( posArr[0] );
                var lng = this.trim( posArr[1] );
                var zoom = posArr.length == 3 ? parseInt( this.trim( posArr[2] ) ) : 15;
                return [lat, lng, zoom ];
            }
        }
        return [ 51.500152, -0.126236, 15 ];
    },

    getCurrentPosition: function()
    {
        var pos = this.getDefaultCurrentPosition();
        this.setPosition(pos[0], pos[1]);
    },

    setPosition: function(lat, lng)
    {
        var lat = this.roundDecimal( lat, 6 );
        var lng = this.roundDecimal( lng, 6 );

        this.marker.setLatLng(L.latLng(lat, lng));

        var zoom = this.map.getZoom();
        this.map.setView(this.marker.getLatLng(), zoom);
        this.$element.val(lat + "," + lng+","+zoom);
    },

    setPositionValues: function()
    {
        if( this.elementMap.css("display") != "none")
        {
            var pos = this.getDefaultCurrentPosition();

            pos[ 2 ] = this.map.getZoom();
            this.$element.val( pos.join( "," ) );
        }
    },

    isLngLat: function (val)
    {
        var lngLatArr = val.split(",");
        if(lngLatArr.length == 2 || lngLatArr.length == 3 ){
            if(isNaN(lngLatArr[0]) || isNaN(lngLatArr[1])){
                return false;
            }else{
                return true;
            }
        }
        return false;
    },

    openMap: function()
    {
        this.elementMap.css("left", this.$element.offset().left);
        this.elementMap.css("top", this.$element.offset().top);
        this.elementMap.css("width", this.$element.width());
        this.elementMap.css("display", "block");

        if (window.PinaxFormEditgooglemaps.map == undefined) {
            var pos = this.getDefaultCurrentPosition();

            window.PinaxFormEditgooglemaps.map = L.map('leafletmap', {
                doubleClickZoom: false
            }).setView([pos[0], pos[1]], pos[2]);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(window.PinaxFormEditgooglemaps.map);

            window.PinaxFormEditgooglemaps.marker = L.marker([pos[0], pos[1]], {
                draggable: true
            }).addTo(window.PinaxFormEditgooglemaps.map);

            this.map = window.PinaxFormEditgooglemaps.map;
            this.marker = window.PinaxFormEditgooglemaps.marker;

            var self = this;
            this.map.on('dblclick', function(e) {
                self.setPosition(e.latlng.lat, e.latlng.lng);
            });

            this.marker.on('dragend', function(e) {
                self.setPosition(window.PinaxFormEditgooglemaps.marker._latlng.lat, window.PinaxFormEditgooglemaps.marker._latlng.lng);
            });

            function myButton(){
                var btn = $('<div class="close-button" style="z-index: 1000 !important; position: absolute; top: 0px; right: 0px">'+PinaxLocale.GoogleMap.close+'</div>');
                btn.bind('click', function(){
                    self.setPositionValues();
                    self.closeMap();
                });
                return btn[0];
            }
            jQuery('#PinaxFormEditgooglemaps').append(myButton());
        }

        this.map.panTo(this.marker.getLatLng());
    },

    closeMap: function()
    {
        this.elementMap.css("display", "none");
        if (window.PinaxFormEditgooglemaps.map != undefined) {
            window.PinaxFormEditgooglemaps.map.remove();
            window.PinaxFormEditgooglemaps.map = undefined;
        }
    },

    search: function()
    {
        this.findAddress();
    },

    findAddress: function()
    {
        var self = this;
        var address = this.$element.val();
        if(address == ""){
            alert(PinaxLocale.GoogleMap.error_1);
        }else{
            if(this.isLngLat(address)){
                this.openMap();
                self.getCurrentPosition();
            }else{
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search?q=' + address + '&format=json&limit=1',
                    success: function(result) {
                        if (result.length) {
                            self.element.val(result[0].lat + ',' + result[0].lon + ',15');
                            self.openMap();
                            self.setPosition(
                                result[0].lat,
                                result[0].lon
                            );
                        } else {
                            alert(PinaxLocale.GoogleMap.error_1);
                        }
                    }
                });
            }
            this.focus();
        }
    },

    destroy: function() {
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        this.$element.addClass(className);
    },

    removeClass: function(className) {
        this.$element.removeClass(className);
    }
});
