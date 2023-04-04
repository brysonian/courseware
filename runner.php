<?php

# Global config for this install can be set by creating a .env file

if (file_exists('.env')) {
  $env = parse_ini_file('.env');
  foreach ($env as $k => $v) {
    define($k, $v);
  }
}

##########################################
define('PUBLIC_ROOT', __DIR__);

# open htaccess and parse out base path
if (!defined('PUBLIC_URL')) {
  $base = '';
  try {
    $htaccess = file_get_contents(realpath(PUBLIC_ROOT . '/.htaccess'));
    $m = [];
    preg_match_all('/RewriteBase(.*)/', $htaccess, $m);
    $base = trim($m[1][0]);
    $base = $base === '/' ? '' : $base;
  } catch(Exception $e) {}

  define('PUBLIC_URL', $base);
}

if (defined('USE_PHAR')) {
  require USE_PHAR;
} else {
  require './app/app.php';
}