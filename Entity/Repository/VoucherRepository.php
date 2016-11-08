<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Entity\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Common\Collections\Criteria;

/**
 * Voucher Repository
 */
class VoucherRepository extends EntityRepository
{
    /**
     * Find non expired voucher by its token
     *
     * @param string $token
     *
     * @return Voucher|null The entity instance or NULL if the entity can not be found.
     */
    public function findNonExpiredByToken($token)
    {
        return $this->createQueryBuilder('voucher')
            ->where('voucher.token <= :token')
            ->andWhere('voucher.expiration > :date')
            ->setParameter('token', $token)
            ->setParameter('date', new DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Find all expired vouchers
     *
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return array
     */
    public function findAllExpired()
    {
        return $this->createQueryBuilder('voucher')
            ->where('voucher.expiration <= :date')
            ->setParameter('date', new DateTime())
            ->getQuery()
            ->getResult();
    }
}
