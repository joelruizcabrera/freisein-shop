<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Command;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\SendMailAction;
use Shopware\Core\Content\Flow\Dispatching\FlowFactory;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'etagen:resend-order-confirmation-mail',
    description: 'Resend the order confirmation mail of the given orderId.',
)]
class ResendOrderConfirmationMailCommand extends Command
{
    public function __construct(
        private readonly SendMailAction $sendMailAction,
        private readonly OrderConverter $orderConverter,
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $mailTemplateRepository,
        private readonly FlowFactory $flowFactory
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addArgument('orderId', InputArgument::REQUIRED)
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'If specified, the e-mail is sent to this recipient instead of the actual recipient.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        // See: shopware/core/Checkout/Cart/SalesChannel/CartOrderRoute.php
        $criteria = new Criteria([strtolower($input->getArgument('orderId'))]);
        $criteria
            ->addAssociation('orderCustomer.customer')
            ->addAssociation('orderCustomer.salutation')
            ->addAssociation('deliveries.shippingMethod')
            ->addAssociation('deliveries.shippingOrderAddress.country')
            ->addAssociation('deliveries.shippingOrderAddress.countryState')
            ->addAssociation('transactions.paymentMethod')
            ->addAssociation('lineItems.cover')
            ->addAssociation('lineItems.downloads.media')
            ->addAssociation('currency')
            ->addAssociation('addresses.country')
            ->addAssociation('addresses.countryState')
            ->addAssociation('stateMachineState')
            ->addAssociation('deliveries.stateMachineState')
            ->addAssociation('transactions.stateMachineState')
            ->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));

        /** @var OrderEntity $order */
        $order = $this->orderRepository->search($criteria, $context)->first();

        $criteriaMailTemplate = new Criteria();
        $criteriaMailTemplate->addFilter(new EqualsFilter('mailTemplateType.technicalName', 'order_confirmation_mail'));

        /** @var MailTemplateEntity $mailTemplate */
        $mailTemplate = $this->mailTemplateRepository->search($criteriaMailTemplate, $context)->first();

        $salesChannelContext = $this->orderConverter->assembleSalesChannelContext($order, $context)->getContext();
        $event = new CheckoutOrderPlacedEvent($salesChannelContext, $order, $order->getSalesChannelId());

        $config = [
            'mailTemplateId' => $mailTemplate->getId(),
        ];

        $to = $input->getOption('to');
        if (null !== $to) {
            $config['recipient'] = [
                'data' => [
                    $to => $to,
                ],
                'type' => 'custom',
            ];
        } else {
            $config['recipient'] = [
                'data' => [],
                'type' => 'default',
            ];
        }

        // @see Shopware\Core\Content\Flow\Dispatching\FlowDispatcher
        // @see Shopware\Core\Content\Flow\Dispatching\FlowExecutor
        $storableFlow = $this->flowFactory->create($event);
        $storableFlow->setConfig($config);
        $this->sendMailAction->handleFlow($storableFlow);

        $output->writeln('Mail has been sent');

        return Command::SUCCESS;
    }
}
