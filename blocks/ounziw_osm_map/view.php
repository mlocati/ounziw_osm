<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var string $package_path
 * @var string $unique_identifier
 * @var string $width
 * @var string $height
 * @var int|string $zoom
 * @var bool|string $zindex
 * @var int|string $zindexval
 * @var float|string $latitude
 * @var float|string $longitude
 * @var bool|int|float $marker
 * @var float|string|null $markerlatitude
 * @var float|string|null $markerlongitude
 * @var int|string $zoom
 * @var string|null $message
 * @var bool|int|string $hideZoomControls
 * @var bool|int|string $showMyPosition
 */

?>
<div class="maparea" id="map<?= $unique_identifier ?>" style="width:<?= $width ?>;height:<?= $height ?>;<?= $zindex ? "z-index:{$zindexval};" : '' ?>max-width:100%"></div>
<?php
if ($marker && $showMyPosition) {
    ?>
    <button type="button" class="btn btn-secondary map-my-position-ask" id="map-my-position-ask<?= $unique_identifier ?>"><?= t('Show the current position and the distance') ?></button>
    <div class="alert alert-info d-none map-my-position-distance" id="map-my-position-distance<?= $unique_identifier ?>"></div>
    <?php
}
?>
<script>
(function() {

var MAP_ID = <?= json_encode("map{$unique_identifier}") ?>;

function initialize()
{
    var options = {};
    <?php
    if ($hideZoomControls) {
        ?>
        options.zoomControl = false;
        <?php
    }
    ?>
    var map = L.map(MAP_ID, options).setView([<?= $latitude ?>, <?= $longitude ?>], <?= $zoom ?>);
    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }
    ).addTo(map);
    <?php
    if ($marker) {
        ?>
        var marker = L.marker([<?= $markerlatitude ?>, <?= $markerlongitude ?>]).addTo(map);
        <?php
        if (($message ?? '') !== '') {
            ?>
            marker.bindPopup(<?= json_encode($message) ?>).openPopup();
            <?php
        }
        if ($showMyPosition) {
            ?>
            var currentPositionMarker = null;
            document.getElementById(<?= json_encode("map-my-position-ask{$unique_identifier}") ?>).addEventListener('click', function(e) {
                e.preventDefault();
                window.navigator.geolocation.getCurrentPosition(
                    function(position) {
                        if (!position || !position.coords) {
                            window.alert(<?= json_encode(t('Unable to retrieve your current position.')) ?>);
                            return;
                        }
                        var markerPoint = marker.getLatLng();
                        var currentPositionPoint =  L.latLng(position.coords.latitude, position.coords.longitude);
                        var distance = L.CRS.Earth.distance(markerPoint, currentPositionPoint);
                        var distanceDiv = document.getElementById(<?= json_encode("map-my-position-distance{$unique_identifier}") ?>);
                        distanceDiv.classList.remove('d-none');
                        distanceDiv.innerText = parseInt(distance) + ' m';
                        map.fitBounds(
                            [markerPoint, currentPositionPoint],
                            {paddingTopLeft: [0,20]}
                        );
                        if (currentPositionMarker === null) {
                            var icon = L.icon({
                                iconUrl: <?= json_encode($package_path . '/images/current-position.png') ?>,
                                iconSize: [32, 32],
                                iconAnchor: [13, 32],
                            });
                            currentPositionMarker = L.marker(currentPositionPoint, {icon}).addTo(map);
                        } else {
                            currentPositionMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
                        }
                    },
                    function(error) {
                        window.alert(error.message);
                    }
                );
            });
            <?php
        }
    }
    ?>
}

if (window.L && window.document && window.document.getElementById(MAP_ID)) {
    initialize();
} else {
    window.addEventListener('DOMContentLoaded', function() {
        initialize();
    });
}

})();
</script>
