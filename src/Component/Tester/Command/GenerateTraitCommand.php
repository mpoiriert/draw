<?php namespace Draw\Component\Tester\Command;

use PHPUnit\Framework\Assert;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTraitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('draw:tester:generate-trait')
            ->setDescription('Generate the assert trait base on the methods available')
            ->addArgument(
                'assertMethodsFilePath',
                InputArgument::OPTIONAL,
                'The file path where the methods configuration are.',
                __DIR__ . '/../Resources/config/assert_methods.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('assertMethodsFilePath');
        $reflectionClass = new ReflectionClass(Assert::class);
        $methods = json_decode(file_get_contents($filePath), true);

        $class = '<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-trait.php script.
 * Do not modify manually.
 */
namespace Draw\Component\Tester;

use PHPUnit\Framework\Assert;
use ArrayAccess;
use Countable;
use Traversable;

/**
 * @internal
 */
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

            if(count($docCommentLines) == 1) {
                $docCommentLines[0] = '/**';
                $docCommentLines[1] = '';
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

        file_put_contents(__DIR__ . '/../AssertTrait.php', $class);
    }
}