<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Emergento\PonyUShipment\Model\IsDeliverySlotValid;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Determine is delivery slot is still valid or not
 */
class IsCartSlotValid
{

    public function __construct(
        private readonly  AddressInterfaceFactory $addressFactory,
        private readonly GetLatLngFromAddressInterface $getLatLngFromAddress,
        private readonly Json $json,
        private readonly IsDeliverySlotValid $isDeliverySlotValid
    ) {
    }

    /**
     * @param CartInterface $quote
     * @return void
     * @throws NoSuchEntityException|GuzzleException|LocalizedException
     */
    public function execute(CartInterface $quote): void
    {
        if (!$this->isPonyUShipping($quote)) {
            return;
        }

        $selectedDeliverySlotJson = $quote->getShippingAddress()->getPonyuSlot();

        if (!$selectedDeliverySlotJson) {
            throw new LocalizedException(_('Delivery slot not found'));
        }

        $shippingMethod = $this->extractShippingMethod($quote->getShippingAddress()->getShippingMethod());

        $selectedDeliverySlot = $this->json->unserialize($selectedDeliverySlotJson);

        if ($this->isDeliverySlotValid->execute($shippingMethod, $this->getReceiverCoordinate($quote->getShippingAddress()), $selectedDeliverySlot, (int) $quote->getStoreId())) {
            return;
        }

        throw new LocalizedException(__('Slot is not valid'));
    }

    private function getReceiverCoordinate(AddressInterface $address): LatLngInterface
    {
        $addressToInventorySourceSelectionAddress = $this->addressFactory->create([
            'street' => $address->getStreetLine(1) ?? '',
            'country' => $address->getCountryId() ?? '',
            'city' => $address->getCity() ?? '',
            'region' => $address->getRegion() ?? '',
            'postcode' => $address->getPostcode() ?? '',
        ]);
        return $this->getLatLngFromAddress->execute($addressToInventorySourceSelectionAddress);
    }

    private function extractShippingMethod(string $getShippingMethod): string
    {
        return str_replace('ponyu_', '', $getShippingMethod);
    }

    private function isPonyUShipping(CartInterface $quote): bool
    {
        return str_starts_with($quote->getShippingAddress()->getShippingMethod(), 'ponyu_');
    }
}
