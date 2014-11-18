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
     * Find non expired voucher
     *
     * @param array $criteria
     * @param array|null $orderBy
     *
     * @return Voucher|null The entity instance or NULL if the entity can not be found.
     */
    public function findNonExpired(array $criteria, array $orderBy = null)
    {
        return $this->findOneBy(
            array_merge(
                $criteria,
                [Criteria::expr()->gt('expiration', new DateTime)]
            ),
            $orderBy
        );
    }
}
