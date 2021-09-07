<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Mock\Controller;

use Draw\Bundle\OpenApiBundle\Request\Deserialization;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Bundle\OpenApiBundle\Tests\Mock\Model\Test;
use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param string $param1
     *
     * @return Test The created test entity
     */
    public function createAction(Test $test, $param1 = 'default')
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
     * @OpenApi\QueryParameter(name="param1", type="array", collectionFormat="csv")
     *
     * @return array The query parameter value
     */
    public function arrayAction(array $param1)
    {
        return $param1;
    }
}
