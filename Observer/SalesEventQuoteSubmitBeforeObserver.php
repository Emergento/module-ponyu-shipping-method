<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Observer;

use Emergento\PonyUShipment\Model\Service\IsPonyUOrder;
use Emergento\PonyUShippingMethod\Model\Config;
use Emergento\PonyUShippingMethod\Model\GetCoordinateFromAddress;
use Emergento\PonyUShippingMethod\Model\GetNextAvailableDeliverySlot;
use Emergento\PonyUShippingMethod\Model\IsCartSlotValid;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;

class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{

    public function __construct(
        private readonly Json $json,
        private readonly IsCartSlotValid $isCartSlotValid,
        private readonly Config $config,
        private readonly IsPonyUOrder $isPonyUOrder,
        private readonly ManagerInterface $messageManager,
        private readonly GetCoordinateFromAddress $getCoordinateFromAddress,
        private readonly GetNextAvailableDeliverySlot $getNextAvailableDeliverySlot
    ) {
    }

    /**
     * Set preferred delivery date to order from quote address
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getData('order');

        if (!$this->isPonyUOrder->execute($order)) {
            return;
        }

        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');

        $deliverySlotJsonArray = $this->json->unserialize($quote->getShippingAddress()->getPonyuSlot());

        try {
            $this->isCartSlotValid->execute($quote);
        } catch (LocalizedException | NoSuchEntityException | GuzzleException $e) {

            if ($this->config->isNextAvailableSlotEnabled((int) $quote->getStoreId())) {
                $shippingMethod = $this->extractShippingMethod($quote->getShippingAddress()->getShippingMethod());

                try {
                    $deliverySlotJsonArray = $this->getNextAvailableDeliverySlot->execute(
                        $shippingMethod,
                        $this->getCoordinateFromAddress->execute($quote->getShippingAddress()),
                        (int) $quote->getStoreId(),
                        $deliverySlotJsonArray
                    );
                    $this->messageManager->addSuccessMessage(__('Delivery time has changed'));
                } catch (GuzzleException|LocalizedException|\Exception $e) {

                }
            }
        }

        $order->setPonyuSlot($this->json->serialize($deliverySlotJsonArray));
    }

    private function extractShippingMethod(string $shippingMethod): string
    {
        return preg_replace('/^ponyu_/', '', $shippingMethod);
    }
}
