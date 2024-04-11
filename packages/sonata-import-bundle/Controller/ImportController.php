<?php

namespace Draw\Bundle\SonataImportBundle\Controller;

use Draw\Bundle\SonataImportBundle\Entity\Import;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ImportController extends CRUDController
{
    public function downloadAction(Import $import): Response
    {
        $response = new Response($import->getFileContent());

        $disposition = $response->headers
            ->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'import_'.$import->getId().'.csv'
            );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
