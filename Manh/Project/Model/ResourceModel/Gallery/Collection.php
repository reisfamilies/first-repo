<?php

namespace Mageplus\Project\Model\ResourceModel\Gallery;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplus\Project\Model\Gallery as Model;
use Mageplus\Project\Model\ResourceModel\Gallery as ResourceModel;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_gallery_collection';

    /**
     * @var CollectionFactory
     */
    private $factory;

    /**
     * Collection Constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param CollectionFactory $factory
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        CollectionFactory      $factory,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->factory = $factory;
    }

    public function getImagesByProjectId($projectId)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Model[] $images */
        $images = $imagesCollection->addFieldToFilter('project_id', $projectId)->getItems();

        return $images;
    }

    public function getByNameAndProjectId($projectId, $name)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Model $image */
        $image = $imagesCollection
            ->addFieldToFilter('project_id', $projectId)
            ->addFieldToFilter('image_name', $name)
            ->getFirstItem();

        return $image;
    }

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
