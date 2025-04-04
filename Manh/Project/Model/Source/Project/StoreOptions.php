<?php

namespace Mageplus\Project\Model\Source\Project;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * Class StoreOptions
 *
 * To use this class, add
 * <item name="options" xsi:type="object">Amasty\Base\Ui\Component\Listing\Column\StoreOptions</item>
 * to your ui field as argument
 *
 */
class StoreOptions extends Options
{
    /**
     * All Store Views value
     */
    public const ALL_STORE_VIEWS = '0';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = self::ALL_STORE_VIEWS;

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
