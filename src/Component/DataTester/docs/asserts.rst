Asserts
=======

The list of asserts available are a sub-set of the **PHPUnit Assert** available.

Some of the methods have been remove since they are replicable trough a combination of **path** and another assert.
Other are not available either for compatibility issues. If you think that some must be added just open a issue
in the git repository.

For a more exhaustive documentation please refer to `PHPUnit Documentation <https://phpunit.de/manual/current/en/appendixes.assertions.html>`_.
Do not forgot that all the asserts are not available and that the **$this->getData()** replace the data you want to test
that is normally pass trough the **PHPUnit Assert** methods.


assertArraySubset
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-ArraySubset
   :start-after: //example-start: assertArraySubset
   :end-before: //example-end: assertArraySubset
   :dedent: 4
   :prepend: <?php

assertContains
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Contains
   :start-after: //example-start: assertContains
   :end-before: //example-end: assertContains
   :dedent: 4
   :prepend: <?php

assertNotContains
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotContains
   :start-after: //example-start: assertNotContains
   :end-before: //example-end: assertNotContains
   :dedent: 4
   :prepend: <?php

assertContainsOnly
^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-ContainsOnly
   :start-after: //example-start: assertContainsOnly
   :end-before: //example-end: assertContainsOnly
   :dedent: 4
   :prepend: <?php

assertContainsOnlyInstancesOf
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-ContainsOnlyInstancesOf
   :start-after: //example-start: assertContainsOnlyInstancesOf
   :end-before: //example-end: assertContainsOnlyInstancesOf
   :dedent: 4
   :prepend: <?php

assertNotContainsOnly
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotContainsOnly
   :start-after: //example-start: assertNotContainsOnly
   :end-before: //example-end: assertNotContainsOnly
   :dedent: 4
   :prepend: <?php

assertCount
^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Count
   :start-after: //example-start: assertCount
   :end-before: //example-end: assertCount
   :dedent: 4
   :prepend: <?php

assertNotCount
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotCount
   :start-after: //example-start: assertNotCount
   :end-before: //example-end: assertNotCount
   :dedent: 4
   :prepend: <?php

assertEquals
^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Equals
   :start-after: //example-start: assertEquals
   :end-before: //example-end: assertEquals
   :dedent: 4
   :prepend: <?php

assertNotEquals
^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotEquals
   :start-after: //example-start: assertNotEquals
   :end-before: //example-end: assertNotEquals
   :dedent: 4
   :prepend: <?php

assertEmpty
^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Empty
   :start-after: //example-start: assertEmpty
   :end-before: //example-end: assertEmpty
   :dedent: 4
   :prepend: <?php

assertNotEmpty
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotEmpty
   :start-after: //example-start: assertNotEmpty
   :end-before: //example-end: assertNotEmpty
   :dedent: 4
   :prepend: <?php

assertGreaterThan
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-GreaterThan
   :start-after: //example-start: assertGreaterThan
   :end-before: //example-end: assertGreaterThan
   :dedent: 4
   :prepend: <?php

assertGreaterThanOrEqual
^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-GreaterThanOrEqual
   :start-after: //example-start: assertGreaterThanOrEqual
   :end-before: //example-end: assertGreaterThanOrEqual
   :dedent: 4
   :prepend: <?php

assertLessThan
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-LessThan
   :start-after: //example-start: assertLessThan
   :end-before: //example-end: assertLessThan
   :dedent: 4
   :prepend: <?php

assertLessThanOrEqual
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-LessThanOrEqual
   :start-after: //example-start: assertLessThanOrEqual
   :end-before: //example-end: assertLessThanOrEqual
   :dedent: 4
   :prepend: <?php

assertTrue
^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-True
   :start-after: //example-start: assertTrue
   :end-before: //example-end: assertTrue
   :dedent: 4
   :prepend: <?php

assertNotTrue
^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotTrue
   :start-after: //example-start: assertNotTrue
   :end-before: //example-end: assertNotTrue
   :dedent: 4
   :prepend: <?php

assertFalse
^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-False
   :start-after: //example-start: assertFalse
   :end-before: //example-end: assertFalse
   :dedent: 4
   :prepend: <?php

assertNotFalse
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotFalse
   :start-after: //example-start: assertNotFalse
   :end-before: //example-end: assertNotFalse
   :dedent: 4
   :prepend: <?php

assertNull
^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Null
   :start-after: //example-start: assertNull
   :end-before: //example-end: assertNull
   :dedent: 4
   :prepend: <?php

assertNotNull
^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotNull
   :start-after: //example-start: assertNotNull
   :end-before: //example-end: assertNotNull
   :dedent: 4
   :prepend: <?php

assertFinite
^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Finite
   :start-after: //example-start: assertFinite
   :end-before: //example-end: assertFinite
   :dedent: 4
   :prepend: <?php

assertInfinite
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Infinite
   :start-after: //example-start: assertInfinite
   :end-before: //example-end: assertInfinite
   :dedent: 4
   :prepend: <?php

assertNan
^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Nan
   :start-after: //example-start: assertNan
   :end-before: //example-end: assertNan
   :dedent: 4
   :prepend: <?php

assertSame
^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Same
   :start-after: //example-start: assertSame
   :end-before: //example-end: assertSame
   :dedent: 4
   :prepend: <?php

assertNotSame
^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotSame
   :start-after: //example-start: assertNotSame
   :end-before: //example-end: assertNotSame
   :dedent: 4
   :prepend: <?php

assertInstanceOf
^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-InstanceOf
   :start-after: //example-start: assertInstanceOf
   :end-before: //example-end: assertInstanceOf
   :dedent: 4
   :prepend: <?php

assertNotInstanceOf
^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotInstanceOf
   :start-after: //example-start: assertNotInstanceOf
   :end-before: //example-end: assertNotInstanceOf
   :dedent: 4
   :prepend: <?php

assertInternalType
^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-InternalType
   :start-after: //example-start: assertInternalType
   :end-before: //example-end: assertInternalType
   :dedent: 4
   :prepend: <?php

assertNotInternalType
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotInternalType
   :start-after: //example-start: assertNotInternalType
   :end-before: //example-end: assertNotInternalType
   :dedent: 4
   :prepend: <?php

assertRegExp
^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-RegExp
   :start-after: //example-start: assertRegExp
   :end-before: //example-end: assertRegExp
   :dedent: 4
   :prepend: <?php

assertNotRegExp
^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotRegExp
   :start-after: //example-start: assertNotRegExp
   :end-before: //example-end: assertNotRegExp
   :dedent: 4
   :prepend: <?php

assertSameSize
^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-SameSize
   :start-after: //example-start: assertSameSize
   :end-before: //example-end: assertSameSize
   :dedent: 4
   :prepend: <?php

assertNotSameSize
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-NotSameSize
   :start-after: //example-start: assertNotSameSize
   :end-before: //example-end: assertNotSameSize
   :dedent: 4
   :prepend: <?php

assertStringMatchesFormat
^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-StringMatchesFormat
   :start-after: //example-start: assertStringMatchesFormat
   :end-before: //example-end: assertStringMatchesFormat
   :dedent: 4
   :prepend: <?php

assertStringNotMatchesFormat
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-StringNotMatchesFormat
   :start-after: //example-start: assertStringNotMatchesFormat
   :end-before: //example-end: assertStringNotMatchesFormat
   :dedent: 4
   :prepend: <?php

assertStringStartsNotWith
^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-StringStartsNotWith
   :start-after: //example-start: assertStringStartsNotWith
   :end-before: //example-end: assertStringStartsNotWith
   :dedent: 4
   :prepend: <?php

assertStringEndsWith
^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-StringEndsWith
   :start-after: //example-start: assertStringEndsWith
   :end-before: //example-end: assertStringEndsWith
   :dedent: 4
   :prepend: <?php

assertStringEndsNotWith
^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-StringEndsNotWith
   :start-after: //example-start: assertStringEndsNotWith
   :end-before: //example-end: assertStringEndsNotWith
   :dedent: 4
   :prepend: <?php

assertJson
^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-Json
   :start-after: //example-start: assertJson
   :end-before: //example-end: assertJson
   :dedent: 4
   :prepend: <?php

assertJsonStringEqualsJsonString
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-JsonStringEqualsJsonString
   :start-after: //example-start: assertJsonStringEqualsJsonString
   :end-before: //example-end: assertJsonStringEqualsJsonString
   :dedent: 4
   :prepend: <?php

assertJsonStringNotEqualsJsonString
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../src/AssertTrait.php
   :name: assert-JsonStringNotEqualsJsonString
   :start-after: //example-start: assertJsonStringNotEqualsJsonString
   :end-before: //example-end: assertJsonStringNotEqualsJsonString
   :dedent: 4
   :prepend: <?php
