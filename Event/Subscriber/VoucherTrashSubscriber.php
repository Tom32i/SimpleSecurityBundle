<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Event\Subscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Tom32i\Bundle\SimpleSecurityBundle\Service\VoucherManager;

/**
 * Trash out dated vouchers on kernel.terminate
 */
class VoucherTrashSubscriber implements EventSubscriberInterface
{
    /**
     * Voucher manager
     *
     * @var VoucherManager
     */
    private $manager;

    /**
     * Voucher manager
     *
     * @param VoucherManager $manager
     */
    public function __construct(VoucherManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.terminate' => 'onKernelTerminate',
        ];
    }
    /**
     * On kernel terminate end
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->manager->clearTrash();
    }
}
