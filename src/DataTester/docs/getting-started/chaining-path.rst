Chaining Path
=============
Since the **path** method return a new **Tester** you must keep a reference on the original **Tester**
if you want to test other **path**.

.. literalinclude:: ../../test/ExampleTest.php
   :name: Example-Chain-Path
   :caption: Example: Chain Path
   :emphasize-lines: 1,2,3
   :start-after: //example-start: ChainTestPath
   :end-before: //example-end: ChainTestPath
   :dedent: 8
   :prepend: <?php
