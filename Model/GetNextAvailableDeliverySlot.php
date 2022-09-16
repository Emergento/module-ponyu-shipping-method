<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Emergento\PonyUShipment\Model\Service\GetSlotsByShippingMethodCode;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;

class GetNextAvailableDeliverySlot
{

    public function __construct(
        private readonly GetSlotsByShippingMethodCode $getSlotsByShippingMethodCode,
        private readonly GenerateSlotLabel $generateSlotLabel
    ) {
    }

    /**
     * @throws GuzzleException|LocalizedException|\Exception
     */
    public function execute(string $shippingMethod, LatLngInterface $coordinates, int $getStoreId, array $currentSlot)
    {
        $ponyUTimeZone = new \DateTimeZone(Config::PONYU_TIMEZONE);

        $slots = $this->getSlotsByShippingMethodCode->execute(
            $shippingMethod,
            $coordinates,
            '',
            $getStoreId
        );

        foreach ($slots as $slot) {
            if (strtotime($slot['pickupDate']) > strtotime($currentSlot['pickupDate'])){
                $slot['label'] = $this->generateSlotLabel->execute(
                    new \DateTime($slot['deliveryDateStart'], $ponyUTimeZone),
                    new \DateTime($slot['deliveryDateEnd'], $ponyUTimeZone)
                );

                return $slot;
            }
        }

        throw new LocalizedException(__('Cannot find a better slot'));
    }
}
