<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VersionLinkDocumentationExtractor implements ExtractorInterface
{
    private array $versions;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(array $versions, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->versions = $versions;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Root) {
            return false;
        }

        return true;
    }

    /**
     * @param string $source
     * @param Root   $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $version = $extractionContext->getParameter('api.version');

        $description = $target->info->description;
        $target->info->description = '';
        foreach ($this->versions as $otherVersion) {
            if ((string) $otherVersion === $version) {
                continue;
            }

            $otherVersionUrl = $this->urlGenerator
                ->generate(
                    'draw_open_api.versioned_api_doc',
                    ['version' => $otherVersion],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

            $target->info->description .= 'Go to <a href="'.$otherVersionUrl.'">Version '.$otherVersion.'</a><br/>';
        }

        $target->info->description .= $description;
    }
}
