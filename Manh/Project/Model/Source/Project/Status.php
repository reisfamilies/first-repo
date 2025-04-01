<?php

namespace Mageplus\Project\Model\Source\Project;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * @param int $status
     * @return string
     */
    public function getStatusLabelByValue($status)
    {
        $options = $this->toOptionArray();
        foreach ($options as $optionStatus) {
            if ($optionStatus['value'] == $status) {
                return $optionStatus['label'];
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::STATUS_INACTIVE,
                'label' => __('Inactive')
            ],
            [
                'value' => self::STATUS_ACTIVE,
                'label' => __('Active')
            ]
        ];
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
