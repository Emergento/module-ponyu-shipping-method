<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Retrieve Source Latitude and Longitude by the storeId
 * @api
 */
class GetSenderCoordinates
{
    public function __construct(
        private readonly StockResolverInterface $stockResolver,
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        private readonly LatLngInterfaceFactory $latLngInterfaceFactory
    ) {
    }

    public function execute($storeId): LatLngInterface
    {
        $source = $this->getFirstInventorySourceByStoreId($storeId);
        return $this->latLngInterfaceFactory->create([
                'lat' => $source->getLatitude(),
                'lng' => $source->getLongitude(),
        ]);
    }

    /**
     * Get the first inventory source by priority
     *
     * @throws InputException|LocalizedException|NoSuchEntityException
     */
    private function getFirstInventorySourceByStoreId(?int $storeId): SourceInterface
    {
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $this->storeRepository->getById($storeId)->getWebsite()->getCode());
        $searchResults =  $this->getSourcesAssignedToStockOrderedByPriority->execute($stock->getStockId());
        if (count($searchResults) === 0) {
            throw new NoSuchEntityException(__('No inventory for this store id: %1', $storeId));
        }
        return $searchResults[0];
    }
}
