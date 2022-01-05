<?php
declare(strict_types=1);
/**
 * Build script
 */
const FILE_NAME = "BuildFFA";
const COMPRESS_FILES = true;
const COMPRESSION = Phar::GZ;
$startTime = microtime(true);

// Input & Output directory...
$from = getcwd() . DIRECTORY_SEPARATOR;
$to = getcwd() . DIRECTORY_SEPARATOR . "out" . DIRECTORY_SEPARATOR . FILE_NAME . DIRECTORY_SEPARATOR;
@mkdir($to, 0777, true);

// Clean output directory...
cleanDirectory($to);

// Copying new files...
copyDirectory($from . "src", $to . "src");
copyDirectory($from . "resources", $to . "resources");
file_put_contents($to . "LICENSE", file_get_contents($from . "LICENSE"));
$description = yaml_parse_file($from . "plugin.yml");
yaml_emit_file($to . "plugin.yml", $description);

// Defining output path...
$outputPath = $from . "out" . DIRECTORY_SEPARATOR . FILE_NAME . "_{$description["version"]}";
@unlink($outputPath . ".phar");

// Generate phar
var_dump($outputPath . ".phar");
$phar = new Phar($outputPath . ".phar");
$phar->buildFromDirectory($to);
if (COMPRESS_FILES) {
	$phar->compressFiles(COMPRESSION);
}
printf("Built in %s seconds! Output path: %s\n", round(microtime(true) - $startTime, 3), $outputPath);

// Defining output path...
$outputPath = "C:/Users/kfeig/Desktop/pmmp4/plugins" . DIRECTORY_SEPARATOR . FILE_NAME;
@unlink($outputPath . ".phar");

// Generate phar
$phar = new Phar($outputPath . ".phar");
$phar->buildFromDirectory($to);
if (COMPRESS_FILES) {
	$phar->compressFiles(COMPRESSION);
}
printf("Built in %s seconds! Output path: %s\n", round(microtime(true) - $startTime, 3), $outputPath);


cleanDirectory($to);
rmdir($to);

function copyDirectory(string $from, string $to): void{
	mkdir($to, 0777, true);
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	/** @var SplFileInfo $fileInfo */
	foreach ($files as $fileInfo) {
		$target = str_replace($from, $to, $fileInfo->getPathname());
		if ($fileInfo->isDir()) {
			mkdir($target, 0777, true);
		} else {
			$contents = file_get_contents($fileInfo->getPathname());
			file_put_contents($target, $contents);
		}
	}
}

function cleanDirectory(string $directory): void{
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	/** @var SplFileInfo $fileInfo */
	foreach ($files as $fileInfo) {
		if ($fileInfo->isDir()) {
			rmdir($fileInfo->getPathname());
		} else {
			unlink($fileInfo->getPathname());
		}
	}
}