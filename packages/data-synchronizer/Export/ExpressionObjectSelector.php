<?php

namespace Draw\Component\DataSynchronizer\Export;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadata;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(ObjectSelectorInterface::class)]
class ExpressionObjectSelector implements ObjectSelectorInterface
{
    public function __construct(
        private ObjectSelectorInterface $decorated,
        private ManagerRegistry $managerRegistry,
        private ?ExpressionLanguage $expressionLanguage = null,
    ) {
        if (class_exists(ExpressionLanguage::class)) {
            $this->expressionLanguage ??= new ExpressionLanguage();
        }
    }

    public function select(EntitySynchronizationMetadata $extractionMetadata): ?array
    {
        if (!$extractionMetadata->exportExpression) {
            return $this->decorated->select($extractionMetadata);
        }

        if (null === $this->expressionLanguage) {
            throw new \LogicException('Expression language is not available. Please install [symfony/expression-language] component.');
        }

        return $this->expressionLanguage->evaluate(
            $extractionMetadata->exportExpression,
            [
                'repository' => $this->managerRegistry->getRepository($extractionMetadata->classMetadata->name),
            ]
        );
    }
}
