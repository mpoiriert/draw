Create a Data Tester
====================
From a PHPUnit test case you simply create a new **Draw\\DataTester\\Tester** instance:

.. literalinclude:: ../../test/ExampleTest.php
   :name: Example-Simple-Test
   :caption: Example: Simple Test
   :start-after: //example-start: TestClass
   :end-before: //example-end: TestClass
   :prepend: <?php
   :append: }

The **Tester** use a fluent interface by returning himself on all of the **assert\*** methods and most of his methods.
This allow to easily make multiple test on the same *data*.

If you don't need a reference to the tester you can be even more concise:

.. literalinclude:: ../../test/ExampleTest.php
   :name: Example-New-Concise
   :caption: Example: New Concise
   :emphasize-lines: 2
   :start-after: //example-start: ConciseNew
   :end-before: //example-end: ConciseNew
   :dedent: 8
   :prepend: <?php
