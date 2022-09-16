<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

class SaveSlotPreference
{
    /**
     * @param ShippingInformationManagementInterface $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    public function beforeSaveAddressInformation(ShippingInformationManagementInterface $subject, $cartId, ShippingInformationInterface $addressInformation): array
    {
        $addressInformation->getShippingAddress()->setData('ponyu_slot', $addressInformation->getExtensionAttributes()->getPonyuSlot());
        return [$cartId, $addressInformation];
    }
}
