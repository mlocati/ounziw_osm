<?php

use Concrete\Core\Editor\LinkAbstractor;

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
 * @var bool|int|string $marker
 * @var bool|int|string $expert
 * @var float|string|null $markerlatitude
 * @var float|string|null $markerlongitude
 * @var string|null $message
 * @var bool|int|string $zindex
 * @var int|string|null $zindexval
 * @var bool|int|string $hideZoomControls
 * @var bool|int|string $showMyPosition
 */

$mapHeight = '200px';
$latPrecision = '0.000001'; // as string to avoid rounding or scientific notation
$lngPrecision = '0.000001'; // as string to avoid rounding or scientific notation
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
        <?= $form->label('marker', t('Display a Marker')) ?>
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
    <?= $editor->outputBlockEditModeEditor('message', LinkAbstractor::translateFromEditMode($message)) ?>
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
                <?= $form->number('latitude', $latitude, ['min' => -90, 'max' => 90, 'step' => $latPrecision]) ?>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $form->label('longitude', t('Longitude')) ?>
                <?= $form->number('longitude', $longitude, ['min' => -180, 'max' => 180, 'step' => $lngPrecision]) ?>
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
                <?= $form->label('markerlatitude', t('Marker lat.')) ?>
                <?= $form->number('markerlatitude', $markerlatitude, ['min' => -90, 'max' => 90, 'step' => $latPrecision]) ?>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $form->label('markerlongitude', t('Marker lng.')) ?>
                <?= $form->number('markerlongitude', $markerlongitude, ['min' => -180, 'max' => 180, 'step' => $lngPrecision]) ?>
            </div>
        </div>
    </div>
</div>
<script>$(document).ready(function() {
'use strict';
var UI, map, marker;

function roundLatLng(value, precision)
{
    value = parseFloat(value);
    if (isNaN(value)) {
        return null;
    }
    return Math.round(value / precision) * precision;
}

var disableUpdateInputsFromMap = false;

function updateInputsFromMap()
{
    if (disableUpdateInputsFromMap) {
        return;
    }
    var center = map.getCenter();
    UI.lat.val(roundLatLng(center.lat, <?= $latPrecision ?>));
    UI.lng.val(roundLatLng(center.lng, <?= $lngPrecision ?>));
    var zoom = map.getZoom();
    UI.zoom.val(zoom);
}

function updateMapFromInputs()
{
    var lat = roundLatLng(UI.lat.val(), <?= $latPrecision ?>);
    var lng = roundLatLng(UI.lng.val(), <?= $lngPrecision ?>);
    if (lat !== null && lng !== null) {
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
        marker.setLatLng(e.latlng).addTo(map);
        UI.markerLat.val(roundLatLng(e.latlng.lat, <?= $latPrecision ?>));
        UI.markerLng.val(roundLatLng(e.latlng.lng, <?= $lngPrecision ?>));
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
            var markerLat = roundLatLng(UI.markerLat.val(), <?= $latPrecision ?>);
            var markerLng = roundLatLng(UI.markerLng.val(), <?= $lngPrecision ?>);
            if (markerLat !== null && markerLng !== null) {
                marker.setLatLng([markerLat, markerLng]);
            }
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
    var lat = roundLatLng(UI.lat.val(), <?= $latPrecision ?>);
    var lng = roundLatLng(UI.lng.val(), <?= $lngPrecision ?>);
    var zoom = parseInt(UI.zoom.val());
    map = L.map(<?= json_encode("mapedit{$unique_identifier}") ?>).setView([lat, lng], zoom);
    var tiles = L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }
    );
    map.addLayer(tiles);
    var markerLat = roundLatLng(UI.markerLat.val(), <?= $latPrecision ?>);
    if (markerLat === null) {
        markerLat = lat;
    }
    var markerLng = roundLatLng(UI.markerLng.val(), <?= $lngPrecision ?>);
    if (markerLng === null) {
        markerLng = lng;
    }

    marker = L.marker([markerLat, markerLng]);
    marker.addTo(map);
}

setTimeout(function() {
    initialize();
});

});</script>