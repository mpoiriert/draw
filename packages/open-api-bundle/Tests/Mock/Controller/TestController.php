<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Mock\Controller;

use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Bundle\OpenApiBundle\Tests\Mock\Model\Test;
use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class TestController
{
    /**
     * @Route(methods={"POST"}, path="/tests")
     *
     * @OpenApi\Operation(
     *     operationId="createTest",
     *     tags={"test"}
     * )
     *
     * @OpenApi\QueryParameter(name="param1")
     *
     * @OpenApi\Vendor(name="x-test", value={"key":"value", "object":{"property":"value"}})
     *
     * @Deserialization(
     *     name="test",
     *     deserializationGroups={"Included"}
     * )
     *
     * @Serialization(
     *     statusCode=201,
     *     serializerGroups={"Included"},
     *     headers={
     *       "X-Draw":@OpenApi\Header(type="string", description="Description of the header")
     *     }
     * )
     *
     * @param string $param1 param1 description
     *
     * @return Test The created test entity
     */
    public function createAction(Test $test, string $param1 = 'default'): Test
    {
        $test->setProperty($param1);

        return $test;
    }

    /**
     * @Route(methods={"POST"}, path="/tests-array")
     *
     * @OpenApi\Operation(
     *     operationId="arrayTest",
     *     tags={"test"}
     * )
     *
     * @OpenApi\QueryParameter(
     *     name="param1",
     *     type="array",
     *     collectionFormat="csv",
     *     constraints={
     *         @Assert\NotNull()
     *     }
     * )
     *
     * @param ?array $param1 The parameter
     *
     * @return array The query parameter value
     */
    public function arrayAction(?array $param1): ?array
    {
        return $param1;
    }

    /**
     * @Route(methods={"GET"}, path="/v2/void", defaults={"_api_version":2})
     *
     * @OpenApi\Operation(
     *     operationId="version2",
     *     tags={"test"}
     * )
     *
     * @return void Nothing
     */
    public function version2Action(): void
    {
    }
}
