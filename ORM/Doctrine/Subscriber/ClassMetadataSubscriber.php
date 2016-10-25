<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\ORM\Doctrine\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class Metadata Subscriber
 */
class ClassMetadataSubscriber implements EventSubscriber
{
    /**
     * User classname
     *
     * @var string
     */
    private $userClassname;

    /**
     * Constructor
     *
     * @param string $userClassname
     */
    public function __construct($userClassname)
    {
        $this->userClassname = $userClassname;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [ Events::loadClassMetadata ];
    }

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if ('Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher' === $metadata->getName()) {
            $this->overrideTargetEntity($metadata, 'user', $this->userClassname);
        }
    }

    /**
     * Override target entity
     *
     * @param ClassMetadata $metadata
     * @param string $field
     * @param string $className
     */
    protected function overrideTargetEntity(ClassMetadata $metadata, $field, $className)
    {
        $mapping = $metadata->getAssociationMapping($field);

        $mapping['targetEntity'] = $className;

        unset($metadata->associationMappings[$mapping['fieldName']]);

        switch ($mapping['type']) {
            case ClassMetadata::MANY_TO_MANY:
                $metadata->mapManyToMany($mapping);
                break;

            case ClassMetadata::MANY_TO_ONE:
                $metadata->mapManyToOne($mapping);
                break;

            case ClassMetadata::ONE_TO_MANY:
                $metadata->mapOneToMany($mapping);
                break;

            case ClassMetadata::ONE_TO_ONE:
                $metadata->mapOneToOne($mapping);
                break;
        }
    }
}
