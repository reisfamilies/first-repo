<?php

namespace Mageplus\Project\Model\Source\Project;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Country implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $countriesFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->countriesFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->countriesFactory->create()->toOptionArray();
    }
}
