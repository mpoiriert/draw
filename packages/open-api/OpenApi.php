<?php

namespace Draw\Component\OpenApi;

use Doctrine\Common\Annotations\AnnotationReader;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Exception\ExtractionCompletedException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\JsonRootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root as Schema;
use Draw\Component\OpenApi\Serializer\Handler\OpenApiHandler;
use Draw\Component\OpenApi\Serializer\Subscriber\OpenApiSubscriber;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenApi
{
    private SerializerInterface $serializer;

    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * @var iterable|ExtractorInterface[]
     */
    private iterable $extractors;

    private bool $cleanOnDump = false;

    private SchemaCleaner $schemaCleaner;

    public function __construct(
        ?iterable $extractors = null,
        ?SerializerInterface $serializer = null,
        ?SchemaCleaner $schemaCleaner = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        if (null === $serializer) {
            $serializer = SerializerBuilder::create()
                ->configureListeners(
                    function (EventDispatcher $dispatcher) {
                        $dispatcher->addSubscriber(new OpenApiSubscriber());
                    }
                )
                ->configureHandlers(
                    function (HandlerRegistry $handlerRegistry) {
                        $handlerRegistry->registerSubscribingHandler(new OpenApiHandler());
                    }
                )
                ->build();
        }

        $this->serializer = $serializer;
        $this->schemaCleaner = $schemaCleaner ?: new SchemaCleaner();
        $this->eventDispatcher = $eventDispatcher;
        $this->extractors = $extractors ?: [new JsonRootSchemaExtractor($this->serializer)];
    }

    public function getCleanOnDump(): bool
    {
        return $this->cleanOnDump;
    }

    public function setCleanOnDump(bool $cleanOnDump): void
    {
        $this->cleanOnDump = $cleanOnDump;
    }

    public function dump(Schema $schema, bool $validate = true): string
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new PreDumpRootSchemaEvent($schema));
        }

        if ($this->getCleanOnDump()) {
            $schema = $this->schemaCleaner->clean($schema);
        }

        if ($validate) {
            $this->validate($schema);
        }

        return $this->serializer->serialize($schema, 'json');
    }

    public function validate(Schema $schema): void
    {
        $annotationReader = new AnnotationReader();
        $result = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->setDoctrineAnnotationReader($annotationReader)
            ->getValidator()
            ->validate($schema, null, [Constraint::DEFAULT_GROUP]);

        if (\count($result)) {
            throw new ConstraintViolationListException($result);
        }
    }

    public function extract($source, $type = null, ?ExtractionContextInterface $extractionContext = null)
    {
        if (null === $type) {
            $type = new Schema();
        }

        if (null === $extractionContext) {
            $extractionContext = new ExtractionContext($this, $type);
        }

        foreach ($this->extractors as $extractor) {
            if ($extractor->canExtract($source, $type, $extractionContext)) {
                try {
                    $extractor->extract($source, $type, $extractionContext);
                } catch (ExtractionCompletedException $error) {
                    break;
                }
            }
        }

        return $type;
    }
}
