<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;

function parse_markdown($file, $isfile = true) {
	if ($isfile === false) {
		$md = $file;
	} else {
		# include to run php
		ob_start();
		include($file);
		$md = ob_get_clean();
	}

	# replace tokens
	$md = str_replace(array('${CONTENT_URL}', '${PUBLIC_URL}'), array(options('content_url'), options('public_url')), $md);

	$matches = array();
	$week_and_day = '|^\[(\d+)\.([MTWRFSU\d])\]|';
	$days_of_the_week = get_days_of_the_week();

	$public_url_link = '|^\/(.*)|';
	$local_relative_link = '|^\.(.*)|';

	$matched = preg_match_all('|\{([^}]*)\}|', $md, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	if ($matched !== false && $matched > 0) {
		$matches = array_reverse($matches);
		foreach ($matches as $key => $value) {
			$code_length = strlen($value[0][0]);
			$found = $value[1][0];
			$position = $value[1][1] - 1;

			# parse for date shortcode
			$capture = array();
			if (preg_match($week_and_day, $found, $capture) === 1) {
				$date = date_for_week_and_day($capture[1], $days_of_the_week[$capture[2]]);
				$md = substr_replace($md, $date->format('l F jS'), $position, $code_length);
			}

			# parse for public link shortcode
			$capture = array();
			if (preg_match($public_url_link, $found, $capture) === 1) {
				$md = substr_replace($md, public_url($capture[0]), $position, $code_length);
			}

			# parse for local relative link shortcode
			$capture = array();
			if (preg_match($local_relative_link, $found, $capture) === 1) {
				$no_dot = substr($capture[0], 1);

				if ($isfile) {
					$file_path = pathinfo(str_replace(options('doc_root'), '', str_replace('//', '/', $file)), PATHINFO_DIRNAME);
					$file_path .= $no_dot;
					$md = substr_replace($md, public_url($file_path), $position, $code_length);
				} else {
					$md = substr_replace($md, content_url($no_dot), $position, $code_length);
				}
			}
		}
	}

	# parse as md
	$environment = new Environment([
    'html_input' => 'allow',
    'allow_unsafe_links' => true,
	]);
	$environment->addExtension(new CommonMarkCoreExtension());
	$environment->addExtension(new AttributesExtension());
	$environment->addExtension(new FrontMatterExtension());
	$converter = new MarkdownConverter($environment);

	$result = $converter->convert($md);

	$meta = [
		'title' => '',
		'name' => ''
	];
	if ($result instanceof RenderedContentWithFrontMatter) {
		$meta = $result->getFrontMatter();
	}
	return [
		'content' => $result->getContent(),
		'meta' => $meta
	];

}
