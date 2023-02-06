<?php

namespace Draw\Component\OpenApi\Tests\Mock\Controller;

use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema as OpenApi;
use Draw\Component\OpenApi\Serializer\Serialization;
use Draw\Component\OpenApi\Tests\Mock\Model\Test;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class TestController
{
    /**
     * @Route(methods={"POST"}, path="/tests")
     *
     * @param string $param1 param1 description
     *
     * @return Test The created test entity
     */
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
        #[RequestBody(deserializationGroups: ['Included'])] Test $test,
        #[OpenApi\QueryParameter] string $param1 = 'default'
    ): Test {
        $test->setProperty($param1);

        return $test;
    }

    /**
     * @Route(methods={"POST"}, path="/tests-array")
     *
     * @param ?array $param1 The parameter
     *
     * @return ?array The query parameter value
     */
    #[OpenApi\Operation(operationId: 'arrayTest', tags: ['test'])]
    public function arrayAction(
        #[OpenApi\QueryParameter(
            type: 'array',
            collectionFormat: 'csv',
            constraints: [
                new Assert\NotNull(),
            ]
        )]
        ?array $param1
    ): ?array {
        return $param1;
    }

    /**
     * @Route(methods={"GET"}, path="/v2/void", defaults={"_api_version": 2})
     *
     * @return void Nothing
     */
    #[OpenApi\Operation(operationId: 'version2', tags: ['test'])]
    public function version2Action(): void
    {
    }
}
