<?php

declare(strict_types=1);

namespace HbH\ProjectMainTheme\Subscriber\Product;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParentProductSubscriber implements EventSubscriberInterface
{
    public SalesChannelRepository $productRepository;

    public function __construct(SalesChannelRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SearchPageLoadedEvent::class => 'setSearchPageProductParent',
            ProductListingResultEvent::class => 'setListingPageProductParent',
            ProductPageLoadedEvent::class => 'setPdpProductParent',
        ];
    }

    public function setSearchPageProductParent(SearchPageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        $products = $event->getPage()->getListing()->getElements();

        /** @var ProductEntity $product */
        foreach ($products as $product) {
            $productParentId = $product->getParentId();
            $result = $this->buildParent($productParentId, $context);
            if ($result instanceof SalesChannelProductEntity) {
                $product->setParent($result);
            }
        }
    }

    public function setListingPageProductParent(ProductListingResultEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        $products = $event->getResult()->getElements();

        /** @var ProductEntity $product */
        foreach ($products as $product) {
            $productParentId = $product->getParentId();
            $result = $this->buildParent($productParentId, $context);
            if ($result instanceof SalesChannelProductEntity) {
                $product->setParent($result);
            }
        }
    }

    public function setPdpProductParent(ProductPageLoadedEvent $event): SalesChannelProductEntity
    {
        $context = $event->getSalesChannelContext();
        $product = $event->getPage()->getProduct();
        $productParentId = $product->getParentId();

        $result = $this->buildParent($productParentId, $context);

        if ($result instanceof SalesChannelProductEntity) {
            $product->setParent($result);
        }

        return $product;
    }

    private function buildParent(?string $productParentId, SalesChannelContext $context): ?SalesChannelProductEntity
    {
        if ($productParentId === null) {
            return null;
        }

        $result = $this->productRepository->search(new Criteria([$productParentId]), $context)->first();

        if (!$result instanceof SalesChannelProductEntity) {
            throw new ProductNotFoundException($productParentId);
        }

        return $result;
    }
}
