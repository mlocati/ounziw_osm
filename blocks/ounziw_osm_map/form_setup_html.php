<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Form\Service\Form $form
 * @var \Concrete\Core\Editor\EditorInterface $editor
 * @var string $unique_identifier
 * @var string[] $units
 * @var string $width
 * @var string $height
 * @var float|string $latitude
 * @var float|string $longitude
 * @var int|string $zoom
 * @var int $zoomMin
 * @var int $zoomMax
 * @var array $tileLayerConfig
 * @var bool|int|string $marker
 * @var bool|int|string $expert
 * @var float|string|null $markerlatitude
 * @var float|string|null $markerlongitude
 * @var string $message
 * @var bool|int|string $zindex
 * @var int|string|null $zindexval
 * @var bool|int|string $hideZoomControls
 * @var bool|int|string $showMyPosition
 */
$mapHeight = '200px';
$latPrecision = 6; // about 0.1 meter
$lngPrecision = 6; // about 0.1 meter at the equator
?>
<style>
    #ccm-block-fields .mapedit {
        position: absolute;
        z-index: 10;
        height: <?= $mapHeight ?>;
        width: calc(100% - 70px);
    }
    #ccm-block-fields .mapedit-spacer {
        height: <?= $mapHeight ?>;
    }
</style>
<div id="mapedit<?= $unique_identifier ?>" class="mapedit"></div>
<div class="mapedit-spacer"></div>
<div class="text-end mb-2">
    <button class="btn btn-sm btn-outline-primary applygps" type="button"><?= t('Apply current location using GPS') ?></button>
</div>

<div class="row">
    <div class="col">
        <div class="form-group">
            <?= $form->label('width', t('Width')) ?>
            <?= $form->text('width', $width, ['maxlength' => 8]) ?>
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <?= $form->label('height', t('Height')) ?>
            <?= $form->text('height', $height, ['maxlength' => 8]) ?>
        </div>
  </div>
</div>

<div class="form-group">
    <div class="form-check">
        <?= $form->checkbox('marker', 1, $marker) ?>
        <?= $form->label('marker', t('Display a marker')) ?>
    </div>
    <div class="form-check">
        <?= $form->checkbox('zindex', 1, $zindex) ?>
        <?= $form->label('zindex', t('Manually sets z-index value')) ?>
    </div>
    <div class="form-check">
        <?= $form->checkbox('hideZoomControls', 1, $hideZoomControls) ?>
        <?= $form->label('hideZoomControls', t('Hide zoom controls')) ?>
    </div>
    <div class="form-check">
        <?= $form->checkbox('showMyPosition', 1, $showMyPosition, ['class' => 'marker-disable']) ?>
        <?= $form->label('showMyPosition', t('Show user position and distance'), ['class' => 'marker-disable']) ?>
    </div>
    <div class="form-check">
        <?= $form->checkbox('expert', 1, $expert) ?>
        <?= $form->label('expert', t('Expert Mode')) ?>
    </div>
</div>
<div class="form-group marker-hide<?= $marker ? '' : ' d-none' ?>">
    <?= $form->label('message', t('Marker Text')) ?>
    <?= $editor->outputBlockEditModeEditor('message', $message) ?>
</div>
<div class="form-group zindex">
    <?= $form->label('zindexval', t('Z-index Value')) ?>
    <?= $form->number('zindexval', $zindexval, ['min' => -2147483647, 'max' => 2147483647, 'step' => 1]) ?>
</div>
<div class="expert<?= $expert ? '' : ' d-none' ?>">
    <div class="row">
        <div class="col">
            <div class="form-group">
                <?= $form->label('latitude', t('Latitude')) ?>
                <?= $form->number('latitude', $latitude, ['min' => -90, 'max' => 90, 'step' => '0.' . str_repeat('0', $latPrecision - 1) . '1']) ?>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $form->label('longitude', t('Longitude')) ?>
                <?= $form->number('longitude', $longitude, ['min' => -180, 'max' => 180, 'step' => '0.' . str_repeat('0', $lngPrecision - 1) . '1']) ?>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $form->label('zoom', t('Zoom')) ?>
                <?= $form->number('zoom', $zoom, ['min' => $zoomMin, 'max' => $zoomMax, 'step' => 1]) ?>
            </div>
        </div>
    </div>
    <div class="row marker-hide">
        <div class="col">
            <div class="form-group">
                <?= $form->label('markerlatitude', t('Marker Latitude')) ?>
                <?= $form->number('markerlatitude', $markerlatitude, ['min' => -90, 'max' => 90, 'step' => '0.' . str_repeat('0', $latPrecision - 1) . '1']) ?>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $form->label('markerlongitude', t('Marker Longitude')) ?>
                <?= $form->number('markerlongitude', $markerlongitude, ['min' => -180, 'max' => 180, 'step' => '0.' . str_repeat('0', $lngPrecision - 1) . '1']) ?>
            </div>
        </div>
    </div>
</div>
<script>$(document).ready(function() {
'use strict';
var UI, map, marker;

function latLngToInput(value, precision)
{
    value = parseFloat(value);
    if (isNaN(value)) {
        return '';
    }
    return value.toFixed(precision).replace(/\.0+$/, '').replace(/(\.[^0]+)0+$/, '$1');
}

var disableUpdateInputsFromMap = false;

function updateInputsFromMap()
{
    if (disableUpdateInputsFromMap) {
        return;
    }
    var center = map.getCenter();
    UI.lat.val(latLngToInput(center.lat, <?= $latPrecision ?>));
    UI.lng.val(latLngToInput(center.lng, <?= $lngPrecision ?>));
    var zoom = map.getZoom();
    UI.zoom.val(zoom);
}

function updateMapFromInputs()
{
    var lat = parseFloat(UI.lat.val());
    var lng = parseFloat(UI.lng.val());
    if (!isNaN(lat) && !isNaN(lng)) {
        map.panTo(
            [lat, lng],
            {
                animate: false,
                noMoveStart: true,
            }
        );
    }
    var zoom = parseInt(UI.zoom.val());
    if (!isNaN(zoom)) {
        map.setZoom(zoom);
    }
}

function updateInputsFromMarker()
{
    var position = marker.getLatLng();
    UI.markerLat.val(latLngToInput(position.lat, <?= $latPrecision ?>));
    UI.markerLng.val(latLngToInput(position.lng, <?= $lngPrecision ?>));
}

function updateMarkerFromInputs()
{
    var lat = parseFloat(UI.markerLat.val());
    var lng = parseFloat(UI.markerLng.val());
    if (!isNaN(lat) && !isNaN(lng)) {
        marker.setLatLng([lat, lng]);
    }
}

function initialize()
{
    UI = {
        lat: $('#ccm-block-fields input[name="latitude"]'),
        lng: $('#ccm-block-fields input[name="longitude"]'),
        zoom: $('#ccm-block-fields input[name="zoom"]'),
        markerLat: $('#ccm-block-fields input[name="markerlatitude"]'),
        markerLng: $('#ccm-block-fields input[name="markerlongitude"]'),
        message: $('#ccm-block-fields input[name="message"]'),
        marker: $('#ccm-block-fields input[name="marker"]'),
        markerHideElements: $('#ccm-block-fields .marker-hide'),
        markerDisableElements: $('#ccm-block-fields .marker-disable'),
        zIndex: $('#ccm-block-fields input[name="zindex"]'),
        zIndexElements: $('#ccm-block-fields .zindex'),
        expert: $('#ccm-block-fields input[name="expert"]'),
        expertElements: $('#ccm-block-fields .expert'),
    }
    createMap();
    updateInputsFromMap();
    updateInputsFromMarker();
    UI.marker
        .on('change', function () {
            if (UI.marker.is(':checked')) {
                marker.setOpacity(1);
            } else {
                marker.setOpacity(0);
            }
            UI.markerHideElements.toggleClass('d-none', UI.marker.is(':checked') ? false : true);
            UI.markerDisableElements
                .toggleClass('text-muted', UI.marker.is(':checked') ? false : true)
                .attr('disabled', UI.marker.is(':checked') ? null : 'disabled')
            ;
        })
        .trigger('change')
    ;
    UI.zIndex
        .on('change', function() {
            UI.zIndexElements.toggleClass('d-none', UI.zIndex.is(':checked') ? false : true);
        })
        .trigger('change')
    ;
    UI.expert
        .on('change', function () {
            if (UI.expert.is(':checked')) {
                UI.expertElements.removeClass('d-none');
            } else {
                UI.expertElements.addClass('d-none');
            }
        })
        .trigger('change')
    ;
    map.on('move', function(e) {
        updateInputsFromMap();
    });
    map.on('zoom', function(e) {
        updateInputsFromMap();
    });
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateInputsFromMarker();
    });
    UI.lat.add(UI.lng).add(UI.zoom)
        .on('focus', function() {
            disableUpdateInputsFromMap = true;
        })
        .on('input', function() {
            updateMapFromInputs();
        })
        .on('blur', function() {
            disableUpdateInputsFromMap = false;
            updateInputsFromMap();
        })
    ;
    UI.markerLat.add(UI.markerLng)
        .on('input', function() {
            updateMarkerFromInputs();
        })
        .on('blur', function() {
            updateInputsFromMarker();
        })
    ;
    $('#ccm-block-fields .applygps').on('click', function (e) {
        e.preventDefault();
        window.navigator.geolocation.getCurrentPosition(
            function (position) {
                map.panTo([position.coords.latitude, position.coords.longitude]);
            },
            function (error) {
                var err_msg;
                switch (error.code) {
                    case 1: // PERMISSION_DENIED
                        if (window.location.protocol !== 'https:') {
                            err_msg = <?= json_encode(t('Https required for accessing your location information.')) ?>;
                        } else {
                            err_msg = <?= json_encode(t('Location access is not allowed.')) ?>;
                        }
                        break;
                    case 2: // POSITION_UNAVAILABLE
                        err_msg = <?= json_encode(t('Current position is not obtained.')) ?>;
                        break;
                    case 3: // TIMEOUT
                        err_msg = <?= json_encode(t('Timed out.')) ?>;
                        break;
                    default:
                        err_msg = error.message;
                        break;
                }
                window.ConcreteAlert.error({
                    message: err_msg,
                    plainTextMessage: true,
                });
            }
        );
    });
}

function createMap()
{
    var lat = parseFloat(UI.lat.val());
    var lng = parseFloat(UI.lng.val());
    var zoom = parseInt(UI.zoom.val());
    map = L.map(<?= json_encode("mapedit{$unique_identifier}") ?>).setView([lat, lng], zoom);
    var tiles = L.tileLayer(
        <?= json_encode($tileLayerConfig['urlTemplate']) ?>,
        <?= json_encode($tileLayerConfig['options']) ?>
    );
    map.addLayer(tiles);
    var markerLat = parseFloat(UI.markerLat.val());
    if (isNaN(markerLat)) {
        markerLat = lat;
    }
    var markerLng = parseFloat(UI.markerLng.val());
    if (isNaN(markerLng)) {
        markerLng = lng;
    }

    marker = L.marker([markerLat, markerLng]);
    marker.addTo(map);
}

setTimeout(function() {
    initialize();
});

});</script>