<?php

namespace Draw\Component\Tester\Command;

use PHPUnit\Framework\Assert;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpAssertMethodsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('draw:tester:dump-assert-methods')
            ->setDescription('Dump all PHPUnit Assert Methods in a json file to use for generation ')
            ->addArgument('filePath', InputArgument::REQUIRED, 'The file path where to dump.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        if (file_exists($filePath)) {
            $methods = json_decode(file_get_contents($filePath), true);
        } else {
            $methods = [];
        }

        $reflectionClass = new ReflectionClass(Assert::class);

        foreach ($reflectionClass->getMethods() as $method) {
            if (0 !== strpos($method->name, 'assert')) {
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
                case 2 == count($parameters):
                    $guessParameter = $parameters[0];
                    break;
                default:
                    foreach ($parameters as $parameterName) {
                        switch (true) {
                            case 0 === strpos($parameterName, 'actual'):
                                $guessParameter = $parameterName;
                                break;
                        }
                    }
                    break;
            }

            $ignore = false;

            switch (true) {
                case 0 === strpos($method->name, 'assertAttribute'):
                case 'assertThat' === $method->name:
                    $ignore = true;
                    break;
            }

            if (!array_key_exists($method->name, $methods)) {
                $methods[$method->name] = [
                    'validated' => false,
                    'ignore' => $ignore,
                    'dataParameter' => $guessParameter ?: ($ignore ? 'IGNORE' : null),
                    'parameters' => $parameters,
                ];
            } else {
                $methods[$method->name]['parameters'] = $parameters;
            }
        }

        file_put_contents($filePath, json_encode($methods, JSON_PRETTY_PRINT));

        return 0;
    }
}
