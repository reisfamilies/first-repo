<?php

namespace Mageplus\Project\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplus\Project\Api\Data\ProjectInterface;

class Project extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_resource_model';

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @var Product
     */
    private $tagResource;

    public function __construct(
        Context    $context,
        Product    $productResource,
        ProjectTag $tagResource,
                   $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->productResource = $productResource;
        $this->tagResource = $tagResource;
    }

    public function getProjectProductIds(ProjectInterface $project): array
    {
        return $this->productResource->getProductIdsByProjectId($project->getProjectId());
    }

    public function getProjectTagIds(ProjectInterface $project): array
    {
        return $this->tagResource->getTagIdsByProjectId($project->getProjectId());
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mageplus_project', ProjectInterface::PROJECT_ID);
        $this->_useIsObjectNew = true;
    }
}
