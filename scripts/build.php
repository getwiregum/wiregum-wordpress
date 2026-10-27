<?php
/** Build the plugin ZIP without WordPress, Node.js or Composer. */
if (PHP_SAPI !== 'cli') { exit; }
if (!class_exists('ZipArchive')) { fwrite(STDERR, "Enable PHP's Zip extension to build the plugin.\n"); exit(1); }
$root = dirname(__DIR__);
$output = $root . '/dist';
if (!is_dir($output) && !mkdir($output, 0755, true)) { throw new RuntimeException('Cannot create dist directory.'); }
$zip = new ZipArchive();
$target = $output . '/wiregum-wordpress.zip';
if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { throw new RuntimeException('Cannot open output ZIP.'); }
try {
    foreach (array('wiregum-wordpress.php', 'assets', 'blocks', 'README.md', 'readme.txt', 'LICENSE') as $entry) {
        $path = $root . '/' . $entry;
        if (!file_exists($path)) { throw new RuntimeException('Missing package entry: ' . $entry); }
        $files = is_dir($path) ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) : array(new SplFileInfo($path));
        foreach ($files as $file) {
            if (!$file->isFile() || str_starts_with($file->getFilename(), '.')) { continue; }
            if ($file->isLink()) { throw new RuntimeException('Symlinks are not allowed in the plugin package.'); }
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($root) + 1));
            if (!$zip->addFile($file->getPathname(), 'wiregum-wordpress/' . $relative)) { throw new RuntimeException('Cannot package ' . $relative); }
        }
    }
} finally { $zip->close(); }
echo 'Built ' . $target . PHP_EOL;
