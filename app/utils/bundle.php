<?php

$src = __DIR__ . "/..";
$out = __DIR__ . "/../../courseware.phar";

class TestsOnlyFilter extends RecursiveFilterIterator {
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


$directory = new \RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS);
$filter   = new TestsOnlyFilter($directory);
$iterator = new \RecursiveIteratorIterator($filter);

// foreach($iterator as $key => $value)
// {
//     echo $value . "\n";
// }

$phar = new Phar($out,  0, "courseware.phar");
$phar->buildFromIterator($iterator, $src);
$phar->setStub($phar->createDefaultStub('app.php', 'app.php'));
