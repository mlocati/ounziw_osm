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


## Publish a new version

In order to publish a new version (let's assume `1.2.3` for example):

1. Set the value of the `$pkgVersion` property of the package controller to `1.2.3` 
2. Commit the changes to GIT
3. Create a GIT tag:
   ```sh
   git tag 1.2.3
   ```
4. Push the changes to GitHub (including the tags):
   ```sh
   git push --tags
   ```
5. Create the ZIP archive to be uploaded to the marketplace by running the `create-marketplace-zip` script in the `bin` directory
6. Upload the ZIP archive to the [ConcreteCMS marketplace](https://marketplace.concretecms.com/marketplace/addons/free-map) 
