<?php

##########################################
#             CONFIGURATION              #
##########################################

define('USE_PHAR', 1);

# use the specified directory to load site content, the default is to use the dir named `content`
define('CONTENT_DIR', '403');

# enable error display (this may be affected by local php config)
# define('DEBUG', 1);



##########################################
define('PUBLIC_ROOT', __DIR__);

# open htaccess and parse out base path
$base = '';
try {
  $htaccess = file_get_contents(realpath(PUBLIC_ROOT . '/.htaccess'));
  $m = [];
  preg_match_all('/RewriteBase(.*)/', $htaccess, $m);
  $base = trim($m[1][0]);
  $base = $base === '/' ? '' : $base;
} catch(Exception $e) {}

define('PUBLIC_URL', $base);

if (defined('USE_PHAR')) {
  require './courseware.phar';
} else {
  require './app/app.php';
}