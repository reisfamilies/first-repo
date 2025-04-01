<?php

namespace Mageplus\Project\Model\ResourceModel\Product\Grid;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    public const PRODUCT_ENTITY_TABLE = 'catalog_product_entity';

    /**
     * @var array
     */
    private $mappedFields = [

    ];

    private $eavAttribute;

    public function __construct(
        EntityFactory $entityFactory,
        Logger        $logger,
        FetchStrategy $fetchStrategy,
        EventManager  $eventManager,
        EavAttribute  $eavAttribute,
                      $mainTable = self::PRODUCT_ENTITY_TABLE,
                      $resourceModel = Product::class,
                      $identifierName = null,
                      $connectionName = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @return $this
     */
    public function addNameAttributeToSelect()
    {
        $productNameAttributeId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
        $catalogVarcharTable = $this->getResource()->getTable('catalog_product_entity_varchar');

        $this->getSelect()->joinLeft(
            ['cpev' => $catalogVarcharTable],
            'cpev.entity_id = main_table.entity_id',
            ['name' => 'cpev.value']
        )->where('cpev.attribute_id = ?', $productNameAttributeId);

        return $this;
    }

    /**
     * @param $productIds
     * @return $this
     */
    public function addProductIdFilter($productIds = [])
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $this->getSelect()->where('main_table.entity_id in (?)', $productIds);

        return $this;
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::addOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::setOrder($field, $direction);
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
