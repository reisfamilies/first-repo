<?php

namespace Mageplus\Project\Model\ResourceModel\Tag\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Model\Tag;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @var string
     */
    protected $document = Tag::class;

    /**
     * @var array
     */
    private $mappedFields = [];

    public function __construct(
        EntityFactory $entityFactory,
        Logger        $logger,
        FetchStrategy $fetchStrategy,
        EventManager  $eventManager,
                      $mainTable = TagInterface::TABLE_NAME,
                      $resourceModel = \Mageplus\Project\Model\ResourceModel\Tag::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
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

    public function addTagIdFilter($tagIds = [])
    {
        if (!is_array($tagIds)) {
            $tagIds = [$tagIds];
        }

        $this->getSelect()->where('main_table.tag_id in (?)', $tagIds);

        return $this;
    }
}
