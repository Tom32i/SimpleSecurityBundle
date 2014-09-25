<?php

namespace Tom32i\SimpleSecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * User Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * Find users by id
     *
     * @param array $ids
     *
     * @return ArrayCollection
     */
    public function findByIds(array $ids)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}