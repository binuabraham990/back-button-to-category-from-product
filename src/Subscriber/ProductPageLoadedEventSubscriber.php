<?php

namespace NtfxBackFromProductPage\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use NtfxBackFromProductPage\Struct\BackUrlStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductPageLoadedEventSubscriber implements EventSubscriberInterface {

    private EntityRepository $categoryRepository;
    private SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler;

    public function __construct(
            EntityRepository $categoryRepository,
            SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoadedEvent',
        ];
    }

    public function onProductPageLoadedEvent(ProductPageLoadedEvent $event) {

        $previousLink = $event->getRequest()->server->get('HTTP_REFERER');
        $siteUrl = $event->getSalesChannelContext()->getSalesChannel()->getDomains()->first()->getUrl();
        $redirectBackUrl = '';

        // Check if from same site. If not return
        if (!str_contains($previousLink, $siteUrl)) {
            return;
        }

        // If there is no categories associated with product return 
        $categoryIds = $event->getPage()->getProduct()->getCategoryIds();
        if (count($categoryIds) <= 0) {
            return;
        }

        $host = $event->getRequest()->attributes->get(RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL)
                . $event->getRequest()->attributes->get(RequestTransformer::SALES_CHANNEL_BASE_URL);

        foreach ($categoryIds as $index => $categoryId) {

            $navigationUrl = $this->seoUrlPlaceholderHandler->replace(
                    $this->seoUrlPlaceholderHandler->generate(
                            'frontend.navigation.page',
                            ['navigationId' => $categoryId]
                    ),
                    $host,
                    $event->getSalesChannelContext()
            );
            if ($index === 0) {
                $redirectBackUrl = $navigationUrl;
            }

            if ($navigationUrl === $previousLink) {
                $redirectBackUrl = $navigationUrl;
            }
        }

        $backUrlStruct = new BackUrlStruct($redirectBackUrl);
        $event->getSalesChannelContext()->addExtensions(['back-url' => $backUrlStruct]);
    }
}
