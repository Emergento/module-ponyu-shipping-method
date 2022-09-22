<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;

class GenerateSlotLabel
{

    public function __construct(private readonly ResolverInterface $localeResolver)
    {
    }

    public function execute(\DateTime $slotStartDate, \DateTime $slotEndDate): Phrase
    {
        $formatter = new \IntlDateFormatter($this->localeResolver->getLocale(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $formatter->setPattern("EEEE, dd LLLL");
        $formatter->setTimeZone(Config::PONYU_TIMEZONE);
        return __('%1 from %2 to %3', $formatter->format($slotStartDate), $slotStartDate->format('H:i'), $slotEndDate->format('H:i'));
    }
}
