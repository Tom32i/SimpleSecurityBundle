<?php

namespace Tom32i\SimpleSecurityBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Tom32i\SimpleSecurityBundle\Interfaces\SafePasswordInterface;

/**
 * Doctrine Listener Class
 */
class DoctrineListener
{
    /**
     * Encoder Factory
     *
     * @var EncoderFactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param EncoderFactoryInterface $factory
     */
    public function __construct(EncoderFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        if ($entity instanceof AdvancedUserInterface && $entity instanceof SafePasswordInterface) {
            $this->encodePassword($entity);
            $entity->eraseCredentials();

            if ($objectManager) {
                $this->recomputeChanges($objectManager, $entity);
            }
        }
    }

    /**
     * Encode User password
     *
     * @param SafePasswordInterface $user The User
     */
    protected function encodePassword(SafePasswordInterface $user)
    {
        $plain = $user->getPlainPassword();

        if (!empty($plain)) {
            $encoder  = $this->factory->getEncoder($user);
            $password = $encoder->encodePassword($plain, $user->getSalt());

            $user->setPassword($password);
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