<?php

namespace Draw\Component\DataSynchronizer;

class Artefact extends \ZipArchive
{
    /**
     * We need to keep track of the order of the files since it affect restoration.
     */
    private array $dataFiles = [];

    public static function loadFromFile(string $file): self
    {
        $artefact = new self();

        if (true !== ($open = $artefact->open($file))) {
            throw new \RuntimeException('Unable to read file: '.$file.'. Error code: '.$open);
        }

        return $artefact;
    }

    public static function createFromFile(string $file): self
    {
        $artefact = new self();

        if (true !== ($open = $artefact->open($file, \ZipArchive::CREATE))) {
            throw new \RuntimeException('Unable to read file: '.$file.'. Error code: '.$open);
        }

        return $artefact;
    }

    public function addClassData(string $className, array $data): bool
    {
        return $this->addFromString(
            $this->dataFiles[] = 'data/'.str_replace('\\', '_', $className).'.json',
            json_encode(
                [$className => $data],
                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE
            )
        );
    }

    public function getExtractionDataFiles(): array
    {
        return $this->jsonDecodeFromName('files.json');
    }

    public function jsonDecodeFromName(string $filePath): array
    {
        return json_decode(
            $this->getFromName($filePath),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );
    }

    public function finalize(): void
    {
        $this->addFromString(
            'files.json',
            json_encode(
                $this->dataFiles,
                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE
            )
        );

        $this->close();
    }
}
