<?php

use League\CommonMark\CommonMarkConverter;

function content($path, $subdir=false) {
	if (empty($path)) return false;
	if (strpos($path, 'http://') === 0) return false;
	$subdir = ($subdir === false) ? options('content') : $subdir;
	$path = str_replace('..', '', $path);
	$path = str_replace('//', '/', $path);
	return realpath($subdir . DIRECTORY_SEPARATOR . $path);
}

function content_url() {
	$args = func_get_args();
	if (func_num_args() == 1) {
		if (empty($args[0])) return '';
		if (strpos($args[0], 'http://') === 0) return $args[0];
	}
	$path = '/' . options('content_url') . '/' . join('/', $args);
	return str_replace(options('doc_root'), '', str_replace('//', '/', $path));
}

function projects($path) {
	return content($path, options('projects'));
}

function project_url() {
	$args = func_get_args();
	array_unshift($args, 'projects');
	$path = call_user_func_array('content_url', $args);
	$path = PUBLIC_URL . str_replace(options('content_url'), '', $path);
	return $path;
}

function urlsafe($name) {
	$out = trim(preg_replace('/[^a-zA-Z0-9\-]/', ' ', strtolower($name)));
	$out = preg_replace('/ +/', '-', $out);
	return $out;
}

function striplinks($text) {
	return preg_replace("/<a href=[^>]*>([^<]*)<\/a>/i", '<span class="delink">$1</span>', $text);
}

function url_exists($url) {
	$hdrs = @get_headers($url);
	return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
}

function image($path, $attrs=array(), $size=true) {
	$fpath = content($path);
	if ($size && (!isset($attrs['width']) || !isset($attrs['height'])) && (strpos($path, 'http://') === 0) || file_exists($fpath)) {
		$s=@getimagesize($fpath);
		if (is_array($s)) {
			$attrs['width'] = $s[0];
			$attrs['height'] = $s[1];
		}
	}
	$tag = "<img src='" . content_url($path) . "' ";
	foreach ($attrs as $key => $value) $tag .= $key.'="'.$value.'" ';
	$tag .='>';
	return $tag;
}

function link_url() {
	return str_replace('//', '/', '/' . PUBLIC_URL . '/' . join('/', func_get_args()));
}

function css() {
	return str_replace('//', '/', '/' . PUBLIC_URL . '/dist/' . join('/', func_get_args()));
}

// ========
// - UTILS
// ========
function get_files($dir, $ext=false, $ignore_prefix=false) {
	$files = array();
	$dir = new DirectoryIterator($dir);
	foreach ($dir as $fileinfo) {
		$fn = $fileinfo->getFilename();
    if (!$fileinfo->isDot() && $fn[0] != '.') {
    	if ($ext !== false) {
    		$e = pathinfo($fileinfo->getPathname(), PATHINFO_EXTENSION);
    		if ($e !== $ext) continue;
    	}
    	if ($ignore_prefix !== false && $fn[0] === $ignore_prefix) continue;
			$files[] = $fileinfo->getPathname();
		}
	}
	sort($files);
	return $files;
}

function get_days_of_the_week() {
	$days_of_the_week = array(
		'U'			 		=> 0,
		'M'			 		=> 1,
		'T' 				=> 2,
		'W' 				=> 3,
		'R' 				=> 4,
		'F' 				=> 5,
		'S'					=> 6
	);
	foreach(options('days_of_week') as $k => $v) {
		$days_of_the_week[$k+1] = $days_of_the_week[$v];
	}
	return $days_of_the_week;
}

function load_schedule($path) {
	$data = file($path);
	$days_of_the_week = get_days_of_the_week();

	$pattern = '|^\[(\d+)\.([MTWRFSU\d])\]|';
	$output 		= [
		'holiday' => []
	];
	$last_week 	= -1;
	$last_dow 	= -1;

	foreach($data as $k => $line) {
		$m = array();
		if (preg_match($pattern, $line, $m)) {
			$last_week	= $m[1];
			$last_dow		= $days_of_the_week[$m[2]];
			if (!array_key_exists($last_week, $output)) $output[$last_week] = array();
			if (!array_key_exists($last_dow, $output[$last_week])) $output[$last_week][$last_dow] = '';
		} else {
			$output[$last_week][$last_dow] .= $line;
		}
	}
	foreach($output as $week => $days) {
		foreach($days as $day => $md) {
			// die(var_export($md));
			if (strpos($md, '[NO CLASS]') !== false) {
				$md = str_replace('[NO CLASS]', '', $md);
				$output['holiday']["$week.$day"] = true;
			}
			$content = parse_markdown($md, false);
			$output[$week][$day] = $content['content'];
		}
	}
	return $output;
}

function date_for_week_and_day($week, $day) {
	static $class_dates;
	if (!$class_dates) $class_dates = class_dates();

	if (!is_numeric($day)) {
		$days_of_the_week = get_days_of_the_week();
		if (isset($days_of_the_week[$day]))
			$day = $days_of_the_week[$day];
	}

	if (isset($class_dates[$week]) && isset($class_dates[$week][$day])) {
		return $class_dates[$week][$day];
	}

	die("Invalid week: $week and day: $day [$week.$day]");
}

function class_dates($start=false, $end=false, $week0=false) {
	$start 				= $start === false 				? options('term_start_date') 	: $start;
	$end	 				= $end === false 					? options('term_end_date') 		: $end;
	$week0	 			= $week0 === false 				? options('week_zero') 		: $week0;
	$week_num 		= $week0 ? 0 : 1;

	$out = array();
	$today 	= new DateTime($start);
	$end 		= new DateTime($end);
	$one_day  = new DateInterval('P1D');
	while($today <= $end) {
		$day_num = $today->format('w');
		if ($day_num == 0) {
			$week_num++;
			$out[$week_num] = array();
		}
		$out[$week_num][$day_num] = clone $today;
		$today->add($one_day);
	}
	return $out;
}

function link_content_dir($dir) {
	$files = get_files(content($dir));
	foreach ($files as $file) {
		$base = basename($file);
		echo "- [" . str_replace('_', '\_', $base) . "](" . options('doc_root') . "/$dir/$base)\n";
	}
}

function load_links_file($file) {
	$out = array();
  if (!empty($file) && file_exists($file)) {
  	$file = file($file);
  	$week = array();
    foreach ($file as $num => $line) {
			$line = preg_replace('/#.*/', '', $line);
			if (preg_match("/Week (\d+)/i", $line, $matches) === 1) {
				if (!empty($week)) {
					$out[] = $week;
				}
				$week = array(
					'name' => $matches[0],
					'links' => array(),
					'note' => ''
				);
			} else if (preg_match("/([1-5])\.(.*)/i", $line, $matches) === 1) {
				$week['links'][] = trim($matches[2]);

			} else {
				if (!empty($week['note'])) {
					$week['note'] .= $line;
				} else if (trim($line) !== '') {
					$week['note'] .= trim($line);
				}
			}
    }
    $out[] = $week;
	}
	foreach ($out as $key => $value) {
		$out[$key]['note'] = '<p>' . str_replace("\n", '</p><p>', trim($value['note'])) . '</p>';
	}
	return $out;
}

function get_projects() {
	$project_files = get_files(content('projects'), 'md', '_');
	$projects = array();
	foreach ($project_files as $key => $file) {
		//$name = explode('-', pathinfo($file, PATHINFO_FILENAME));
		$fname = pathinfo($file, PATHINFO_FILENAME);
		$dash = strpos($fname, '-');
		$name = ucwords(substr($fname, $dash+1));
		$number = substr($fname, 0, $dash);
		// $name[1] = ucwords(str_replace('-', ' ', $name[1]));
		$projects[] = array(
			'number' => $number,
			'name' => $name,
			'file' => $file
		);
	}
	return $projects;
}

function get_pages() {
	$page_files = get_files(content('pages'), 'md', '_');
	$pages = [];
	foreach ($page_files as $key => $file) {
		$fname = pathinfo($file, PATHINFO_FILENAME);
		$dash = strpos($fname, '-');
		$name = ucwords(substr($fname, $dash+1));
		$number = substr($fname, 0, $dash);
		// $name[1] = ucwords(str_replace('-', ' ', $name[1]));
		$content = parse_markdown($file);
		$pages[] = array(
			'content' => $content['content'],
			'name' => $content['meta']['name'],
			'show_in_nav' => $content['meta']['show_in_nav'] ?? true,
			// 'file' => $file,
			'slug' => $fname
		);
	}
	return $pages;
}

