<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher;

/**
 * Voucher manager
 */
class VoucherManager
{
    /**
     * Entity Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Expired Voucher to be deleted
     *
     * @var array
     */
    protected $trash;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param ValidatorInterface $validator
     */
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator)
    {
        $this->objectManager = $objectManager;
        $this->validator     = $validator;
        $this->trash         = [];
    }

    /**
     * Create a new Voucher
     *
     * @param UserInterface $user
     * @param string $type
     * @param string $ttl
     *
     * @return Voucher
     */
    public function create(UserInterface $user, $type, $ttl = '+ 5 minutes')
    {
        return new Voucher($user, $type, $ttl);
    }

    /**
     * Use voucher and then trash it
     *
     * @param Voucher $voucher
     *
     * @return UserInterface
     */
    public function activate(Voucher $voucher)
    {
        $this->trash[] = $voucher;

        return $voucher->getUser();
    }

    /**
     * Collect expired vouchers and add them to trash
     */
    public function trashExpiredVouchers()
    {
        $this->trash = array_merge($this->trash, $this->getRepository()->findAllExpired());
    }

    /**
     * Clear trash
     */
    public function clearTrash()
    {
        foreach ($this->trash as $voucher) {
            $this->objectManager->delete($voucher);
        }

        $this->trash = [];
    }

    /**
     * Get User repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->objectManager->getRepository('Tom32iSimpleSecurityBundle:Voucher');
    }
}
