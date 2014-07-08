<?php
die('This script is suposed to generate a binary hook.phar file.');

ini_set('phar.readonly', 0);

function glob_recursive($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	}
	return $files;
}

$phar = new Phar('hook.phar');
// foreach(glob_recursive('src/*') as $src) {
// 	$phar->addFile($src);
// }
// foreach(glob_recursive('vendor/*') as $src) {
// 	$phar->addFile($src);
// }
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->addFile('bin/hook');
$phar->setStub( $phar->createDefaultStub('bin/hook') );

// $phar->convertToExecutable(Phar::PHAR);
// $phar->setStub( $phar->createDefaultStub('bootstrap.php') );
