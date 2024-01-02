<?php

namespace App\Controller\Api;

use App\Document\TestDocument;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test-documents', name: 'testDocument')]
class TestDocumentController
{
    public function __construct(private ManagerRegistry $odmManagerRegistry)
    {
    }

    #[Route(name: 'Create', methods: ['POST'])]
    public function create(
        #[RequestBody]
        TestDocument $testDocument,
    ): TestDocument {
        $manager = $this->odmManagerRegistry->getManagerForClass(TestDocument::class);

        $manager->persist($testDocument);

        $manager->flush();

        return $testDocument;
    }
}
