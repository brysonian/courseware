<?php

use Cocur\Slugify\Slugify;

not_found(function ($e) {
	return phtml('404', false);
});

error(function ($e) {
	return phtml('404', false);
});


before(function() {
	phtml()->body_class = array_filter(explode('/', request('uri')));

	$dow = options('days_of_week');
	foreach($dow as $k => $day) {
		switch($day) {
			case 'M' : $dow[$k] = 'Monday'; break;
			case 'T' : $dow[$k] = 'Tuesday'; break;
			case 'W' : $dow[$k] = 'Wednesday'; break;
			case 'R' : $dow[$k] = 'Thursday'; break;
			case 'F' : $dow[$k] = 'Friday'; break;
		}
	}
	phtml()->days = join(' &amp; ', $dow);

	phtml()->user_style = false;
	if (file_exists(content('style.css'))) {
		phtml()->user_style = content_url('style.css');
	}

	$pages = get_pages();
	phtml()->pages = $pages;
});

// ===========================================================
// - ROUTES
// ===========================================================
get(PUBLIC_URL . '/', function() {
	$parsed = parse_markdown(content('syllabus.md'));
	phtml()->syllabus = $parsed['content'];
	return phtml('index');
});

get(PUBLIC_URL . '/tutorials/:page', function() {
	phtml()->section = 'Tutorials';
	phtml()->content = parse_markdown(content('tutorials/' . params('page') . '.md'));
	return phtml('page');
});

get(PUBLIC_URL . '/projects', function() {
	$slugify = new Slugify();

	phtml()->section = 'Projects';
	$projects = array();
	$project_files = get_projects();
	foreach ($project_files as $key => $project) {
		$parsed = parse_markdown($project['file']);
		$due = array_key_exists('due', $parsed['meta']) ? $parsed['meta']['due'] : false;
		$projects[] = [
			'content' => $parsed['content'],
			'due' => $due,
			'number' => $project['number'],
			'title' => $parsed['meta']['title'],
			'slug' => $slugify->slugify($parsed['meta']['title']),
		];
	}
	phtml()->projects = $projects;
	return phtml('projects');
});

get(PUBLIC_URL . '/schedule', function() {
	phtml()->section = 'Schedule';
	phtml()->schedule = load_schedule(content('schedule.md'));
	return phtml('schedule');
});

get(PUBLIC_URL . '/robots.txt', function() {
	return "User-agent: *\nDisallow: /";
});

get(PUBLIC_URL . '/content/*', function($splat) {
	$file = content($splat);
	if (!file_exists($file)) return '';
	$type = mime_content_type($file);
	header("Content-Type: $type");
	readfile($file);
});

get(PUBLIC_URL . '/dist/*', function($splat) {
	$type = '';
	switch(pathinfo($splat, PATHINFO_EXTENSION)) {
		case 'css':	$type = 'text/css'; break;
		case 'js':  $type = 'application/javascript'; break;
	}
	header("Content-Type: $type");
	readfile(options('assets') . '/' . $splat);
});

get(PUBLIC_URL . '/*', function() {
	// die(var_export(params('splat')));
	$s = params('splat');
	foreach(phtml()->pages as $page) {
		if ($page['slug'] === $s) {
			phtml()->section = $page['name'];
			phtml()->content = $page['content'];
			return phtml('page');
		}
	}
	return phtml('404', false);
});
