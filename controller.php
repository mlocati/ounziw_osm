<?php

namespace Concrete\Package\OunziwOsm;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $pkgHandle = 'ounziw_osm';

    protected $appVersionRequired = '9.0';

    protected $pkgVersion = '2.1';

    public function getPackageDescription()
    {
        return t('Displays a map using OpenStreetMap and Leaflet.');
    }

    public function getPackageName()
    {
        return t('Free Map');
    }

    public function install()
    {
        $pkg = parent::install();
        BlockType::installBlockType('ounziw_osm_map', $pkg);
    }
}
