<?php

namespace Mageplus\Project\Model\ResourceModel\Project\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Model\Project;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @var string
     */
    protected $document = Project::class;

    /**
     * @var array
     */
    private $mappedFields = [];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
                      $mainTable = ProjectInterface::TABLE_NAME,
                      $resourceModel = \Mageplus\Project\Model\ResourceModel\Project::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_init(
            'Magento\Framework\View\Element\UiComponent\DataProvider\Document', 'Mageplus\Project\Model\ResourceModel\Project'
        );
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

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
//        $this->getSelect()->joinInner(
//            ['customer' => $this->getResource()->getTable('customer_entity')],
//            'customer.entity_id = main_table.super_user_id',
//            $this->mappedFields
//        );


        parent::_renderFiltersBefore();
    }
}
