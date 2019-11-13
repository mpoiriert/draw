Path
====
For more complex data (array, object) you can use the **path** method to test something deeper in the data itself:

.. literalinclude:: ../../test/ExampleTest.php
   :name: Example-Path
   :caption: Example: Path
   :emphasize-lines: 2
   :start-after: //example-start: TestPath
   :end-before: //example-end: TestPath
   :dedent: 8
   :prepend: <?php

By Using the **path** method you are making a assertion that the *path* is accessible. It also return a new **Tester**
instance with the *data* of the *path* to be tested.

.. literalinclude:: ../../test/ExampleTest.php
   :name: Example-Path-Callable
   :caption: Example: Path Callable
   :emphasize-lines: 3
   :start-after: //example-start: TestPath
   :end-before: //example-end: TestPath
   :dedent: 8
   :prepend: <?php

The library use behind it is the symfony/property-access, make sure you read the doc https://symfony.com/doc/current/components/property_access.html
