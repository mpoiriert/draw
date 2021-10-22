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

.. literalinclude:: ../AssertTrait.php
   :name: assert-ArraySubset
   :start-after: //example-start: assertArraySubset
   :end-before: //example-end: assertArraySubset
   :dedent: 4
   :prepend: <?php

assertContains
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Contains
   :start-after: //example-start: assertContains
   :end-before: //example-end: assertContains
   :dedent: 4
   :prepend: <?php

assertNotContains
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotContains
   :start-after: //example-start: assertNotContains
   :end-before: //example-end: assertNotContains
   :dedent: 4
   :prepend: <?php

assertContainsOnly
^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-ContainsOnly
   :start-after: //example-start: assertContainsOnly
   :end-before: //example-end: assertContainsOnly
   :dedent: 4
   :prepend: <?php

assertContainsOnlyInstancesOf
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-ContainsOnlyInstancesOf
   :start-after: //example-start: assertContainsOnlyInstancesOf
   :end-before: //example-end: assertContainsOnlyInstancesOf
   :dedent: 4
   :prepend: <?php

assertNotContainsOnly
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotContainsOnly
   :start-after: //example-start: assertNotContainsOnly
   :end-before: //example-end: assertNotContainsOnly
   :dedent: 4
   :prepend: <?php

assertCount
^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Count
   :start-after: //example-start: assertCount
   :end-before: //example-end: assertCount
   :dedent: 4
   :prepend: <?php

assertNotCount
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotCount
   :start-after: //example-start: assertNotCount
   :end-before: //example-end: assertNotCount
   :dedent: 4
   :prepend: <?php

assertEquals
^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Equals
   :start-after: //example-start: assertEquals
   :end-before: //example-end: assertEquals
   :dedent: 4
   :prepend: <?php

assertNotEquals
^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotEquals
   :start-after: //example-start: assertNotEquals
   :end-before: //example-end: assertNotEquals
   :dedent: 4
   :prepend: <?php

assertEmpty
^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Empty
   :start-after: //example-start: assertEmpty
   :end-before: //example-end: assertEmpty
   :dedent: 4
   :prepend: <?php

assertNotEmpty
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotEmpty
   :start-after: //example-start: assertNotEmpty
   :end-before: //example-end: assertNotEmpty
   :dedent: 4
   :prepend: <?php

assertGreaterThan
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-GreaterThan
   :start-after: //example-start: assertGreaterThan
   :end-before: //example-end: assertGreaterThan
   :dedent: 4
   :prepend: <?php

assertGreaterThanOrEqual
^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-GreaterThanOrEqual
   :start-after: //example-start: assertGreaterThanOrEqual
   :end-before: //example-end: assertGreaterThanOrEqual
   :dedent: 4
   :prepend: <?php

assertLessThan
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-LessThan
   :start-after: //example-start: assertLessThan
   :end-before: //example-end: assertLessThan
   :dedent: 4
   :prepend: <?php

assertLessThanOrEqual
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-LessThanOrEqual
   :start-after: //example-start: assertLessThanOrEqual
   :end-before: //example-end: assertLessThanOrEqual
   :dedent: 4
   :prepend: <?php

assertTrue
^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-True
   :start-after: //example-start: assertTrue
   :end-before: //example-end: assertTrue
   :dedent: 4
   :prepend: <?php

assertNotTrue
^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotTrue
   :start-after: //example-start: assertNotTrue
   :end-before: //example-end: assertNotTrue
   :dedent: 4
   :prepend: <?php

assertFalse
^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-False
   :start-after: //example-start: assertFalse
   :end-before: //example-end: assertFalse
   :dedent: 4
   :prepend: <?php

assertNotFalse
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotFalse
   :start-after: //example-start: assertNotFalse
   :end-before: //example-end: assertNotFalse
   :dedent: 4
   :prepend: <?php

assertNull
^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Null
   :start-after: //example-start: assertNull
   :end-before: //example-end: assertNull
   :dedent: 4
   :prepend: <?php

assertNotNull
^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotNull
   :start-after: //example-start: assertNotNull
   :end-before: //example-end: assertNotNull
   :dedent: 4
   :prepend: <?php

assertFinite
^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Finite
   :start-after: //example-start: assertFinite
   :end-before: //example-end: assertFinite
   :dedent: 4
   :prepend: <?php

assertInfinite
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Infinite
   :start-after: //example-start: assertInfinite
   :end-before: //example-end: assertInfinite
   :dedent: 4
   :prepend: <?php

assertNan
^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Nan
   :start-after: //example-start: assertNan
   :end-before: //example-end: assertNan
   :dedent: 4
   :prepend: <?php

assertSame
^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Same
   :start-after: //example-start: assertSame
   :end-before: //example-end: assertSame
   :dedent: 4
   :prepend: <?php

assertNotSame
^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotSame
   :start-after: //example-start: assertNotSame
   :end-before: //example-end: assertNotSame
   :dedent: 4
   :prepend: <?php

assertInstanceOf
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-InstanceOf
   :start-after: //example-start: assertInstanceOf
   :end-before: //example-end: assertInstanceOf
   :dedent: 4
   :prepend: <?php

assertNotInstanceOf
^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotInstanceOf
   :start-after: //example-start: assertNotInstanceOf
   :end-before: //example-end: assertNotInstanceOf
   :dedent: 4
   :prepend: <?php

assertInternalType
^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-InternalType
   :start-after: //example-start: assertInternalType
   :end-before: //example-end: assertInternalType
   :dedent: 4
   :prepend: <?php

assertNotInternalType
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotInternalType
   :start-after: //example-start: assertNotInternalType
   :end-before: //example-end: assertNotInternalType
   :dedent: 4
   :prepend: <?php

assertRegExp
^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-RegExp
   :start-after: //example-start: assertRegExp
   :end-before: //example-end: assertRegExp
   :dedent: 4
   :prepend: <?php

assertNotRegExp
^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotRegExp
   :start-after: //example-start: assertNotRegExp
   :end-before: //example-end: assertNotRegExp
   :dedent: 4
   :prepend: <?php

assertSameSize
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-SameSize
   :start-after: //example-start: assertSameSize
   :end-before: //example-end: assertSameSize
   :dedent: 4
   :prepend: <?php

assertNotSameSize
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotSameSize
   :start-after: //example-start: assertNotSameSize
   :end-before: //example-end: assertNotSameSize
   :dedent: 4
   :prepend: <?php

assertStringMatchesFormat
^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringMatchesFormat
   :start-after: //example-start: assertStringMatchesFormat
   :end-before: //example-end: assertStringMatchesFormat
   :dedent: 4
   :prepend: <?php

assertStringNotMatchesFormat
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringNotMatchesFormat
   :start-after: //example-start: assertStringNotMatchesFormat
   :end-before: //example-end: assertStringNotMatchesFormat
   :dedent: 4
   :prepend: <?php

assertStringStartsWith
^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringStartsWith
   :start-after: //example-start: assertStringStartsWith
   :end-before: //example-end: assertStringStartsWith
   :dedent: 4
   :prepend: <?php

assertStringStartsNotWith
^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringStartsNotWith
   :start-after: //example-start: assertStringStartsNotWith
   :end-before: //example-end: assertStringStartsNotWith
   :dedent: 4
   :prepend: <?php

assertStringEndsWith
^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringEndsWith
   :start-after: //example-start: assertStringEndsWith
   :end-before: //example-end: assertStringEndsWith
   :dedent: 4
   :prepend: <?php

assertStringEndsNotWith
^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringEndsNotWith
   :start-after: //example-start: assertStringEndsNotWith
   :end-before: //example-end: assertStringEndsNotWith
   :dedent: 4
   :prepend: <?php

assertJson
^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-Json
   :start-after: //example-start: assertJson
   :end-before: //example-end: assertJson
   :dedent: 4
   :prepend: <?php

assertJsonStringEqualsJsonString
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-JsonStringEqualsJsonString
   :start-after: //example-start: assertJsonStringEqualsJsonString
   :end-before: //example-end: assertJsonStringEqualsJsonString
   :dedent: 4
   :prepend: <?php

assertJsonStringNotEqualsJsonString
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-JsonStringNotEqualsJsonString
   :start-after: //example-start: assertJsonStringNotEqualsJsonString
   :end-before: //example-end: assertJsonStringNotEqualsJsonString
   :dedent: 4
   :prepend: <?php

assertContainsEquals
^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-ContainsEquals
   :start-after: //example-start: assertContainsEquals
   :end-before: //example-end: assertContainsEquals
   :dedent: 4
   :prepend: <?php

assertNotContainsEquals
^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotContainsEquals
   :start-after: //example-start: assertNotContainsEquals
   :end-before: //example-end: assertNotContainsEquals
   :dedent: 4
   :prepend: <?php

assertEqualsCanonicalizing
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-EqualsCanonicalizing
   :start-after: //example-start: assertEqualsCanonicalizing
   :end-before: //example-end: assertEqualsCanonicalizing
   :dedent: 4
   :prepend: <?php

assertEqualsIgnoringCase
^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-EqualsIgnoringCase
   :start-after: //example-start: assertEqualsIgnoringCase
   :end-before: //example-end: assertEqualsIgnoringCase
   :dedent: 4
   :prepend: <?php

assertEqualsWithDelta
^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-EqualsWithDelta
   :start-after: //example-start: assertEqualsWithDelta
   :end-before: //example-end: assertEqualsWithDelta
   :dedent: 4
   :prepend: <?php

assertNotEqualsCanonicalizing
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotEqualsCanonicalizing
   :start-after: //example-start: assertNotEqualsCanonicalizing
   :end-before: //example-end: assertNotEqualsCanonicalizing
   :dedent: 4
   :prepend: <?php

assertNotEqualsIgnoringCase
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotEqualsIgnoringCase
   :start-after: //example-start: assertNotEqualsIgnoringCase
   :end-before: //example-end: assertNotEqualsIgnoringCase
   :dedent: 4
   :prepend: <?php

assertNotEqualsWithDelta
^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-NotEqualsWithDelta
   :start-after: //example-start: assertNotEqualsWithDelta
   :end-before: //example-end: assertNotEqualsWithDelta
   :dedent: 4
   :prepend: <?php

assertIsArray
^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsArray
   :start-after: //example-start: assertIsArray
   :end-before: //example-end: assertIsArray
   :dedent: 4
   :prepend: <?php

assertIsBool
^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsBool
   :start-after: //example-start: assertIsBool
   :end-before: //example-end: assertIsBool
   :dedent: 4
   :prepend: <?php

assertIsFloat
^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsFloat
   :start-after: //example-start: assertIsFloat
   :end-before: //example-end: assertIsFloat
   :dedent: 4
   :prepend: <?php

assertIsInt
^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsInt
   :start-after: //example-start: assertIsInt
   :end-before: //example-end: assertIsInt
   :dedent: 4
   :prepend: <?php

assertIsNumeric
^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNumeric
   :start-after: //example-start: assertIsNumeric
   :end-before: //example-end: assertIsNumeric
   :dedent: 4
   :prepend: <?php

assertIsObject
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsObject
   :start-after: //example-start: assertIsObject
   :end-before: //example-end: assertIsObject
   :dedent: 4
   :prepend: <?php

assertIsResource
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsResource
   :start-after: //example-start: assertIsResource
   :end-before: //example-end: assertIsResource
   :dedent: 4
   :prepend: <?php

assertIsString
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsString
   :start-after: //example-start: assertIsString
   :end-before: //example-end: assertIsString
   :dedent: 4
   :prepend: <?php

assertIsScalar
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsScalar
   :start-after: //example-start: assertIsScalar
   :end-before: //example-end: assertIsScalar
   :dedent: 4
   :prepend: <?php

assertIsCallable
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsCallable
   :start-after: //example-start: assertIsCallable
   :end-before: //example-end: assertIsCallable
   :dedent: 4
   :prepend: <?php

assertIsIterable
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsIterable
   :start-after: //example-start: assertIsIterable
   :end-before: //example-end: assertIsIterable
   :dedent: 4
   :prepend: <?php

assertIsNotArray
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotArray
   :start-after: //example-start: assertIsNotArray
   :end-before: //example-end: assertIsNotArray
   :dedent: 4
   :prepend: <?php

assertIsNotBool
^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotBool
   :start-after: //example-start: assertIsNotBool
   :end-before: //example-end: assertIsNotBool
   :dedent: 4
   :prepend: <?php

assertIsNotFloat
^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotFloat
   :start-after: //example-start: assertIsNotFloat
   :end-before: //example-end: assertIsNotFloat
   :dedent: 4
   :prepend: <?php

assertIsNotInt
^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotInt
   :start-after: //example-start: assertIsNotInt
   :end-before: //example-end: assertIsNotInt
   :dedent: 4
   :prepend: <?php

assertIsNotNumeric
^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotNumeric
   :start-after: //example-start: assertIsNotNumeric
   :end-before: //example-end: assertIsNotNumeric
   :dedent: 4
   :prepend: <?php

assertIsNotObject
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotObject
   :start-after: //example-start: assertIsNotObject
   :end-before: //example-end: assertIsNotObject
   :dedent: 4
   :prepend: <?php

assertIsNotResource
^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotResource
   :start-after: //example-start: assertIsNotResource
   :end-before: //example-end: assertIsNotResource
   :dedent: 4
   :prepend: <?php

assertIsNotString
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotString
   :start-after: //example-start: assertIsNotString
   :end-before: //example-end: assertIsNotString
   :dedent: 4
   :prepend: <?php

assertIsNotScalar
^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotScalar
   :start-after: //example-start: assertIsNotScalar
   :end-before: //example-end: assertIsNotScalar
   :dedent: 4
   :prepend: <?php

assertIsNotCallable
^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotCallable
   :start-after: //example-start: assertIsNotCallable
   :end-before: //example-end: assertIsNotCallable
   :dedent: 4
   :prepend: <?php

assertIsNotIterable
^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-IsNotIterable
   :start-after: //example-start: assertIsNotIterable
   :end-before: //example-end: assertIsNotIterable
   :dedent: 4
   :prepend: <?php

assertStringContainsString
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringContainsString
   :start-after: //example-start: assertStringContainsString
   :end-before: //example-end: assertStringContainsString
   :dedent: 4
   :prepend: <?php

assertStringContainsStringIgnoringCase
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringContainsStringIgnoringCase
   :start-after: //example-start: assertStringContainsStringIgnoringCase
   :end-before: //example-end: assertStringContainsStringIgnoringCase
   :dedent: 4
   :prepend: <?php

assertStringNotContainsString
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringNotContainsString
   :start-after: //example-start: assertStringNotContainsString
   :end-before: //example-end: assertStringNotContainsString
   :dedent: 4
   :prepend: <?php

assertStringNotContainsStringIgnoringCase
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. literalinclude:: ../AssertTrait.php
   :name: assert-StringNotContainsStringIgnoringCase
   :start-after: //example-start: assertStringNotContainsStringIgnoringCase
   :end-before: //example-end: assertStringNotContainsStringIgnoringCase
   :dedent: 4
   :prepend: <?php
