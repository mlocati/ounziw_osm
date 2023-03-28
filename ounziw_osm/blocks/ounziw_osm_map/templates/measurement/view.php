<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
$package = Package::getByHandle('ounziw_osm');
$package_path = BASE_URL . $package->getRelativePath() . '/';

if (!$width) {
    $width = '300px';
} else if (strspn($width,'0123456789') == strlen($width)) {
    $width .= 'px';
}
if (!$height) {
    $height = '200px';
} else if (strspn($height,'0123456789') == strlen($height)) {
    $height .= 'px';
}

if (!is_numeric($latitude)) {
    $latitude = 36;
}
if (!is_numeric($longitude)) {
    $longitude = 136;
}
$zoom = filter_var($zoom, FILTER_VALIDATE_INT, array('min_range' => 1, 'max_range' => 21, 'default' => 12));

$c = Page::getCurrentPage();?>
<?php
if ( $c->isEditMode()) {
    ?>
    <div class="ccm-edit-mode-disabled-item" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>">
        <div style="padding: 80px 0px 0px 0px"><?php echo t('Open Street Map disabled in edit mode.')?></div>
    </div>
<?php
} else { //not editmode start ?>
    <div class="maparea" id="map<?php echo $unique_identifier?>" style="width:<?php echo h($width);?>;height:<?php echo h($height);?>;<?php if ($zindex) {echo "z-index:" . h($zindexval) . ";";}?>max-width: 100%" data-lat="<?php echo h($latitude);?>" data-lng="<?php echo h($longitude);?>" data-markerlat="<?php echo h($markerlatitude);?>" data-markerlng="<?php echo h($markerlongitude);?>" data-zoom="<?php echo h($zoom);?>" data-message="<?php echo h($message);?>"></div>
    <?php
    if ($marker) :
        ?>
    <form id="currentlocation">
        <input type="submit" value="<?php echo t('Show the current position and the distance');?>">
    </form>
    <p><span id="distance"></span></p>
        <?php
        endif; // $marker
        ?>
    <script type="text/javascript">
        if($('#map<?php echo $unique_identifier?>')) {
            var map = L.map('map<?php echo $unique_identifier?>').setView([$('#map<?php echo $unique_identifier?>').data("lat"), $('#map<?php echo $unique_identifier?>').data("lng")],$('#map<?php echo $unique_identifier?>').data("zoom"));

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https ://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            <?php
            if ($marker) :
            ?>
            var marker = L.marker(
                [$('#map<?php echo $unique_identifier?>').data("markerlat"), $('#map<?php echo $unique_identifier?>').data("markerlng")]
            ).addTo(map) ;
            if ($('#map<?php echo $unique_identifier?>').data("message") != '') {
                marker.bindPopup($('#map<?php echo $unique_identifier?>').data("message")).openPopup() ;
            }
            <?php
            endif; // $marker
            ?>
        }
        </script>
    <?php
    if ($marker) :
        ?>
    <script type="text/javascript">
        function ounziw_osm_successCallback(position) {
            var point = L.latLng($('#map<?php echo $unique_identifier?>').data("markerlat"), $('#map<?php echo $unique_identifier?>').data("markerlng"));
            var point1 =  L.latLng(position.coords.latitude, position.coords.longitude);
            var distance = L.CRS.Earth.distance(point, point1);
            $("#distance").text(parseInt(distance)+' m');
            map.fitBounds(
                [point, point1],
                {paddingTopLeft:[0,20]}
            );

            var currentPosition = L.icon({
                iconUrl: '<?php echo $package_path;?>images/current-position.png',
                iconSize: [32, 32],
                iconAnchor: [13, 32],
            });
            var markers = L.marker(
                point1, {icon: currentPosition}
            ).addTo(map);
        }

        function ounziw_osm_errorCallback(error) {
            var err_msg = "";
            switch(error.code)
            {
                case 1:
                    err_msg = "<?php echo t('Location access is not allowed.');?>";
                    break;
                case 2:
                    err_msg = "<?php echo t('Current position is not obtained.');?>";
                    break;
                case 3:
                    err_msg = "<?php echo t('Timed out.');?>";
                    break;
            }
            document.getElementById("distance").innerHTML = err_msg;
        }
        $('#currentlocation').click(function(e){
            e.preventDefault();
            navigator.geolocation.getCurrentPosition(ounziw_osm_successCallback, ounziw_osm_errorCallback);
        });
    </script>
    <?php
    endif; // $marker
    ?>
<?php  } //not editmode end ?>