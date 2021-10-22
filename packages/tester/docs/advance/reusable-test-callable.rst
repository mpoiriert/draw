Reusable Test Callable
======================

Some time you want to execute the same set of test on different source of data.
You have a method that return you a user and one that return you a list of users.
You can simply create a class that as all the test inside of it.

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-class-callable
   :caption: Example: Class Callable
   :start-after: //example-start: UserDataTester
   :end-before: //example-end: UserDataTester
   :prepend: <?php\nnamespace Your\Project\Name;\nuse Draw\DataTester\Tester;

And now you can use it to test the *data* of one user:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-test-with-class-callable
   :caption: Example: Test With Class Callable
   :emphasize-lines: 8
   :start-after: //example-start: TestWithClassCallable
   :end-before: //example-end: TestWithClassCallable
   :dedent: 8
   :prepend: <?php

Or with **each** in case of a list of users:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-each-with-class-callable
   :caption: Example: Each With Class Callable
   :emphasize-lines: 14
   :start-after: //example-start: EachWithClassCallableEach
   :end-before: //example-end: EachWithClassCallableEach
   :dedent: 8
   :prepend: <?php