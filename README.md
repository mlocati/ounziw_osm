# Free Map package for Concrete CMS

This repository contains the source code of the [Free Map package of Concrete CMS](https://marketplace.concretecms.com/marketplace/addons/free-map).


## Developers Instructions

This package comes with [Leaflet](https://leafletjs.com/).

If you want to update it, or use a development (uncompressed) version of it, you need [NodeJS](https://nodejs.org) and NPM.

### Building for production

If you want to use a compressed version of leaflet, you need to

1. Install the NPM dependencies with `npm ci`
2. Execute `npm run prod`

### Building for development

If you want to use an uncompressed version of leaflet, you need to

1. Install the NPM dependencies with `npm ci`
2. Execute `npm run dev`

### Update leaflet

If you want to update leaflet to the most recent version, you need to:

1. Update the NPM dependencies with `npm update`
2. Execute `npm run prod`
