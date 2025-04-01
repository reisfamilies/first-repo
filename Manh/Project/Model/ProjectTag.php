<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplus\Project\Api\Data\ProjectTagInterface;

class ProjectTag extends AbstractExtensibleModel implements ProjectTagInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\ProjectTag::class);
    }

    /**
     * Getter for TagId.
     *
     * @return int|null
     */
    public function getTagId(): ?int
    {
        return $this->getData(self::TAG_ID) === null ? null
            : (int)$this->getData(self::TAG_ID);
    }

    /**
     * Setter for TagId.
     *
     * @param int|null $tagId
     *
     * @return void
     */
    public function setTagId(?int $tagId): void
    {
        $this->setData(self::TAG_ID, $tagId);
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
