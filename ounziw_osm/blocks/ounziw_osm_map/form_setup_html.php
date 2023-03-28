<?php defined( 'C5_EXECUTE' ) or die( "Access Denied." );
?>
<style>
    .col-12, .col-2, .col-3, .col-4, .col-9 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .col-12 {
        padding-bottom: 10px;
    }

    .mapedit {
        position: absolute;
        z-index: 10;
        width: 380px;
        height: 200px;
        max-width: 100%;
    }

    .gpsbtn {
        position: absolute;
        margin-top: 205px;
        margin-bottom: 5px;
    }

    .mapdetail {
        margin-top: 245px;
    }

    #err_msg {
        display: block;
    }
</style>
<div class="form-group">
    <div class="row">
        <div class="col-12">
            <div id="mapedit<?php echo $unique_identifier ?>" class="mapedit"></div>
            <div class="gpsbtn">
                <button class="btn btn-primary applygps"><?php echo t( "Apply current location using GPS" ); ?></button>
                <span id="err_msg"></span>
            </div>
        </div>
    </div>
</div>
<div class="mapdetail">
    <div class="form-group">
		<?php echo $form->label( 'width', t( 'width' ) ) ?>
		<?php echo $form->text( 'width', $width, [ 'maxlength' => 8 ] ) ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'height', t( 'height' ) ) ?>
		<?php echo $form->text( 'height', $height, [ 'maxlength' => 8 ] ) ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'marker', t( 'Marker' ) ) ?>
		<?php echo $form->checkbox( 'marker', 1, $marker ) ?><?php echo t( 'Display a Marker' ); ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'message', t( 'Message' ) . t( '(max 1000 chars)' ) ) ?>
		<?php echo $form->textarea( 'message', $message, [ 'col' => 4, 'maxlength' => 1000 ] ) ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'zindex', t( 'z-index' ) ) ?>
		<?php echo $form->checkbox( 'zindex', 1, $zindex ) ?><?php echo t( 'Manually sets z-index value' ); ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'zindexval', t( 'Z-index Value' ) ) ?>
		<?php echo $form->number( 'zindexval', $zindexval, [ 'min' => -2147483647, 'max' => 2147483647, 'step' => 1 ] ) ?>
    </div>
    <div class="form-group">
		<?php echo $form->label( 'expert', t( 'Expert Mode' ) ) ?>
		<?php echo $form->checkbox( 'expert', 1, $expert ) ?><?php echo t( 'Display lat/lng values' ); ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
		<?php echo $form->label( 'latitude', t( 'Latitude' ) ) ?>
		<?php echo $form->text( 'latitude', $latitude, [ 'maxlength' => 20 ] ) ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
		<?php echo $form->label( 'longitude', t( 'Longitude' ) ) ?>
		<?php echo $form->text( 'longitude', $longitude, [ 'maxlength' => 20 ] ) ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
		<?php echo $form->label( 'zoom', t( 'Zoom' ) ) ?>
		<?php echo $form->number( 'zoom', $zoom, [ 'min' => 1, 'max' => 21, 'step' => 1 ] ) ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
		<?php echo $form->label( 'markerlatitude', t( 'Marker lat.' ) ) ?>
		<?php echo $form->text( 'markerlatitude', $markerlatitude, [ 'maxlength' => 20 ] ) ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
		<?php echo $form->label( 'markerlongitude', t( 'Marker lng.' ) ) ?>
		<?php echo $form->text( 'markerlongitude', $markerlongitude, [ 'maxlength' => 20 ] ) ?>
    </div>
    <div class="form-group expert<?php if ( ! $expert ) {
		echo ' d-none';
	} ?>">
        <button class="btn btn-primary commit"><?php echo t( 'Commit to MAP' ); ?></button>
    </div>
</div>
<script>

    $('#mapedit<?php echo $unique_identifier?>').ready(function () {
        setTimeout(function () {

            if (!isNaN($("#latitude").val()) && !isNaN($("#longitude").val()) && !isNaN($("#zoom").val())) {
                lat = $("#latitude").val();
                lng = $("#longitude").val();
                zoom = $("#zoom").val();
            } else {
                lat = 35.16809895181293;
                lng = 136.89892888069156;
                zoom = 15;
            }

            var mapedit = L.map('mapedit<?php echo $unique_identifier?>').setView([lat, lng], zoom);

            var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });
            mapedit.addLayer(tiles);

            if (!isNaN($("#markerlatitude").val()) && !isNaN($("#markerlongitude").val())) {
                markerlat = $("#markerlatitude").val();
                markerlng = $("#markerlongitude").val();
            } else {
                markerlat = lat;
                markerlng = lng;
            }

            var markers = L.marker(
                [markerlat, markerlng],
            );

            if ($("#marker").prop('checked')) {
                markers.addTo(mapedit);
                $("#message").prop("disabled", false);
            } else {
                $("#message").prop("disabled", true);
            }
            if ($("#zindex").prop('checked')) {
                $("#zindexval").prop("disabled", false);
            } else {
                $("#zindexval").prop("disabled", true);
            }


            function onMapMoveend(e) {
                var center = mapedit.getCenter();
                $("#latitude").val(center.lat);
                $("#longitude").val(center.lng);
            }

            mapedit.on('moveend', onMapMoveend);

            function onMapClick(e) {
                markers.setLatLng(e.latlng).addTo(mapedit);
                $("#markerlatitude").val(e.latlng.lat);
                $("#markerlongitude").val(e.latlng.lng);
            }

            mapedit.on('click', onMapClick);

            function onMapZoom(e) {
                var center = mapedit.getZoom();
                $("#zoom").val(center);
            }

            mapedit.on('zoom', onMapZoom);

            $("#marker").on('change', function () {
                if ($(this).prop('checked')) {
                    markers.addTo(mapedit);
                    $("#message").prop("disabled", false);
                } else {
                    markers.remove();
                    $("#message").prop("disabled", true);
                }
            });

            $("#zindex").on('change', function () {
                if ($(this).prop('checked')) {
                    $("#zindexval").prop("disabled", false);
                } else {
                    $("#zindexval").prop("disabled", true);
                }
            });
            $("#expert").on('change', function () {
                if ($(this).prop('checked')) {
                    $(".expert").removeClass('d-none');
                } else {
                    $(".expert").addClass('d-none');
                }
            });

            $("button.commit").on('click', function (e) {
                e.preventDefault();
                mapedit.panTo([$("#latitude").val(), $("#longitude").val()]).setZoom($("#zoom").val());
                markers.setLatLng([$("#markerlatitude").val(), $("#markerlongitude").val()]);
            });

            $(".applygps").on('click', function (e) {
                e.preventDefault();
                if (location.protocol == 'https:') {
                    navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
                } else {
                    err_msg = "<?php echo t( 'Https required for accessing your location information.' );?>";
                    $("#err_msg").text(err_msg);
                }
            });


            // location access success
            function successCallback(position) {
                mapedit.panTo(
                    [position.coords.latitude, position.coords.longitude]
                );
            }

            // location access failure
            function errorCallback(error) {
                var err_msg = "";
                switch (error.code) {
                    case 1:
                        err_msg = "<?php echo t( 'Location access is not allowed.' );?>";
                        break;
                    case 2:
                        err_msg = "<?php echo t( 'Current position is not obtained.' );?>";
                        break;
                    case 3:
                        err_msg = "<?php echo t( 'Timed out.' );?>";
                        break;
                }
                $("#err_msg").text(err_msg);
            }
        });
    });
</script>