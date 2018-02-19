<?php

require "vendor/autoload.php";

$reflectionClass = new ReflectionClass(PHPUnit\Framework\Assert::class);
$methods = json_decode(file_get_contents(__DIR__ . '/../resources/methods.json'), true);

$class = '<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-assertion-builder.php script.
 * Do not modify manually.
 */
namespace Draw\DataTester;

use ArrayAccess;
use Countable;
use Traversable;

class AssertionBuilder
{
    private $assertions = [];

    public function __invoke(Tester $tester)
    {
        foreach ($this->assertions as $assertion) {
            $methodName = array_shift($assertion);
            call_user_func_array([$tester, $methodName], $assertion);
        }
    }
';

foreach($methods as $methodName => $information) {
    if($information['ignore']) {
        continue;
    }
    $method = $reflectionClass->getMethod($methodName);

    $docComment = $method->getDocComment();

    $parameters = [];
    foreach($method->getParameters() as $parameter) {
        if($information['dataParameter'] === $parameter->getName()) {
            continue;
        }
        $parameterString = '';
        if($parameter->hasType()) {
            $parameterString .= $parameter->getType() . ' ';
        }
        $parameterString .= '$' . $parameter->getName();

        if($parameter->isDefaultValueAvailable()) {
            $parameterString .= ' = ' . var_export($parameter->getDefaultValue(), true);
        }
        $parameters[] = $parameterString;
    }

    $parametersString = implode(', ', $parameters);

    $docCommentLines = [];
    foreach(explode("\n", $docComment) as $line) {
        if(strpos($line, '$' . $information['dataParameter']) !== false) {
            if(strpos($line, '@param') !== false) {
                continue;
            }
        }

        if(strpos($line, '@throws') !== false) {
            continue;
        }

        $docCommentLines[] = $line;
    }

    $docCommentLines[count($docCommentLines) -1] = '     * @return $this';
    $docCommentLines[] = '     */';

    $correctedDocComment = implode("\n", $docCommentLines);

    $class .= "
    {$correctedDocComment}    
    public function {$methodName}({$parametersString}) {
        \$this->assertions[] = array_merge(['$methodName'], func_get_args());
        
        return \$this;
    }    
";
}

$class .= "
}";

file_put_contents(__DIR__ . '/../src/AssertionBuilder.php', $class);