<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model\Carrier;

use Emergento\PonyU\Model\GetZonesByLatitudeLongitude;
use Emergento\PonyUShippingMethod\Model\GetPriceByPonyUShippingCode;
use Emergento\PonyUShippingMethod\Model\GetShippingMethods;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class PonyU extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'ponyu';

    public function __construct(
        private readonly GetLatLngFromAddressInterface $getLatLngFromAddress,
        private readonly GetZonesByLatitudeLongitude $getZonesByLatitudeLongitude,
        private readonly ExtractAddressFromRateRequest $extractAddressFromRateRequest,
        private readonly ResultFactory $rateResultFactory,
        private readonly MethodFactory $rateMethodFactory,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        private readonly GetShippingMethods $getShippingMethods,
        private readonly GetPriceByPonyUShippingCode $getPriceByPonyUShippingCode,
        private readonly ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request): DataObject|Result|bool|null
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->canRequestBeServed($request)) {
            return false;
        }

        $result = $this->rateResultFactory->create();

        foreach ($this->getShippingMethods->execute(
            $this->getReceiverCoordinate($request),
            $request->getStoreId()
        ) as $shippingMethod) {
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setMethod($shippingMethod['type']);
            $method->setMethodTitle($this->extractLocalizedAttribute('name', $shippingMethod, $request->getStoreId()));
            $method->setCarrierTitle($this->extractLocalizedAttribute('description', $shippingMethod, $request->getStoreId()));
            $method->setPrice($this->getPriceByPonyUShippingCode->execute($shippingMethod['type'], $request->getStoreId()));
            $method->setCost($this->getPriceByPonyUShippingCode->execute($shippingMethod['type'], $request->getStoreId()));
            $result->append($method);
        }
        return $result;
    }

    public function getAllowedMethods(): array
    {
        return [$this->_code, $this->getConfigData('name')];
    }

    /**
     * @throws GuzzleException
     */
    private function canRequestBeServed(RateRequest $request): bool
    {
        $coordinates = $this->getReceiverCoordinate($request);
        return (bool) !empty($this->getZonesByLatitudeLongitude->execute($coordinates->getLat(), $coordinates->getLng(), $request->getStoreId()));
    }

    /**
     * @throws LocalizedException
     */
    private function extractLocalizedAttribute(string $attribute, array $shippingMethod, int $storeId): string
    {
        $local = $this->localeResolver->emulate($storeId);
        foreach ($shippingMethod[$attribute] as $shippingMethodNameLocalized) {
            if ($shippingMethodNameLocalized['locale'] === $local) {
                return $shippingMethodNameLocalized['value'];
            }
        }
        throw new LocalizedException(__('No language available'));
    }

    private function getReceiverCoordinate(RateRequest $request): LatLngInterface
    {
        $address = $this->extractAddressFromRateRequest->execute($request);
        return $this->getLatLngFromAddress->execute($address);
    }
}
