<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trash expired vouchers command
 */
class TrashExpiredVouchersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tom32i_simple_security:vouchers:trash_expired')
            ->setDescription('Trash expired vouchers')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $length = $this->getManager()->trashExpiredVouchers()->getTrashLength();

        $this->getManager()->clearTrash();

        $output->writeln(sprintf('Success: <info>%s</info> expired voucher(s) deleted.', $length));
    }

    /**
     * Get voucher manager
     *
     * @return Tom32i\Bundle\SimpleSecurityBundle\Service\VoucherManager
     */
    private function getManager()
    {
        return $this->getContainer()->get('tom32i_simple_security.manager.voucher');
    }
}
