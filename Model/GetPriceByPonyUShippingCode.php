<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Retrieve shipping price from configuration using shipping method code
 */
class GetPriceByPonyUShippingCode
{
    private const PONYU_SHIPPING_METHOD_PRICES = 'carriers/ponyu/prices';
    private const PONYU_SHIPPING_DEFAULT_PRICE = 'carriers/ponyu/default_price';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $json
    ) {
    }

    public function execute(string $code, ?int $storeId): int
    {
        $prices =  $this->scopeConfig->getValue(self::PONYU_SHIPPING_METHOD_PRICES, ScopeInterface::SCOPE_STORE, $storeId);

        if (!is_array($prices)) {
            $prices = $this->json->unserialize($prices);
        }

        foreach ($prices ?? [] as $price) {
            if ($price['method_code'] === $code) {
                return (int) $price['price'];
            }
        }

        return (int) $this->scopeConfig->getValue(self::PONYU_SHIPPING_DEFAULT_PRICE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
