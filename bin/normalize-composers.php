<?php

require_once __DIR__.'/../vendor/autoload.php';

$finder = (new \Symfony\Component\Finder\Finder())
    ->in(realpath(__DIR__.'/../packages'))
    ->depth(1)
    ->filter(static function (SplFileInfo $fileInfo) {
        return 'composer.json' === $fileInfo->getFilename();
    });

foreach ($finder as $file) {
    (new \Symfony\Component\Process\Process(['php', 'composer-normalize', (string) $file], realpath(__DIR__.'/..')))
        ->mustRun();
}
