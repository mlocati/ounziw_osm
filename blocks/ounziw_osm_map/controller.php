<?php

namespace Concrete\Package\OunziwOsm\Block\OunziwOsmMap;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Geolocator\GeolocationResult;

class Controller extends BlockController
{
    protected $btInterfaceWidth = 400;

    protected $btInterfaceHeight = 700;

    protected $btTable = 'btOunziwosmmap';

    protected $btDefaultSet = 'multimedia';

    protected $units = ['px', 'vw', 'vh', 'em', 'rem', 'vx'];

    public function getBlockTypeDescription()
    {
        return t('Displays a map using OpenStreetMap and Leaflet.');
    }

    public function getBlockTypeName()
    {
        return t('Free Map');
    }

    public function add()
    {
        $this->getLatLng();
    }

    public function on_start()
    {
        // block identifier
        $this->set('unique_identifier', $this->app->make('helper/validation/identifier')->getString(18));

        // load leaflet js/css
        $al = AssetList::getInstance();
        $al->register(
            'javascript',
            'leaflet',
            'js/leaflet.js',
            ['position' => Asset::ASSET_POSITION_HEADER, 'version' => '1.9.3'],
            'ounziw_osm'
        );
        $al->register(
            'css',
            'leaflet',
            'css/leaflet.css',
            ['version' => '1.9.3'],
            'ounziw_osm'
        );
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('css', 'leaflet');
        $this->requireAsset('javascript', 'leaflet');
    }

    public function validate($args)
    {
        $error = $this->app->make('helper/validation/error');

        if (!$this->validate_sizeunit($args['width'])) {
            $error->add(t('Width must be either one of; number, number + unit (px, vw, vh, em, rem, vx, %).'));
        }
        if (!$this->validate_sizeunit($args['height'])) {
            $error->add(t('Height must be either one of; number, number + unit (px, vw, vh, em, rem, vx, %).'));
        }

        if (trim($args['latitude']) === '' || trim($args['longitude']) === '') {
            $error->add(t('You must select a valid location.'));
        }
        if (!is_numeric($args['latitude'])) {
            $error->add(t('Latitude must be a floating number.'));
        }
        if (!is_numeric($args['longitude'])) {
            $error->add(t('Longitude must be a floating number.'));
        }

        if (!is_numeric($args['zoom'])) {
            $error->add(t('Please enter a zoom number from 1 to 21.'));
        }

        if ($args['marker']) {
            if (mb_strlen($args['message']) > 1000) {
                $error->add(t('Message must be at most 1000 chars.'));
            }
            if (trim($args['markerlatitude']) === '' || trim($args['markerlongitude']) === '') {
                $error->add(t('You must select a valid location for the marker.'));
            }

            if (!is_numeric($args['markerlatitude'])) {
                $error->add(t('MarkerLatitude must be a floating number.'));
            }
            if (!is_numeric($args['markerlongitude'])) {
                $error->add(t('Marker Longitude must be a floating number.'));
            }
        }
        if ($args['zindex']) {
            if (!is_numeric($args['zindexval'])) {
                $error->add(t('zindexval must be a integer.'));
            }
        }

        return $error;
    }

    public function save($data)
    {
        $args['width'] = isset($data['width']) ? trim($data['width']) : '';
        $args['height'] = isset($data['height']) ? trim($data['height']) : '';
        $args['zoom'] = ((int) ($data['zoom']) >= 1 && (int) ($data['zoom']) <= 21) ? (int) ($data['zoom']) : 14;
        $args['latitude'] = is_numeric($data['latitude']) ? $data['latitude'] : 0;
        $args['longitude'] = is_numeric($data['longitude']) ? $data['longitude'] : 0;
        $args['marker'] = $data['marker'] ? 1 : 0;
        $args['expert'] = $data['expert'] ? 1 : 0;
        $args['markerlatitude'] = is_numeric($data['markerlatitude']) ? $data['markerlatitude'] : 0;
        $args['markerlongitude'] = is_numeric($data['markerlongitude']) ? $data['markerlongitude'] : 0;
        $args['message'] = isset($data['message']) ? trim($data['message']) : '';
        $args['zindex'] = $data['zindex'] ? 1 : 0;
        $args['zindexval'] = is_numeric($data['zindexval']) ? $data['zindexval'] : 0;

        parent::save($args);
    }

    protected function getLatLng()
    {
        $geolocated = $this->app->make(GeolocationResult::class);
        if ($geolocated && (abs($geolocated->getLatitude()) > 0.00001 || abs($geolocated->getLongitude()) > 0.00001)) {
            $data_lat = $geolocated->getLatitude();
            $data_lng = $geolocated->getLongitude();
            $this->set('zoom', 15);
        } else {
            $data_lat = 35.16809895181293;
            $data_lng = 136.89892888069156;
            $this->set('zoom', 1);
        }
        $this->set('latitude', $data_lat);
        $this->set('longitude', $data_lng);
        $this->set('marker', 1);
        $this->set('markerlatitude', $data_lat);
        $this->set('markerlongitude', $data_lng);
    }

    protected function validate_sizeunit($data)
    {
        $valid = false;
        if (trim($data) === '') {
            $valid = false;
        } elseif (substr($data, -1) === '%') {
            if (strlen($data) - 1 === strspn($data, '0123456789')) {
                $valid = true;
            }
        } elseif (in_array(substr($data, -2), $this->units)) {
            if (strlen($data) - 2 === strspn($data, '0123456789')) {
                $valid = true;
            }
        } else { // number only. It will treated as XXXpx, by adding px in view.php
            if (strlen($data) === strspn($data, '0123456789')) {
                $valid = true;
            }
        }

        return $valid;
    }
}
