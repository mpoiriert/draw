<?php

// This is for our test to be compatible with PHP 5.6 version
if (!class_exists('PHPUnit\\Framework\\ExpectationFailedException')) {
    class_alias('PHPUnit_Framework_ExpectationFailedException', 'PHPUnit\\Framework\\ExpectationFailedException');
}