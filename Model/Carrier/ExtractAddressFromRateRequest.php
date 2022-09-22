<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model\Carrier;

use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;

/**
 * Extract Address object from Rate request
 *
 * @api
 */
class ExtractAddressFromRateRequest
{
    public function __construct(
        private readonly AddressInterfaceFactory $addressFactory
    ) {
    }

    public function execute(RateRequest $request): AddressInterface
    {
        return $this->addressFactory->create([
            'street' => $request->getDestStreet(),
            'postcode' => $request->getDestPostcode() ?? '',
            'city' => $request->getDestCity() ?? '',
            'country' => $request->getDestCountryId(),
            'region' => $request->getDestRegionCode() ?? ''
        ]);
    }
}
