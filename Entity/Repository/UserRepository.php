<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * User repository
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this
            ->createQueryBuilder('user')
            ->where('user.username = :username OR user.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
