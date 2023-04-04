PHP and Markdown based website builder for DMA classes

To set some PHP-level config options, rename .env.example to .env
all of these are optional and the default values are described below

* `DEBUG` enables error display (this may be affected by local php config)
* `USE_PHAR` loads the app code from the specified phar file rather than the `app` directory
* `CONTENT_DIR` will use the specified directory to load site content. The default is `content`

## Building sass
Styles are written in sass. You can compile them by cding into `app` via parcel using `npm run build` You can also watch for changes (useful for development) using `npm start`

## Bundling
The app dir can be bundled into a phar archive using `npm run bundle` in the `app` dir.

