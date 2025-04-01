<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplus\Project\Api\Data\ProductInterface;

class Product extends AbstractExtensibleModel implements ProductInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Product::class);
    }

    /**
     * Getter for ProductId.
     *
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID) === null ? null
            : (int)$this->getData(self::PRODUCT_ID);
    }

    /**
     * Setter for ProductId.
     *
     * @param int|null $productId
     *
     * @return void
     */
    public function setProductId(?int $productId): void
    {
        $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Getter for ProjectId.
     *
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->getData(self::PROJECT_ID) === null ? null
            : (int)$this->getData(self::PROJECT_ID);
    }

    /**
     * Setter for ProjectId.
     *
     * @param int|null $projectId
     *
     * @return void
     */
    public function setProjectId(?int $projectId): void
    {
        $this->setData(self::PROJECT_ID, $projectId);
    }
}
