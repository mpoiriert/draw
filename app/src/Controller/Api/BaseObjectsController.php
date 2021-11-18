<?php

namespace App\Controller\Api;

use App\Entity\BaseObject;
use Draw\Component\OpenApi\Schema as OpenApi;
use Symfony\Component\Routing\Annotation\Route;

class BaseObjectsController
{
    /**
     * @Route(name="base_object_list", methods={"GET"}, path="/base-objects")
     *
     * @OpenApi\Operation(operationId="baseObjectList")
     *
     * @return BaseObject[] The list of base object
     */
    public function listAction(): array
    {
        return [];
    }
}
