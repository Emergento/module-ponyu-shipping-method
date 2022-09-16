<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;

class GetCoordinateFromAddress
{

    public function __construct(
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly GetLatLngFromAddressInterface $getLatLngFromAddress
    ) {
    }

    public function execute(AddressInterface $address): LatLngInterface
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
}
