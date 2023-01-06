<?php

require 'vendor/autoload.php';
require 'common/helpers.php';
require 'common/markdown.php';
require 'common/Enkoder.php';

\Vicious\Vicious::Free();
\Vicious\Vicious::Autorun();

date_default_timezone_set('America/Los_Angeles');

if (defined('DEBUG') || isset($_GET['debug'])) {
	set('environment', \Vicious\Config::DEVELOPMENT);
} else {
	set('environment', \Vicious\Config::PRODUCTION);
}

configure(function ($settings) {
	$isphar = current(explode('://', __DIR__)) === 'phar';
	$content_dir = defined('CONTENT_DIR') ? CONTENT_DIR : 'content';

	$settings->root 						= PUBLIC_URL;
	$settings->content 					= realpath(PUBLIC_ROOT . '/' . $content_dir);
	$settings->content_url 			= $settings->root . '/' . $content_dir;
	$settings->projects 				= realpath(PUBLIC_ROOT . '/projects');
	$settings->templates				= $isphar ? (__DIR__ . '/views') : realpath(__DIR__ . '/views');
	$settings->assets 					= $isphar ? (__DIR__ . '/dist') : realpath(__DIR__ . '/dist');
	$settings->doc_root					= $_SERVER['DOCUMENT_ROOT'];

	$config = file_get_contents(content('config.json'));
	$config = json_decode($config);

	foreach ($config as $key => $value) {
		$settings->$key = $value;
	}

	$config->has_projects = $config->has_projects ?? true;
	$config->has_schedule = $config->has_schedule ?? true;

	if ($config->has_projects !== false) {
		$settings->has_projects = file_exists(content('projects'));
	}

	if ($config->has_schedule !== false) {
		$settings->has_schedule = file_exists(content('schedule.md'));
	}
});

session_start();

require 'routes.php';
