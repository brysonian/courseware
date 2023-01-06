<?php

##########################################
#             CONFIGURATION              #
##########################################

define('DEBUG', 1);
define('DEV', 1);
define('CONTENT_DIR', '403');



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

if (defined('DEBUG')) {
  require './app/app.php';
} else {
  require './courseware.phar';
}