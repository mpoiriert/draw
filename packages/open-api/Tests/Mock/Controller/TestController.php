<?php

namespace Draw\Component\OpenApi\Tests\Mock\Controller;

use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\Component\OpenApi\Tests\Mock\Model\TestClass;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class TestController
{
    /**
     * @param string $param1 param1 description
     *
     * @return TestClass The created test entity
     */
    #[Route(path: '/tests', methods: ['POST'])]
    #[OpenApi\Operation(operationId: 'createTest', tags: ['test'])]
    #[Serialization(
        statusCode: 201,
        serializerGroups: ['Included']
    )]
    #[OpenApi\Vendor(
        name: 'x-test',
        value: [
            'key' => 'value',
            'object' => [
                'property' => 'value',
            ],
        ]
    )]
    #[OpenApi\Header(name: 'X-Draw', description: 'Description of the header', type: 'string')]
    public function createAction(
        #[RequestBody(deserializationGroups: ['Included'])]
        TestClass $test,
        #[OpenApi\QueryParameter]
        string $param1 = 'default',
    ): TestClass {
        $test->setProperty($param1);

        return $test;
    }

    /**
     * @param ?array $param1 The parameter
     *
     * @return ?array The query parameter value
     */
    #[Route(path: '/tests-array', methods: ['POST'])]
    #[OpenApi\Operation(operationId: 'arrayTest', tags: ['test'])]
    public function arrayAction(
        #[OpenApi\QueryParameter(
            type: 'array',
            collectionFormat: 'csv',
            constraints: [
                new Assert\NotNull(),
            ],
            items: new OpenApi\Items(type: 'string')
        )]
        ?array $param1,
    ): ?array {
        return $param1;
    }

    /**
     * @return void Nothing
     */
    #[Route(path: '/v2/void', defaults: ['_api_version' => 2], methods: ['GET'])]
    #[OpenApi\Operation(operationId: 'version2', tags: ['test'])]
    public function version2Action(): void
    {
    }
}
