<?php

namespace Mageplus\Project\Model\ResourceModel\Project;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplus\Project\Model\Project as Model;
use Mageplus\Project\Model\ResourceModel\Gallery;
use Mageplus\Project\Model\ResourceModel\Project as ResourceModel;
use Psr\Log\LoggerInterface;
use Zend_Db_Select_Exception;


class Collection extends AbstractCollection
{
    public const IS_NEED_TO_COLLECT_PROJECT_DATA = 'is_need_to_collect_project_data';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_collection';

    private $storeManager;

    private $request;

    private $coreRegistry;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        StoreManagerInterface  $storeManager,
        RequestInterface       $request,
        Registry               $coreRegistry,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Apply filters to projects collection
     *
     * @throws NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }

        $select->where('main_table.status = 1');
        $select->order(sprintf('main_table.position %s', Select::SQL_ASC));
        $select->order(sprintf('main_table.project_id %s', Select::SQL_ASC));
    }

    /**
     * @param array $storeIds
     * @return Select
     */
    public function addFilterByStores($storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `main_table`.`stores`)';
        }

        $where = implode(' OR ', $where);

        return $this->getSelect()->where($where);
    }

    /**
     * Join schedule table
     *
     * @return $this
     * @throws Zend_Db_Select_Exception
     */
    public function joinMainImage()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['img'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['img' => $this->getTable(Gallery::TABLE_NAME)],
            'main_table.project_id = img.project_id AND img.is_base = 1',
            ['main_image_name' => 'img.image_name']
        );

        return $this;
    }

    public function applyRegionFilter($region)
    {
        $this->getSelect()->where('main_table.region = ?', $region);

        return $this;
    }

    public function applyTextFilter($text)
    {
        $textFilter = '%' . $text . '%';
        $this->getSelect()->where("main_table.region LIKE ? or main_table.project_name LIKE ?", $textFilter, $textFilter);

        return $this;
    }

    public function applyProjectsFilter($projectIds)
    {
        $this->getSelect()->where('main_table.project_id in (?)', $projectIds);

        return $this;
    }

    public function getIdsOnPage(): array
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
