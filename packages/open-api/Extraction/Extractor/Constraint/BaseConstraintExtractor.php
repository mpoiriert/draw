<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Draw\Component\OpenApi\Schema\Schema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class BaseConstraintExtractor implements ConstraintExtractorInterface
{
    private ValidatorInterface $validator;

    #[Required]
    public function setValidator(ValidatorInterface $metadataFactoryInterface): void
    {
        $this->validator = $metadataFactoryInterface;
    }

    abstract public function supportConstraint(Constraint $constraint): bool;

    abstract public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void;

    protected function assertSupportConstraint(Constraint $constraint): void
    {
        if (!$this->supportConstraint($constraint)) {
            throw new \InvalidArgumentException(\sprintf(
                'The constraint of type [%s] is not supported by [%s]',
                $constraint::class,
                static::class
            ));
        }
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        $constraints = [];
        switch (true) {
            case $target instanceof Schema && $source instanceof \ReflectionClass:
                $constraints = $this->getPropertiesConstraints(
                    $source,
                    $target,
                    $this->getValidationGroups($extractionContext)
                );
                break;
            case $target instanceof QueryParameter && $source instanceof QueryParameter:
                $constraints = array_filter(
                    $target->constraints,
                    $this->supportConstraint(...)
                );
                break;
        }

        return !empty(\count($constraints));
    }

    private function getValidationGroups(ExtractionContextInterface $extractionContext): ?array
    {
        $context = $extractionContext->getParameter('model-context', []);

        return \array_key_exists('validation-groups', $context) ? $context['validation-groups'] : null;
    }

    /**
     * @return array<string,array<Constraint>>
     */
    private function getPropertiesConstraints(
        \ReflectionClass $reflectionClass,
        Schema $schema,
        ?array $groups = null,
    ): array {
        $class = $reflectionClass->getName();
        if (!$this->validator->hasMetadataFor($class)) {
            return [];
        }

        if (null === $groups) {
            $groups = [Constraint::DEFAULT_GROUP];
        }

        $constraints = [];

        $classMetadata = $this->validator->getMetadataFor($class);

        if (!$classMetadata instanceof ClassMetadataInterface) {
            throw new \LogicException(\sprintf(
                'Validator::getMetadataFor expect class return to be of type [%s]',
                ClassMetadataInterface::class
            ));
        }

        foreach ($classMetadata->getConstrainedProperties() as $propertyName) {
            // This is to prevent hading properties just because they have validation
            if (!isset($schema->properties[$propertyName])) {
                continue;
            }

            $constraints[$propertyName] = [];
            foreach ($classMetadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
                /* @var $propertyMetadata */

                $propertyConstraints = [];
                foreach ($groups as $group) {
                    $propertyConstraints = array_merge(
                        $propertyConstraints,
                        $propertyMetadata->findConstraints($group)
                    );
                }

                $finalPropertyConstraints = [];

                foreach ($propertyConstraints as $current) {
                    if (!\in_array($current, $finalPropertyConstraints)) {
                        $finalPropertyConstraints[] = $current;
                    }
                }

                $finalPropertyConstraints = array_filter(
                    $finalPropertyConstraints,
                    $this->supportConstraint(...)
                );

                $constraints[$propertyName] = [...$constraints[$propertyName], ...$finalPropertyConstraints];
            }
        }

        return array_filter($constraints);
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param \ReflectionClass      $source
     * @param Schema|QueryParameter $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $constraintExtractionContext = new ConstraintExtractionContext();

        $validationGroups = $this->getValidationGroups($extractionContext);

        if ($target instanceof QueryParameter) {
            $constraintExtractionContext->context = 'query';
            $constraints = array_filter(
                $target->constraints,
                $this->supportConstraint(...)
            );
            foreach ($constraints as $constraint) {
                $constraintExtractionContext->validationConfiguration = $target;
                $this->extractConstraint($constraint, $constraintExtractionContext);
            }

            return;
        }

        $constraintExtractionContext->classSchema = $target;
        $constraintExtractionContext->context = 'property';
        $propertyConstraints = $this->getPropertiesConstraints($source, $target, $validationGroups);

        foreach ($propertyConstraints as $propertyName => $constraints) {
            foreach ($constraints as $constraint) {
                $constraintExtractionContext->validationConfiguration = $target->properties[$propertyName];
                $constraintExtractionContext->propertyName = $propertyName;
                $this->extractConstraint($constraint, $constraintExtractionContext);
            }
        }
    }
}
