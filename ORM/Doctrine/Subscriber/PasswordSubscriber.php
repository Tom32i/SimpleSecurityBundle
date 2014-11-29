<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\ORM\Doctrine\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\SafePasswordInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Service\Authenticator;

/**
 * Password Subscriber
 */
class PasswordSubscriber implements EventSubscriber
{
    /**
     * Authenticator
     *
     * @var Authenticator
     */
    protected $authenticator;

    /**
     * Constructor
     *
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [ Events::prePersist, Events::preUpdate ];
    }

    /**
     * prePersist Doctrine Event
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->onPersist($args->getEntity());
    }

    /**
     * preUpdate Doctrine Event
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->onPersist($args->getEntity(), $args->getEntityManager());
    }

    /**
     * On entity persist
     *
     * @param mixed $entity
     * @param EntityManager $objectManager
     */
    protected function onPersist($entity, ObjectManager $objectManager = null)
    {
        if ($entity instanceof SafePasswordInterface) {
            $this->authenticator->encodePassword($entity);

            if ($objectManager) {
                $this->recomputeChanges($objectManager, $entity);
            }
        }
    }

    /**
     * Recompute change set on the given entity
     *
     * @param EntityManager $objectManager
     * @param mixed $entity
     */
    protected function recomputeChanges(ObjectManager $objectManager, $entity)
    {
        $uow  = $objectManager->getUnitOfWork();
        $meta = $objectManager->getClassMetadata(get_class($entity));
        $uow->recomputeSingleEntityChangeSet($meta, $entity);
    }
}
