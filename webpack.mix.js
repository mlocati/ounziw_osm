const mix = require('laravel-mix');

mix
    .disableNotifications()
    .options({
        processCssUrls: false
    })
    .setPublicPath('.')
    .webpackConfig({
        cache: false,
        resolve: {
            symlinks: false
        },
    })
;

if (mix.inProduction()) {
    mix
        .js('node_modules/leaflet/dist/leaflet.js', 'js/leaflet.js')
        .css('node_modules/leaflet/dist/leaflet.css', 'css/leaflet.css')
    ;
} else {
    mix
        .copy('node_modules/leaflet/dist/leaflet-src.js', 'js/leaflet.js')
        .copy('node_modules/leaflet/dist/leaflet.css', 'css/leaflet.css')
    ;
}
mix
    .copyDirectory('node_modules/leaflet/dist/images', 'css/images')
;
