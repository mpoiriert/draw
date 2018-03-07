Transform
=========
If you need to **transform** the *data* during the test you can call the **transform** method with a **callable** as
the first argument for the transformation. It will return a new **Tester** with the transformed data to test.

Let's you have a **json** string as *data*, that you want to test the content, it will look like this:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-transform
   :caption: Example: Transform
   :emphasize-lines: 2
   :start-after: //example-start: Transform
   :end-before: //example-end: Transform
   :dedent: 8
   :prepend: <?php

Ideally you should **test** your *data* before transforming it:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-assert-before-transform
   :caption: Example: Assert Before Transform
   :emphasize-lines: 2-3
   :start-after: //example-start: AssertTransform
   :end-before: //example-end: AssertTransform
   :dedent: 8
   :prepend: <?php

If you would like to transform the data but not with the default values of callable you can simply
create a custom callable with the appropriate option. Let say you want **json_decode** with a associative
array:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-assert-before-transform-custom
   :caption: Example: Assert Before Transform-custom
   :emphasize-lines: 4-6
   :start-after: //example-start: AssertTransformCustom
   :end-before: //example-end: AssertTransformCustom
   :dedent: 8
   :prepend: <?php

Take a note that since it's a associative array the path must be change from **key** to **[key]**.