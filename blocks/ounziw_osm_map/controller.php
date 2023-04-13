<?php

namespace Concrete\Package\OunziwOsm\Block\OunziwOsmMap;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Geolocator\GeolocationResult;
use Concrete\Core\Package\PackageService;
use Punic\Misc;

class Controller extends BlockController
{
    protected const UNITS = ['%', 'px', 'vw', 'vh', 'em', 'rem', 'vx'];

    protected const ZOOM_MIN = 1;

    protected const ZOOM_MAX = 21;

    protected $btInterfaceWidth = 750;

    protected $btInterfaceHeight = 700;

    protected $btTable = 'btOunziwosmmap';

    protected $btDefaultSet = 'multimedia';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$supportSavingNullValues
     */
    protected $supportSavingNullValues = true;

    /**
     * @var string|null
     */
    protected $width;

    /**
     * @var string|null
     */
    protected $height;

    /**
     * @var float|string|null
     */
    protected $latitude;

    /**
     * @var float|string|null
     */
    protected $longitude;

    /**
     * @var int|string|null
     */
    protected $zoom;

    /**
     * @var bool|int|string|null
     */
    protected $marker;

    /**
     * @var bool|int|string|null
     */
    protected $expert;

    /**
     * @var float|string|null
     */
    protected $markerlatitude;

    /**
     * @var float|string|null
     */
    protected $markerlongitude;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var bool|int|string|null
     */
    protected $zindex;

    /**
     * @var int|string|null
     */
    protected $zindexval;

    /**
     * @var bool|int|string|null
     */
    protected $hideZoomControls;

    /**
     * @var bool|int|string|null
     */
    protected $showMyPosition;

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
        $this->setInitialPosition();
        $this->set('width', '100%');
        $this->set('height', '400px');
        $this->set('expert', false);
        $this->set('message', '');
        $this->set('zindex', false);
        $this->set('zindexval', 1);
        $this->set('hideZoomControls', false);
        $this->set('showMyPosition', false);
        $this->addOrEdit();
    }

    public function edit()
    {
        $this->addOrEdit();
    }

    public function view()
    {
        $package = $this->app->make(PackageService::class)->getByHandle('ounziw_osm');
        $this->set('package_path', BASE_URL . $package->getRelativePath());
        $this->set('tileLayerConfig', $this->app->make(Repository::class)->get('ounziw_osm::leaflet.tileLayer'));
        if ($this->marker) {
            $this->set('message', LinkAbstractor::translateFrom($this->message ?? ''));
        }
    }

    public function on_start()
    {
        // block identifier
        $this->set('unique_identifier', $this->app->make('helper/validation/identifier')->getString(18));

        $al = AssetList::getInstance();
        if (!$al->getAssetGroup('leaflet')) {
            // load leaflet js/css
            $leafletVersion = '1.9.3';
            $al->register(
                'javascript',
                'leaflet',
                'js/leaflet.js',
                ['position' => Asset::ASSET_POSITION_HEADER, 'version' => $leafletVersion],
                'ounziw_osm'
            );
            $al->register(
                'css',
                'leaflet',
                'css/leaflet.css',
                ['version' => $leafletVersion],
                'ounziw_osm'
            );
            $al->registerGroup('leaflet', [
                ['javascript', 'leaflet'],
                ['css', 'leaflet'],
            ]);
        }
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('leaflet');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::validate()
     */
    public function validate($args)
    {
        $check = $this->normalizeData($args);

        return is_array($check) ? null : $check;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::save()
     */
    public function save($args)
    {
        $check = $this->normalizeData($args);
        if (!is_array($check)) {
            throw (new UserMessageException((string) $check))->setMessageContainsHtml();
        }
        parent::save($check);
    }

    protected function addOrEdit()
    {
        $this->requireAsset('javascript', 'jquery');
        $this->set('editor', $this->app->make('editor'));
        $this->set('zoomMin', static::ZOOM_MIN);
        $this->set('zoomMax', static::ZOOM_MAX);
        $this->set('message', LinkAbstractor::translateFromEditMode($this->message ?? ''));
        $this->set('tileLayerConfig', $this->app->make(Repository::class)->get('ounziw_osm::leaflet.tileLayer'));
    }

    protected function setInitialPosition()
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
        $this->set('marker', true);
        $this->set('markerlatitude', $data_lat);
        $this->set('markerlongitude', $data_lng);
    }

    /**
     * @param string|mixed $data
     *
     * @return string empty string in case of errors
     */
    protected function validateValueWithUnit($data)
    {
        $data = is_string($data) ? trim($data) : '';
        if ($data === '') {
            return '';
        }
        $matches = null;
        if (!preg_match('/^(?<value>(0?\.[0-9]+|[0-9]+))\s*(?<unit>[^0-9\s]\w*)?$/', $data, $matches)) {
            return '';
        }
        $unit = strtolower($matches['unit'] ?? '');
        if ($unit === '') {
            return "{$matches['value']}px";
        }
        if (!in_array($unit, self::UNITS, true)) {
            return '';
        }

        return "{$matches['value']}{$unit}";
    }

    /**
     * Check and normalize the block data.
     *
     * @param array|mixed $data
     *
     * @return array|\Concrete\Core\Error\ErrorList\ErrorList returns an array if everything is ok, the errors otherwise
     */
    protected function normalizeData($data)
    {
        if (!is_array($data)) {
            $data = [];
        }
        $errors = $this->app->make(ErrorList::class);
        $result = [
            'width' => $this->validateValueWithUnit($data['width'] ?? ''),
            'height' => $this->validateValueWithUnit($data['height'] ?? ''),
            'marker' => empty($data['marker']) ? 0 : 1,
            'zindex' => empty($data['zindex']) ? 0 : 1,
            'hideZoomControls' => empty($data['hideZoomControls']) ? 0 : 1,
            'showMyPosition' => empty($data['showMyPosition']) ? 0 : 1,
            'expert' => empty($data['expert']) ? 0 : 1,
            'message' => is_string($data['message'] ?? null) ? LinkAbstractor::translateTo(trim($data['message'])) : '',
            'zindexval' => is_numeric($data['zindexval'] ?? null) ? (int) $data['zindexval'] : null,
            'zoom' => is_numeric($data['zoom'] ?? null) ? (int) $data['zoom'] : null,
            'latitude' => is_numeric($data['latitude'] ?? null) ? (float) $data['latitude'] : null,
            'longitude' => is_numeric($data['longitude'] ?? null) ? (float) $data['longitude'] : null,
            'markerlatitude' => is_numeric($data['markerlatitude'] ?? null) ? (float) $data['markerlatitude'] : null,
            'markerlongitude' => is_numeric($data['markerlongitude'] ?? null) ? (float) $data['markerlongitude'] : null,
        ];
        if ($result['width'] === '') {
            $errors->add(t('The width width must be either a number, or a number followed by: %s', Misc::joinOr(self::UNITS)));
        }
        if ($result['height'] === '') {
            $errors->add(t('The height must be either a number, or a number followed by: %s', Misc::joinOr(self::UNITS)));
        }
        if ($result['zindex'] === 1) {
            if ($result['zindexval'] === null) {
                $errors->add(t('The z-index value must be an integer number.'));
            }
        }
        if ($result['latitude'] === null) {
            $errors->add(t('The latitude must be a number.'));
        }
        if ($result['longitude'] === null) {
            $errors->add(t('The longitude must be a number.'));
        }
        if ($result['zoom'] === null || $result['zoom'] < static::ZOOM_MIN || $result['zoom'] > static::ZOOM_MAX) {
            $errors->add(t('The zoom level must be a number between %1$s and %2$s.', self::ZOOM_MIN, self::ZOOM_MAX));
        }
        if ($result['marker'] === 1) {
            if ($result['markerlatitude'] === null) {
                $errors->add(t('The marker latitude must be a number.'));
            }
            if ($result['markerlongitude'] === null) {
                $errors->add(t('The marker longitude must be a number.'));
            }
        }

        return $errors->has() ? $errors : $result;
    }
}
