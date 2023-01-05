<?php

define('PUBLIC_ROOT', __DIR__);
define('DEBUG', 1);

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

require './app/app.php';
// require './courseware.phar';