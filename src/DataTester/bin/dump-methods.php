<?php

require "vendor/autoload.php";

$reflectionClass = new ReflectionClass(PHPUnit\Framework\Assert::class);

if (file_exists($file = __DIR__ . '/../resources/methods.json')) {
    $methods = json_decode(file_get_contents($file), true);
} else {
    $methods = [];
}

foreach ($reflectionClass->getMethods() as $method) {
    if (strpos($method->name, 'assert') !== 0) {
        continue;
    }
    $parameters = [];

    foreach ($method->getParameters() as $parameter) {
        $parameters[] = $parameter->name;
    }

    $guessParameter = null;

    switch (true) {
        case in_array('actual', $parameters):
            $guessParameter = 'actual';
            break;
        case in_array('array', $parameters):
            $guessParameter = 'array';
            break;
        case in_array('haystack', $parameters):
            $guessParameter = 'haystack';
            break;
        case in_array('condition', $parameters):
            $guessParameter = 'condition';
            break;
        case in_array('className', $parameters):
            $guessParameter = 'className';
            break;
        case in_array('object', $parameters):
            $guessParameter = 'object';
            break;
        case in_array('string', $parameters):
            $guessParameter = 'string';
            break;
        case count($parameters) == 2:
            $guessParameter = $parameters[0];
            break;
        default:
            foreach ($parameters as $parameterName) {
                switch (true) {
                    case strpos($parameterName, 'actual') === 0:
                        $guessParameter = $parameterName;
                        break;
                }
            }
            break;
    }

    $ignore = false;

    switch (true) {
        case strpos($method->name, 'assertAttribute') === 0:
        case $method->name === 'assertThat':
            $ignore = true;
            break;

    }

    if (!array_key_exists($method->name, $methods)) {
        $methods[$method->name] = [
            'validated' => false,
            'ignore' => $ignore,
            'dataParameter' => $guessParameter ?: ($ignore ? 'IGNORE' : null),
            'parameters' => $parameters
        ];
    } else {
        $methods[$method->name]['parameters'] = $parameters;
    }
}

file_put_contents(__DIR__ . '/../resources/methods-new.json', json_encode($methods, JSON_PRETTY_PRINT));

