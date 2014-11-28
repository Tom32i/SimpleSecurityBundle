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
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Voucher manager
     *
     * @param VoucherManager $manager
     */
    public function __construct(VoucherManager $manager, LoggerInterface $logger = null)
    {
        $this->manager = $manager;
        $this->logger  = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.terminate' => [
                ['onKernelTerminateStart', 1],
                ['onKernelTerminateEnd', 0],
            ],
        ];
    }

    /**
     * On kernel terminate start
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminateStart(PostResponseEvent $event)
    {
        $this->log('onKernelTerminateStart');

        //try {
            $this->manager->trashExpiredVouchers();
        /*} catch (\Exception $e) {
            $this->log($e, 'error');
        }*/
    }

    /**
     * On kernel terminate end
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminateEnd(PostResponseEvent $event)
    {
        $this->log('onKernelTerminateEnd');
        try {
            $this->manager->clearTrash();
        } catch (\Exception $e) {
            $this->log($e, 'error');
        }
    }

    /**
     * Log a message
     *
     * @param string $message
     */
    private function log($message, $method = 'info')
    {
        if ($this->logger) {
            $this->logger->$method((string) $message);
        }
    }
}
