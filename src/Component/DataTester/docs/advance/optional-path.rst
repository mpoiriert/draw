Optional Path
=============

Considering you have a complex structure with optional **path** into it. You can use the method **ifPathIsReadable**
to make some test **optional**:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-if-path-is-readable
   :caption: Example: If Path Is Readable
   :emphasize-lines: 3
   :start-after: //example-start: IfPathIsReadable
   :end-before: //example-end: IfPathIsReadable
   :dedent: 8
   :prepend: <?php

This obviously make more sense with a combination of **each**. In this more complex example lets say you receive
a list of users object that don't have the same properties available:

.. literalinclude:: ../../test/ExampleTest.php
   :name: example-if-path-is-readable-and-each
   :caption: Example If Path Is Readable And Each
   :emphasize-lines: 6,29
   :start-after: //example-start: IfPathIsReadableAndEach
   :end-before: //example-end: IfPathIsReadableAndEach
   :dedent: 8
   :prepend: <?php
