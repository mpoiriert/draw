<?php

require_once __DIR__.'/../vendor/autoload.php';

$kernel = new \Draw\Bundle\ApplicationBundle\Tests\AppKernel('test', true);
$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->setAutoExit(false);

$result = $application
    ->run(
        new \Symfony\Component\Console\Input\ArrayInput([
            'command' => 'doctrine:schema:update',
            '--force' => null,
        ]),
        $output = new \Symfony\Component\Console\Output\BufferedOutput()
    );

if (0 !== $result) {
    $output->fetch();
    exit($result);
}
