<?php

$src = __DIR__ . "/..";
$out_name = 'courseware.phar';
$out_dir = __DIR__ . "/../..";

class IgnoreSomeDirsFilter extends RecursiveFilterIterator {
  public function accept() : bool {
    $ignore = [
      'parcel-cache',
      'node_modules',
      'DS_Store',
      'git'
    ];

    foreach ($ignore as $i) {
      if (strpos($this->current(), $i) !== false) {
        return false;
      }
    }
    return true;
  }
}

echo "\n> Bundling app directory into $out_name\n";
$directory = new \RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS);
$filter   = new IgnoreSomeDirsFilter($directory);
$iterator = new \RecursiveIteratorIterator($filter);

$phar = new Phar($out_dir . '/' . $out_name,  0, $out_name);
$phar->buildFromIterator($iterator, $src);
$phar->setStub($phar->createDefaultStub('app.php', 'app.php'));

echo "> Bundling complete\n";
