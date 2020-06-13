<?php namespace Draw\Component\OpenApi\Serializer\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Draw\Component\OpenApi\Doctrine\ArrayCollectionMutationTracker;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

class ArrayCollectionHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];
        $collectionTypes = [
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection',
        ];

        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeCollection',
                ];
            }
        }

        return $methods;
    }

    /**
     * @param mixed $data
     */
    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context): ArrayCollection
    {
        // See above.
        $type['name'] = 'array';
        $collection = new ArrayCollectionMutationTracker($visitor->visitArray($data, $type));

        foreach($data as $key => $value) {
            if(isset($value['_drawOpenApiRemove']) && $value['_drawOpenApiRemove']) {
                $collection->remove($key);
            }
        }

        return $collection;
    }
}