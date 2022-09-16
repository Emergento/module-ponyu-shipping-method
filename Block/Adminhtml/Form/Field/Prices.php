<?php
declare(strict_types=1);

namespace Emergento\PonyUShippingMethod\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Prices
 */
class Prices extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('method_code', ['label' => __('Shipping Method Code'), 'class' => 'required-entry']);
        $this->addColumn('price', ['label' => __('Price'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
