<?php

namespace Draw\Component\Tester\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAssertsDocumentationPageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('draw:tester:generate-asserts-documentation-page')
            ->setDescription('Generate the assert documentation base on the methods available')
            ->addArgument(
                'assertMethodsFilePath',
                InputArgument::OPTIONAL,
                'The file path where the methods configuration are.',
                __DIR__.'/../Resources/config/assert_methods.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('assertMethodsFilePath');
        $methods = json_decode(file_get_contents($filePath), true);

        $file = 'Asserts
=======

The list of asserts available are a sub-set of the **PHPUnit Assert** available.

Some of the methods have been remove since they are replicable trough a combination of **path** and another assert.
Other are not available either for compatibility issues. If you think that some must be added just open a issue
in the git repository.

For a more exhaustive documentation please refer to `PHPUnit Documentation <https://phpunit.de/manual/current/en/appendixes.assertions.html>`_.
Do not forgot that all the asserts are not available and that the **$this->getData()** replace the data you want to test
that is normally pass trough the **PHPUnit Assert** methods.

';

        foreach ($methods as $methodName => $information) {
            if ($information['ignore']) {
                continue;
            }

            $file .= "
$methodName
".str_pad('', strlen($methodName), '^').'

.. literalinclude:: ../AssertTrait.php
   :name: assert-'.str_replace('assert', '', $methodName).'
   :start-after: //example-start: '.$methodName.'
   :end-before: //example-end: '.$methodName.'
   :dedent: 4
   :prepend: <?php
';
        }

        file_put_contents(__DIR__.'/../docs/asserts.rst', $file);

        return 0;
    }
}
