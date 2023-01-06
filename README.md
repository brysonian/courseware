PHP and Markdown based website builder for DMA classes

`runner.php` has a few PHP-level config options, if you comment out `DEBUG` it will not display errors. Setting `DEV` will load the code from the app dir rather than the courseware.phar.

## BUILD STYLES
Styles are written in sass. You can compile them by cding into `app` via parcel using `npm run build` You can also watch for changes (useful for development) using `npm start`

## BUNDLE
The app dir can be bundled into a phar archive using `npm run bundle` in the `app` dir.

