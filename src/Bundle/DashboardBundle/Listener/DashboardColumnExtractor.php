<?php namespace Draw\Bundle\DashboardBundle\Listener;

use Doctrine\Common\Annotations\Reader;
use Draw\Bundle\DashboardBundle\Annotations\Column;
use Draw\Bundle\DashboardBundle\Annotations\FormInput;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event\PropertyExtractedEvent;
use Draw\Component\OpenApi\Schema\Vendor;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class DashboardColumnExtractor implements EventSubscriberInterface
{
    private $annotationReader;

    public static function getSubscribedEvents()
    {
        return [
            PropertyExtractedEvent::class => 'addColumnInformation'
        ];
    }


    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function addColumnInformation(PropertyExtractedEvent $propertyExtractedEvent)
    {
        $propertyMetadata = $propertyExtractedEvent->getPropertyMetadata();

        if($propertyMetadata instanceof VirtualPropertyMetadata) {
            $reflection = new \ReflectionMethod($propertyMetadata->class, $propertyMetadata->getter);
            $annotations = $this->annotationReader->getMethodAnnotations($reflection);
        } else {
            $reflection = new \ReflectionProperty($propertyMetadata->class, $propertyMetadata->name);
            $annotations = $this->annotationReader->getPropertyAnnotations($reflection);
        }

        foreach($annotations as $annotation) {
            if(!$annotation instanceof Vendor) {
                continue;
            }

            if(!$annotation->id) {
                $annotation->id = $propertyMetadata->serializedName;
            }

            $propertyExtractedEvent->getSchema()->vendor[$annotation->name] = $annotation->jsonSerialize();
        }
    }
}