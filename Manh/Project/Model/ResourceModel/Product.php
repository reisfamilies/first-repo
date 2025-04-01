<?php

namespace Mageplus\Project\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplus\Project\Api\Data\ProductInterface;

class Product extends AbstractDb
{
    public const CATALOG_PRODUCT_ENTITY_TABLE = 'catalog_product_entity';

    public function getProductIdsByProjectId($projectId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], ['product_id'])
            ->where('main_table.project_id = ?', $projectId);

        return $this->getConnection()->fetchCol($select);
    }

    public function getProjectIdsByProductId($productIds)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], ['project_id'])
            ->where('main_table.product_id in (?)', $productIds);

        return $this->getConnection()->fetchCol($select);
    }

    public function unassignProject($productIds = [])
    {
        if (!empty($productIds)) {
            $this->getConnection()->update(
                $this->getTable(ProductInterface::TABLE_NAME),
                [ProductInterface::PROJECT_ID => null],
                ['product_id in (?)' => $productIds]
            );
        }

        return $this;
    }

    public function assignProject($projectId, $productIds = [])
    {
        $insertData = [];
        foreach ($productIds as $productId) {
            $data = [
                ProductInterface::PRODUCT_ID => $productId,
                ProductInterface::PROJECT_ID => $projectId,
            ];
            $insertData[] = $data;
        }

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $insertData);

        return $this;
    }

    public function removeProjectProduct($projectId)
    {
        if (!empty($projectId)) {
            $this->getConnection()->delete($this->getMainTable(), ['project_id = ?' => $projectId]);
        }

        return $this;
    }

    protected function _construct()
    {
        $this->_init(ProductInterface::TABLE_NAME, ProductInterface::PRODUCT_ID);
    }
}
