<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php

if (!$width) {
    $width = '300px';
} elseif (strspn($width, '0123456789') == strlen($width)) {
    $width .= 'px';
}
if (!$height) {
    $height = '200px';
} elseif (strspn($height, '0123456789') == strlen($height)) {
    $height .= 'px';
}

if (!is_numeric($latitude)) {
    $latitude = 36;
}
if (!is_numeric($longitude)) {
    $longitude = 136;
}
$zoom = filter_var($zoom, FILTER_VALIDATE_INT, ['min_range' => 1, 'max_range' => 21, 'default' => 12]);

$c = Page::getCurrentPage(); ?>
<?php
if ($c->isEditMode()) {
    ?>
    <div class="ccm-edit-mode-disabled-item" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>">
        <div style="padding: 80px 0px 0px 0px"><?php echo t('Open Street Map disabled in edit mode.')?></div>
    </div>
    <?php
} else { //not editmode start
    ?>
    <div class="maparea" id="map<?php echo $unique_identifier?>" style="width:<?php echo h($width); ?>;height:<?php echo h($height); ?>;<?php if ($zindex) {echo 'z-index:' . h($zindexval) . ';'; }?>max-width: 100%" data-lat="<?php echo h($latitude); ?>" data-lng="<?php echo h($longitude); ?>" data-markerlat="<?php echo h($markerlatitude); ?>" data-markerlng="<?php echo h($markerlongitude); ?>" data-zoom="<?php echo h($zoom); ?>" data-message="<?php echo h($message); ?>"></div>
    <script type="text/javascript">
        if($('#map<?php echo $unique_identifier?>')) {
            var map = L.map('map<?php echo $unique_identifier?>',{zoomControl: false}).setView([$('#map<?php echo $unique_identifier?>').data("lat"), $('#map<?php echo $unique_identifier?>').data("lng")],$('#map<?php echo $unique_identifier?>').data("zoom"));

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            <?php
            if ($marker) {
            ?>
            var marker = L.marker(
                [$('#map<?php echo $unique_identifier?>').data("markerlat"), $('#map<?php echo $unique_identifier?>').data("markerlng")]
            ).addTo(map) ;
            if ($('#map<?php echo $unique_identifier?>').data("message") != '') {
                marker.bindPopup($('#map<?php echo $unique_identifier?>').data("message")).openPopup() ;
            }
            <?php
            } // $marker
            ?>
        }
    </script>
    <?php
} //not editmode end
