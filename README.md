PHP and Markdown based website builder for DMA classes

`runner.php` has a few PHP-level config options, all of which are optional.
* defining `DEBUG` will enable error display (this may be affected by local php config)
* defining `USE_PHAR` will load the code from the app dir rather than the courseware.phar
* defining `CONTENT_DIR` will use the specified directory to load site content. The default is `content`

## Building sass
Styles are written in sass. You can compile them by cding into `app` via parcel using `npm run build` You can also watch for changes (useful for development) using `npm start`

## Bundling
The app dir can be bundled into a phar archive using `npm run bundle` in the `app` dir.

