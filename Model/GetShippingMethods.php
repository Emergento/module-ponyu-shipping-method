<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Emergento\PonyU\Model\GetDeliverySlots;
use GuzzleHttp\Exception\GuzzleException;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;

/**
 * Retrieve available PonyU shipping methods
 * @api
 */
class GetShippingMethods
{

    public function __construct(
        private readonly GetDeliverySlots $getDeliverySlots,
        private readonly GetSenderCoordinates $getSenderCoordinate
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function execute(LatLngInterface $receiverCoordinate, ?int $storeId)
    {
        $senderCoordinate = $this->getSenderCoordinate->execute($storeId);

        return $this->getDeliverySlots->execute(
            $senderCoordinate->getLat(),
            $senderCoordinate->getLng(),
            $receiverCoordinate->getLat(),
            $receiverCoordinate->getLng(),
            '',
            1,
            $storeId
        );
    }
}
