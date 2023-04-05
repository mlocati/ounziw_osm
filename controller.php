<?php

namespace Concrete\Package\OunziwOsm;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Database\Connection\Connection;
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

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $cn = $this->app->make(Connection::class);
        foreach (['width', 'height'] as $field) {
            foreach ($cn->fetchFirstColumn("SELECT DISTINCT {$field} FROM btOunziwosmmap") as $originalValue) {
                $newValue = (string) $originalValue;
                if ($newValue === '') {
                    $newValue = $field === 'width' ? '300px' : '200px';
                } elseif (preg_match('/^\d+$/', $newValue)) {
                    $newValue .= 'px';
                }
                if ($newValue !== $originalValue) {
                    $cn->update('btOunziwosmmap', [$field => $newValue], [$field => $originalValue]);
                }
            }
        }
    }
}
