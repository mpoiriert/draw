<?php

require "../../../vendor/autoload.php";

$reflectionClass = new ReflectionClass(PHPUnit\Framework\Assert::class);
$methods = json_decode(file_get_contents(__DIR__ . '/../resources/methods.json'), true);

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
" . str_pad('', strlen($methodName), '^') . "

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-" . str_replace('assert', '', $methodName) . "
   :start-after: //example-start: " . $methodName . "
   :end-before: //example-end: " . $methodName . "
   :dedent: 4
   :prepend: <?php
";
}

file_put_contents(__DIR__ . '/../docs/asserts.rst', $file);