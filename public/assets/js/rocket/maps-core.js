// Maps Core - Funciones principales JQuery para mapas
var flag = true;

$(document).ready(function(e) {
	if(flag) {
        loadmap(0);

        //get routers
        getRouters();

        flag = false;
	}

    $(document).on('change', '#router', function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        var router = $('#router').val();
        loadmap(router);
    });

});

function getRouters () {
    $.ajax({
        "url":"/client/getclient/routers",
        "type":"POST",
        "data":{},
        "dataType":"json",
        'error': function (xhr, ajaxOptions, thrownError) {
            debug(xhr,thrownError);
        }
    }).done(function(data){
        if(data.msg=='norouters'){
            msg('No se encontraron <b>routers</b>, debe agregar al menos un router.','system');
        }
        else{

            $.each(data, function(i, val) {
                $('#router').append($('<option>').text(val['name']).attr('value', val.id));
            });

        }


    });
}

function initialise_google_maps(selector, latlng, zoom) {
    var myLatlng = new google.maps.LatLng(latlng.lat, latlng.lng);
    var mapOptions = {center: myLatlng,zoom: zoom, mapTypeId: google.maps.MapTypeId.ROADMAP};
    
    return new google.maps.Map(document.getElementById(selector), mapOptions);
}

function initialise_open_street_map(selector, latlng, zoom) {
    let osm_layer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '© OpenStreetMap'
    });
    let map = L.map(selector, {
        center: [latlng.lat, latlng.lng],
        zoom: zoom,
        layers: [osm_layer],
        gestureHandling: true,
        fullscreenControl: true,
    });

    let esri_satellite_layer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });
    let esri_places_layer = L.tileLayer('https://server.arcgisonline.com/arcgis/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}');
    var baseMaps = {
        "OpenStreetMap": osm_layer,
        "Satellite": esri_satellite_layer
    };

    var overlayMaps = {
        "Boundaries & Places": esri_places_layer
    };

    L.control.layers(baseMaps, overlayMaps).addTo(map);
    
    window.drawnItems = new L.FeatureGroup();
    window.markerGroup = new L.LayerGroup();

    map.addLayer(window.drawnItems);
    map.addLayer(window.markerGroup);

    var drawControl = new L.Control.Draw({
        draw: {
            polygon: false,
            circle: false,
            circlemarker: false,
            rectangle: false,
            polyline: false,
            marker: false
        },
        edit: {
            featureGroup: window.drawnItems,
            remove: false
        }
    });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, function (e) {
        const layer = e.layer;
        window.drawnItems.addLayer(layer);
    });
    map.on(L.Draw.Event.EDITED, function (e) {
        var layers = e.layers;
         layers.eachLayer(function (layer) {
            if (typeof layer.id != 'undefined') {
                saveGeoJson(layer);
            }
        });
    });
    return map;
}

function destroy_osm() {
    if (typeof window.default_map != 'undefined' && window.default_map.map != null) {
        window.default_map.map.remove();
        window.default_map.map = null;
    }
}

function plot_marker_on_google_maps(item) {
    var marker = new google.maps.Marker({
        animation: google.maps.Animation.DROP,
        position: new google.maps.LatLng(item.latitud, item.longitud),
        map: window.default_map.map,
        animation: google.maps.Animation.DROP,
        icon:item.icono,
    });

    window.default_map.map.setCenter(marker.position);

    if(item.elongitud){

        var flightPlanCoordinates = [{lat: Number(item.elatitud), lng: Number(item.elongitud)},{lat: Number(item.latitud), lng: Number(item.longitud)}];
        var flightPath = new google.maps.Polyline({
            path: flightPlanCoordinates,
            geodesic: true,
            strokeColor: '#000000',
            strokeOpacity: 0.30,
            strokeWeight: 2
        });

        flightPath.setMap(window.default_map.map);
    }

    var streetmap=item.descr+'<div id="carousel-example-generic" class="carousel slide" data-ride="carousel"><ol class="carousel-indicators"><li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li><li data-target="#carousel-example-generic" data-slide-to="1" class=""></li><li data-target="#carousel-example-generic" data-slide-to="2" class=""></li><li data-target="#carousel-example-generic" data-slide-to="3" class=""></li></ol><div class="carousel-inner"><div class="item"><img src="https://maps.googleapis.com/maps/api/streetview?size=520x280&location='+item.latitud+','+item.longitud+'&fov=70&heading=0&pitch=0&key=AIzaSyATdbhNZE8E5igzSiXXCuchhsSXjW85ipU" /></div><div class="item"><img src="https://maps.googleapis.com/maps/api/streetview?size=520x280&location='+item.latitud+','+item.longitud+'&fov=70&heading=90&pitch=0&key=AIzaSyATdbhNZE8E5igzSiXXCuchhsSXjW85ipU" /></div><div class="item active"><img src="https://maps.googleapis.com/maps/api/streetview?size=520x280&location='+item.latitud+','+item.longitud+'&fov=70&heading=180&pitch=0&key=AIzaSyATdbhNZE8E5igzSiXXCuchhsSXjW85ipU" /></div><div class="item"><img src="https://maps.googleapis.com/maps/api/streetview?size=520x280&location='+item.latitud+','+item.longitud+'&fov=70&heading=270&pitch=0&key=AIzaSyATdbhNZE8E5igzSiXXCuchhsSXjW85ipU" /></div></div><a class="left carousel-control" href="#carousel-example-generic" data-slide="prev"><span class="fa fa-angle-left"></span></a><a class="right carousel-control" href="#carousel-example-generic" data-slide="next"><span class="fa fa-angle-right"></span></a></div>';

    google.maps.event.addListener(marker, 'click', function() {
        if (typeof infowindow != 'undefined') {
            infowindow.close();
        }

        infowindow = new google.maps.InfoWindow({ content: streetmap});
        infowindow.open(window.default_map.map,marker);
    });
}

function plot_marker_on_open_street_map(item) {
    let icon;
    switch (item.icon.type) {
        case 'image':
            icon = L.icon({
                iconUrl: item.icon.url,
                iconSize: [item.icon.size[0], item.icon.size[1]],
                iconAnchor: [item.icon.size[0]/2, item.icon.size[1]]
            });
            break;
    
        case 'remixicon':
            icon = L.divIcon({
                className: `remixicon iconanchor${item.icon.size[0]}`,
                iconSize: [item.icon.size[0], item.icon.size[1]],
                html: `<i style="color:${item.icon.color};font-size:${item.icon.size[0]}px" class="${item.icon.code}"></i>`
            });
            break;
    }

    let marker = L.marker([item.latitud, item.longitud], {
        icon: icon,
        bounceOnAdd: window.default_map.animation,
        draggable: true
    }).addTo(window.markerGroup);

    marker.meta = {
        id: item.id,
        type: item.type
    };

    window.default_map.map.setView(new L.LatLng(item.latitud, item.longitud));

    marker.on('moveend', function (event) {
        const latlng = event.target.getLatLng();
        updateCoordinates({
            marker_type: event.target.meta.type, 
            id: event.target.meta.id
        }, 
        `${latlng.lat},${latlng.lng}`
        ).done(function () {
            window.default_map.animation = false;
            constructMap( $('#router').val());
        });
    });

    if(item.elongitud){
        plot_line_on_open_street_maps(item);
    }

    var popup_content = item.descr 
        + `<div class="text-center"><a href="#" class="change-marker-icon" data-marker-id="${item.id}" data-marker-type="${item.type}" data-marker-icon-type="${item.icon.type}" data-marker-icon-code="${item.icon.code}" data-marker-icon-url="${item.icon.url}"><i class="fa fa-pencil"></i> Change Icon</a></div>`;
    marker.bindPopup(popup_content);
}

function plot_line_on_open_street_maps(item) {
    let jeoJson;
    if (item.geo_json != null) {
        jeoJson = [{
            "type":"FeatureCollection",
            "features":[item.geo_json]
        }];
    } else {
        jeoJson = [{
            "type":"FeatureCollection",
            "features":[{
                "type":"Feature",
                "properties":{},
                "geometry":{
                    "type":"LineString",
                    "coordinates": [
                        [Number(item.elongitud), Number(item.elatitud)],
                        [Number(item.longitud), Number(item.latitud)]
                    ]
                }
            }]
        }];
    }

    L.geoJson(jeoJson, {
        onEachFeature: (feature, layer) => {
            layer.on({
                click: (e) => {
                    $('#my_color_picker')
                        .ColorPickerSetColor((item.geo_json_styles == null ? layer.options.color : item.geo_json_styles.color));
                    $('#change_line_color').data('layer-id', item.client_service_id);
                    $('#change_line_color').modal('show');
                }
            });
            if (item.geo_json_styles != null) {
                layer.setStyle({
                    color: item.geo_json_styles.color
                });
            }
            layer.id = item.client_service_id;
            window.drawnItems.addLayer(layer);
        }
    });
}

function plot_marker(item) {
    switch (window.default_map.map_type) {
        case 'google_map':
            plot_marker_on_google_maps(item);
            break;
        case 'open_street_map':
            plot_marker_on_open_street_map(item);
            break;
    }
}

function initialise_map(selector, map_type, latlng, zoom, animation) {

    if (typeof window.default_map == 'undefined' || window.default_map.map == null) {
        window.default_map = {
            map_type: map_type,
            map: null
        };
        switch (map_type) {
            case 'google_map':
                window.default_map.map = initialise_google_maps(selector, latlng, zoom);
                break;
        
            case 'open_street_map':
                window.default_map.animation = animation;
                window.default_map.map = initialise_open_street_map(selector, latlng, zoom);
                break;
        }
    }

    return window.default_map;
}

function loadmap(router, zoom = 16, animation = true) {

    let map_type = $('#map-default').data('map-type');
    initialise_map('map-default', map_type, {
        lat: 0.0,
        lng: -0.0
    }, zoom, animation);

    constructMap(router);
}

function constructMap(router) {
    $.ajax({
        "type": "POST",
        "url": baseUrl+"/map/get/gpsmap",
        "data": {'router':router},
        "dataType": "json"
    }).done(function(data){

        if (typeof window.markerGroup !== 'undefined') {
            window.markerGroup.clearLayers();
            window.drawnItems.clearLayers();
        }

        $.each(data, function(index, item){
            plot_marker(item);
        });

    });//end ajax
}

function updateCoordinates(meta, coordinates) {

    let url_endpoint = '';

    switch(meta.marker_type){
        case 'client':
            url_endpoint = 'clients';
        break
        case 'router':
            url_endpoint = 'routers';
        break
    }
    return $.ajax({
        "type": "PATCH",
        "url": `${baseUrl}/${url_endpoint}/${meta.id}/update-coordinates`,
        "data": {
            coordinates: coordinates
        }
    });
}

function updateMarker(meta, icon) {

    let url_endpoint = '';

    switch(meta.marker_type){
        case 'client':
            url_endpoint = 'clients';
        break
        case 'router':
            url_endpoint = 'routers';
        break
    }
    return $.ajax({
        "type": "PATCH",
        "url": `${baseUrl}/${url_endpoint}/${meta.id}/update-map-marker-icon`,
        "data": {
            map_marker_icon: icon,
        }
    });
}

function saveGeoJson(layer) {
    let geoFeature = layer.toGeoJSON();
    $.ajax({
        "type": "PATCH",
        "url": `${baseUrl}/clients/services/${layer.id}/geo-json`,
        "data": {
            geo_json: geoFeature,
            geo_json_styles: getStyle(layer)
        },
        "dataType": "json"
    });
}

function updateLayerStyle(id, style) {
    window.drawnItems.eachLayer(function (layer) {
        if (layer.id == id) {
            layer.setStyle(style);
            saveGeoJson(layer);
        }
    });
}

function getStyle(layer) {
    return {
        color: layer.options.color,
    }
}
$('#my_color_picker').ColorPicker({flat: true, 
    onChange: function(hsb, hex, rgb, el) {
        updateLayerStyle($('#change_line_color').data('layer-id'), {color: `#${hex}`});
    }}
);
$('#map-default').on('click', '.change-marker-icon', function (e) {
    e.preventDefault();
    let marker_type = $(this).data('marker-type');
    let marker_icon_code = $(this).data('marker-icon-code');
    let marker_icon_type = $(this).data('marker-icon-type');

    let marker_icons = '';

    switch (marker_type) {
        case 'router':
            marker_icons += `<li class="${((marker_icon_code == 'image_tower') ? 'selected' : '')}" data-marker-icon-code="image_tower" data-marker-icon-type="image"><img src="/assets/markers/tower.png" /></li>`
            marker_icons += `<li class="${((marker_icon_code == 'ri-database-2-fill') ? 'selected' : '')}" data-marker-icon-code="ri-database-2-fill" data-marker-icon-type="remixicon" data-marker-icon-size="48"><i class="ri-database-2-fill size-48"></i></li>`
            break;
    
        case 'client':
            marker_icons += `<li class="${((marker_icon_code == 'image_device') ? 'selected' : '')}" data-marker-icon-code="image_device" data-marker-icon-type="image"><img src="/assets/markers/device.png" /></li>`
            marker_icons += `<li class="${((marker_icon_code == 'ri-home-wifi-fill') ? 'selected' : '')}"  data-marker-icon-code="ri-home-wifi-fill" data-marker-icon-type="remixicon" data-marker-icon-size="32"><i class="ri-home-wifi-fill size-32"></i></li>`
            break;
    }
    $('#change_marker_icon').data('marker-type', marker_type);
    $('#change_marker_icon').data('marker-id', $(this).data('marker-id'));

    $('#change_marker_icon').find('.icons > ul').html(marker_icons);
    $('#change_marker_icon').modal('show');

    $('.color-picker-container').hide();

    $('#marker_icon_color_picker').ColorPicker({flat: true, 
        onChange: function(hsb, hex, rgb, el) {
            $('[name="marker-icon-color"]').val(`#${hex}`);
            $('#change_marker_icon').find('.icons > ul > li.selected').css({color: `#${hex}`});
        }
    });

    if (marker_icon_type == 'remixicon') {
        $('.color-picker-container').show();
    }
});

$('#change_marker_icon').on('click', '.icons > ul > li', function (e) {
    e.preventDefault();
    let marker_icon_type = $(this).data('marker-icon-type');

    $('.icons > ul > li').each(function () {
        $(this).removeClass('selected');
    });
    $(this).addClass('selected');

    if (marker_icon_type == 'remixicon') {
        $('.color-picker-container').show();
        return;
    }

    $('.color-picker-container').hide();
});

$('#update-marker-icon').click(function () {
    let selected_icon = $('#change_marker_icon').find('.icons > ul > li.selected');
    updateMarker(
        {
            marker_type: $(this).closest('#change_marker_icon').data('marker-type'),
            id: $(this).closest('#change_marker_icon').data('marker-id')
        },
        {
            'type': selected_icon.data('marker-icon-type'),
            'size': [selected_icon.data('marker-icon-size'), selected_icon.data('marker-icon-size')],
            'color': $('[name="marker-icon-color"]').val(),
            'code': selected_icon.data('marker-icon-code')
        }
    ).done(function () {
        constructMap( $('#router').val());
        $('#change_marker_icon').modal('hide');
    });
});