<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const PONYU_TIMEZONE = 'Europe/Rome';

    private const PONYU_ENDPOINT_XML = 'carriers/ponyu/endpoint';
    private const PONYU_API_KEY_XML = 'carriers/ponyu/api_key';
    private const PONYU_INSTANT_MODE = 'carriers/ponyu/instant_mode';
    private const PONYU_NEXT_DAYS = 'carriers/ponyu/max_next_days';
    private const PONYU_NEXT_AVAILABLE_SLOT = 'carriers/ponyu/next_available_slot';
    private const PONYU_STATUS_MESSAGE = [
        'REQUESTED' => 'Shipping accepted',
        'ASSIGNED' => 'Expedition assigned to a courier',
        'AT_PICKUP_SITE' => 'Courier arrived at the pickup point',
        'PROGRESS' => 'Shipping withdrawn',
        'AT_DELIVERY_SITE' => 'Courier arrived at the delivery point',
        'COMPLETED' => 'Shipment delivered',
        'CANCELLED' => 'Shipping canceled',
        'WAITING' => 'Pending shipment, we were unable to deliver, hence the shipment could return to delivery or go to return',
        'TO_RETURN' => 'Return shipping to the sender',
        'AT_RETURN_SITE' => 'Courier arrived at the sender for return',
        'RETURNED' => 'Return shipping',
    ];

    public function __construct(
        private readonly ScopeConfigInterface $config
    ){
    }

    public function getEndpoint(?int $storeId): string
    {
        return $this->config->getValue(self::PONYU_ENDPOINT_XML, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getApiKey(?int $websiteId = null): string
    {
        return $this->config->getValue(self::PONYU_API_KEY_XML, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getStoreSupportEmail(?int $storeId = null)
    {
        return $this->config->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMagentoStatusMessage(string $orderStatus): ?string
    {
        return self::PONYU_STATUS_MESSAGE[$orderStatus] ?? null;
    }

    public function isInstantModeEnabled(int $storeId = null): bool
    {
        return (bool) $this->config->getValue(self::PONYU_INSTANT_MODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNextDays(int $storeId = null): int
    {
        return (int) $this->config->getValue(self::PONYU_NEXT_DAYS, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isNextAvailableSlotEnabled(?int $storeId): bool
    {
        return (bool) $this->config->getValue(self::PONYU_NEXT_AVAILABLE_SLOT, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
