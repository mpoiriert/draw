<?php

require "../../../vendor/autoload.php";

$reflectionClass = new ReflectionClass(PHPUnit\Framework\Assert::class);
$methods = json_decode(file_get_contents(__DIR__ . '/../resources/methods.json'), true);

$class = '<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-trait.php script.
 * Do not modify manually.
 */
namespace Draw\DataTester;

use PHPUnit\Framework\Assert;
use ArrayAccess;
use Countable;
use Traversable;

trait AssertTrait
{
    /**
     * @return mixed Return the data that is currently tested
     */
    abstract public function getData();
';

foreach($methods as $methodName => $information) {
    if($information['ignore']) {
        continue;
    }
    $method = $reflectionClass->getMethod($methodName);

    $docComment = $method->getDocComment();

    $callParameters = [];
    $parameters = [];
    foreach($method->getParameters() as $parameter) {
        if($information['dataParameter'] === $parameter->name) {
            $callParameters[] = '$this->getData()';
            continue;
        }
        $parameterString = '';
        if(method_exists($parameter, 'hasType') && $parameter->hasType()) {
            $parameterString .= $parameter->getType() . ' ';
        }
        $parameterString .= '$' . $parameter->name;

        if($parameter->isDefaultValueAvailable()) {
            $parameterString .= ' = ' . var_export($parameter->getDefaultValue(), true);
        }
        $parameters[] = $parameterString;
        $callParameters[] = '$' . $parameter->name;
    }

    $callParametersString = implode(', ', $callParameters);
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
    //example-start: {$methodName}    
    {$correctedDocComment}    
    public function {$methodName}({$parametersString}) {
        Assert::{$methodName}({$callParametersString});
        
        return \$this;
    }    
    //example-end: {$methodName}  
";
}

$class .= "
}";

file_put_contents(__DIR__ . '/../src/AssertTrait.php', $class);