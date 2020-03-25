<?php namespace Draw\Bundle\OpenApiBundle\Tests\Mock\Controller;

use Draw\Bundle\OpenApiBundle\Request\DeserializeBody;
use Draw\Bundle\OpenApiBundle\Tests\Mock\Model\Test;
use Draw\Bundle\OpenApiBundle\View\View;
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
     * @DeserializeBody(
     *     name="test",
     *     deserializationGroups={"Included"}
     * )
     *
     * @View(
     *     statusCode=201,
     *     serializerGroups={"Included"},
     *     headers={
     *       "X-Draw":@OpenApi\Header(type="string", description="Description of the header")
     *     }
     * )
     *
     * @param string $param1
     * @param Test $test
     *
     * @return Test The created test entity
     */
    public function createAction(Test $test, $param1 = 'default')
    {
        $test->setProperty($param1);

        return $test;
    }
}