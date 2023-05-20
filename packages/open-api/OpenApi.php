<?php

namespace Draw\Component\OpenApi;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Exception\ExtractionCompletedException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\JsonRootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Root as Schema;
use Draw\Component\OpenApi\Serializer\Handler\OpenApiHandler;
use Draw\Component\OpenApi\Serializer\Subscriber\OpenApiSubscriber;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenApi
{
    private SerializerInterface $serializer;

    /**
     * @var iterable|ExtractorInterface[]
     */
    private iterable $extractors;

    /**
     * @param array<Scope>|null $scopes
     */
    public function __construct(
        ?iterable $extractors = null,
        ?SerializerInterface $serializer = null,
        private ?EventDispatcherInterface $eventDispatcher = null,
        private ?array $scopes = null,
    ) {
        if (null === $serializer) {
            $serializer = SerializerBuilder::create()
                ->configureListeners(
                    function (EventDispatcher $dispatcher): void {
                        $dispatcher->addSubscriber(new OpenApiSubscriber());
                    }
                )
                ->configureHandlers(
                    function (HandlerRegistry $handlerRegistry): void {
                        $handlerRegistry->registerSubscribingHandler(new OpenApiHandler());
                    }
                )
                ->build();
        }

        $this->eventDispatcher ??= new SymfonyEventDispatcher();
        $this->serializer = $serializer;
        $this->extractors = $extractors ?: [new JsonRootSchemaExtractor($this->serializer)];
    }

    public function matchScope(ExtractionContextInterface $extractionContext, Operation $operation): bool
    {
        if (null === $this->scopes) {
            return true;
        }

        $scopeName = $extractionContext->getParameter('api.scope');

        if (null === $scopeName) {
            return false;
        }

        foreach ($this->scopes as $scope) {
            if ($scope->getName() !== $scopeName) {
                continue;
            }

            if (null === $scope->getTags()) {
                return true;
            }

            if (array_intersect($operation->tags ?? [], $scope->getTags())) {
                return true;
            }
        }

        return false;
    }

    public function dump(Schema $schema, bool $validate = true, ?ExtractionContextInterface $extractionContext = null): string
    {
        $extractionContext ??= new ExtractionContext($this, $schema);
        $this->eventDispatcher->dispatch(new PreDumpRootSchemaEvent($schema));

        $schema = $this->eventDispatcher->dispatch(new CleanEvent($schema, $extractionContext))->getRootSchema();

        if ($validate) {
            $this->validate($schema);
        }

        return $this->serializer->serialize($schema, 'json');
    }

    public function validate(Schema $schema): void
    {
        $result = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->getValidator()
            ->validate($schema, null, [Constraint::DEFAULT_GROUP]);

        if (\count($result)) {
            throw new ConstraintViolationListException($result);
        }
    }

    public function extract(
        mixed $source,
        mixed $target = null,
        ?ExtractionContextInterface $extractionContext = null
    ) {
        if (null === $target) {
            $target = new Schema();
        }

        if (null === $extractionContext) {
            $extractionContext = new ExtractionContext($this, $target);
        }

        foreach ($this->extractors as $extractor) {
            if ($extractor->canExtract($source, $target, $extractionContext)) {
                try {
                    $extractor->extract($source, $target, $extractionContext);
                } catch (ExtractionCompletedException) {
                    break;
                }
            }
        }

        return $target;
    }
}
