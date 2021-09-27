<?php

namespace Draw\Component\OpenApi;

use Doctrine\Common\Annotations\AnnotationReader;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\RootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Root as Schema;
use Draw\Component\OpenApi\Serializer\SerializerHandler;
use Draw\Component\OpenApi\Serializer\SerializerListener;
use InvalidArgumentException;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenApi
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $extractors = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ExtractorInterface[]
     */
    private $sortedExtractors;

    /**
     * Whether or not we want to clean the schema on dump.
     *
     * @var bool
     */
    private $cleanOnDump = false;

    /**
     * @var SchemaCleaner
     */
    private $schemaCleaner;

    public function __construct(SerializerInterface $serializer = null, SchemaCleaner $schemaCleaner = null)
    {
        if (null === $serializer) {
            $serializer = SerializerBuilder::create()
                ->configureListeners(
                    function (EventDispatcher $dispatcher) {
                        $dispatcher->addSubscriber(new SerializerListener());
                    }
                )
                ->configureHandlers(
                    function (HandlerRegistry $handlerRegistry) {
                        $handlerRegistry->registerSubscribingHandler(new SerializerHandler());
                    }
                )
                ->build();
        }

        $this->serializer = $serializer;
        $this->schemaCleaner = $schemaCleaner ?: new SchemaCleaner();

        $this->registerExtractor(new RootSchemaExtractor($this->serializer), -1, 'open_api');
    }

    /**
     * @required
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerExtractor(ExtractorInterface $extractorInterface, $position = 0, $section = 'default')
    {
        $this->extractors[$section][$position][] = $extractorInterface;
        $this->sortedExtractors = null;
    }

    /**
     * @return bool
     */
    public function getCleanOnDump()
    {
        return $this->cleanOnDump;
    }

    /**
     * @param bool $cleanOnDump
     */
    public function setCleanOnDump($cleanOnDump)
    {
        $this->cleanOnDump = $cleanOnDump;
    }

    /**
     * @param bool $validate
     *
     * @return string
     */
    public function dump(Schema $schema, $validate = true)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new PreDumpRootSchemaEvent($schema));
        }

        if ($this->cleanOnDump) {
            $schema = $this->schemaCleaner->clean($schema);
        }

        if ($validate) {
            $this->validate($schema);
        }

        return $this->serializer->serialize($schema, 'json');
    }

    public function validate(Schema $schema)
    {
        /** @var ConstraintViolationList $result */
        $annotationReader = new AnnotationReader();
        $builder = Validation::createValidatorBuilder();
        if (method_exists($builder, 'setDoctrineAnnotationReader')) {
            $builder
                ->enableAnnotationMapping(true)
                ->setDoctrineAnnotationReader($annotationReader);
        } else {
            $builder->enableAnnotationMapping($annotationReader);
        }

        $result = $builder
            ->getValidator()
            ->validate($schema, null, [Constraint::DEFAULT_GROUP]);

        if (count($result)) {
            throw new InvalidArgumentException(''.$result);
        }
    }

    /**
     * @param $source
     * @param null $type
     *
     * @return Schema
     */
    public function extract($source, $type = null, ExtractionContextInterface $extractionContext = null)
    {
        if (null === $type) {
            $type = new Schema();
        }

        if (null === $extractionContext) {
            $extractionContext = new ExtractionContext($this, $type);
        }

        foreach ($this->getSortedExtractors() as $extractor) {
            if ($extractor->canExtract($source, $type, $extractionContext)) {
                $extractor->extract($source, $type, $extractionContext);
            }
        }

        return $type;
    }

    /**
     * @return ExtractorInterface[]
     */
    private function getSortedExtractors()
    {
        if (null === $this->sortedExtractors) {
            $this->sortedExtractors = [];
            foreach ($this->extractors as $section => $extractors) {
                ksort($extractors);
                array_unshift($extractors, $this->sortedExtractors);
                $this->sortedExtractors = call_user_func_array('array_merge', $extractors);
            }
        }

        return $this->sortedExtractors;
    }
}
